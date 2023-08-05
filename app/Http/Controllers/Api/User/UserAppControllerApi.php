<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseUserTokenApiHttpController;
use App\Model\Authorize\FrontAppAuthorizeModel;
use App\Model\Database\AppModel;
use App\Services\AdminAppService;
use App\Services\UserAppService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class UserAppControllerApi extends BaseUserTokenApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_USER;
    const TO_MANY_REQUESTS_CHECK_ENABLED = false;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    protected array $apiAuthorizePathExcludes = [
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/id/{appId}/key-refresh', self::MODULE_CURRENT), 'postAppIdRefresh');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/key/list', self::MODULE_CURRENT), 'getAppKeyList');
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/id/{appId}/key-refresh",
     *   summary="Refresh keys for access user to application id",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\Parameter(name="appId",
     *     in="path",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="deviceUuid", type="string", example="", description="Unique device uuid"),
     *        ),
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @param int $appId
     * @return array
     */
    public function postAppIdRefresh(Request $request, int $appId): array
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
                array_merge(
                    $request->request->all(),
                    [
                        'appId' => $appId
                    ]
                )
            );
            $validator->setRules([
                'deviceUuid' => ['require','string','min:32','max:36'],
                'appId' => ['require','int', function($v){
                    return AppModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
            ]);
            $validator->setRuleMessages([
                'deviceUuid' => 'Invalid value, require string min(32), max(36)',
                'appId' => 'Invalid value',
            ]);

            $validator->validateOrFail();

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return UserAppService::postAppIdRefresh(
            $this->getPdoExtKernel(),
            $validator->getAttribute('appId'),
            FrontAppAuthorizeModel::Instance()->userId,
            $validator->getAttribute('deviceUuid')
        );
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/key/list",
     *   summary="Applications key list for user",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getAppKeyList(Request $request): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        if (self::TO_MANY_REQUESTS_CHECK_ENABLED) {
            HttpHelper::checkTooManyRequestsOrFail(
                $this->getRedisKernel(),
                $this->getLogger(),
                $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, __METHOD__);
        }

        return UserAppService::getAppKeyList(
            $this->getPdoExtKernel(),
            FrontAppAuthorizeModel::Instance()->userId
        );
    }
}