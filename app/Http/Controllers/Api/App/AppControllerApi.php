<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseAppApiHttpController;
use App\Model\Database\AppUserKeyModel;
use App\Model\Database\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\Helpers\ExceptionHelper;
use YusamHub\DbExt\Exceptions\PdoExtModelException;
use YusamHub\Project0001ClientAuthSdk\Servers\Models\AppTokenAuthorizeModel;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class AppControllerApi extends BaseAppApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_APP;

    const TO_MANY_REQUESTS_CHECK_ENABLED = false;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    protected array $apiAuthorizePathExcludes = [
        '/api/' . self::MODULE_CURRENT
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s', self::MODULE_CURRENT), 'getApiHome');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/user-key', self::MODULE_CURRENT), 'getUserKey');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/user-key-service', self::MODULE_CURRENT), 'getUserKeyService');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getApiHome(Request $request): array
    {
        return [];
    }

    /**
     * @OA\Get(
     *   tags={"default"},
     *   path="/user-key",
     *   summary="Get user key for application",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}},{"XSignScheme":{}}},
     *   @OA\Parameter(name="userId",
     *     in="query",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(name="deviceUuid",
     *     in="query",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{"keyHash":"","publicKey":""}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     */
    public function getUserKey(Request $request): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        if (self::TO_MANY_REQUESTS_CHECK_ENABLED) {
            HttpHelper::checkTooManyRequestsOrFail(
                $this->getRedisKernel(),
                $this->getLogger(),
                $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, __METHOD__);
        }

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->query->all()
            );
            $validator->setRules([
                'userId' => ['require','regex:^([0-9]{1,20})$', function($v){
                    return UserModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
                'deviceUuid' => ['require','string','min:32','max:36'],
            ]);
            $validator->setRuleMessages([
                'userId' => 'Invalid value',
                'deviceUuid' => 'Invalid value, require string min(32), max(36)',
            ]);

            $validator->validateOrFail();

            $appUserKeyModel = AppUserKeyModel::findModelByAttributesOrFail($this->getPdoExtKernel(), [
                'appId' => AppTokenAuthorizeModel::Instance()->appId,
                'userId' => $validator->getAttribute('userId'),
                'deviceUuid' => $validator->getAttribute('deviceUuid')
            ]);

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            if ($e instanceof PdoExtModelException) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'userId' => 'Invalid value',
                    'deviceUuid' => 'Invalid value'
                ],$e->getMessage());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return [
            'keyHash' => $appUserKeyModel->keyHash,
            'publicKey' => $appUserKeyModel->publicKey,
        ];
    }

    /**
     * @OA\Get(
     *   tags={"default"},
     *   path="/user-key-service",
     *   summary="Get user key by service for application",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}},{"XSignScheme":{}}},
     *   @OA\Parameter(name="appUserKeyId",
     *     in="query",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(name="serviceKey",
     *     in="query",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{"appId":"","userId":"","deviceUuid":""}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     */
    public function getUserKeyService(Request $request): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        if (self::TO_MANY_REQUESTS_CHECK_ENABLED) {
            HttpHelper::checkTooManyRequestsOrFail(
                $this->getRedisKernel(),
                $this->getLogger(),
                $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, __METHOD__);
        }

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->query->all()
            );
            $validator->setRules([
                'appUserKeyId' => ['require','regex:^([0-9]{1,20})$', function($v){
                    return AppUserKeyModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
                'serviceKey' => ['require','string','size:32'],
            ]);
            $validator->setRuleMessages([
                'appUserKeyId' => 'Invalid value',
                'serviceKey' => 'Invalid value, require string size(32)',
            ]);

            $validator->validateOrFail();

            $appUserKeyModel = AppUserKeyModel::findModelByAttributesOrFail($this->getPdoExtKernel(), [
                'id' => $validator->getAttribute('appUserKeyId'),
                'appId' => AppTokenAuthorizeModel::Instance()->appId,
                'serviceKey' => $validator->getAttribute('serviceKey')
            ]);

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            if ($e instanceof PdoExtModelException) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'appUserKeyId' => 'Invalid value',
                    'serviceKey' => 'Invalid value'
                ],$e->getMessage());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return [
            'appId' => $appUserKeyModel->appId,
            'userId' => $appUserKeyModel->userId,
            'deviceUuid' => $appUserKeyModel->deviceUuid,
        ];
    }
}