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
use YusamHub\AppExt\Helpers\ExceptionHelper;
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
     *   path="/access-token",
     *   summary="Get user access token for application",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}},{"XSignScheme":{}}},
     *   @OA\Parameter(name="accessToken",
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
    public function getAccessToken(Request $request): array
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
                'accessToken' => ['require','regex:^([0-9a-f]{32})$', function($v){
                    return $this->getRedisKernel()->connection()->has($v);
                }],
            ]);
            $validator->setRuleMessages([
                'accessToken' => 'Invalid value',
            ]);

            $validator->validateOrFail();

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return (array) $this->getRedisKernel()->connection()->get($validator->getAttribute('accessToken'));
    }
}