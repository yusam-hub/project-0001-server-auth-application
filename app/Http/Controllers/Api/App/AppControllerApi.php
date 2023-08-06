<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use App\Http\Controllers\Api\BaseAppApiHttpController;
use App\Http\Controllers\Api\BaseUserApiHttpController;
use App\Model\Authorize\AppAuthorizeModel;
use App\Model\Authorize\UserAuthorizeModel;
use App\Model\Database\AppModel;
use App\Model\Database\UserModel;
use App\Services\AdminAppService;
use App\Services\AppService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class AppControllerApi extends BaseAppApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_APP;

    const TO_MANY_REQUESTS_CHECK_ENABLED = true;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    protected array $apiAuthorizePathExcludes = [
        '/api/' . self::MODULE_CURRENT
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s', self::MODULE_CURRENT), 'getApiHome');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/user-key', self::MODULE_CURRENT), 'getUserKey');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/access-token', self::MODULE_CURRENT), 'getAccessToken');
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
     *   summary="Get user public key for application",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\Parameter(name="uid",
     *     in="query",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(name="did",
     *     in="query",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="string")
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
                'uid' => ['require','regex:^([0-9]{1,20})$', function($v){
                    return UserModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
                'did' => ['require','string','min:32','max:36'],
            ]);
            $validator->setRuleMessages([
                'uid' => 'Invalid value',
                'did' => 'Invalid value, require string min(32), max(36)',
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

        return AppService::getUserKey(
            $this->getPdoExtKernel(),
            AppAuthorizeModel::Instance()->appId,
            $validator->getAttribute('uid'),
            $validator->getAttribute('did')
        );
    }
}