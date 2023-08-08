<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseUserApiHttpController;
use App\Model\Authorize\UserAuthorizeModel;
use App\Model\Database\AppModel;
use App\Model\Database\UserConfigs\AppTariffUserConfigModel;
use App\Services\AdminAppService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\Helpers\ExceptionHelper;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class AdminAppControllerApi extends BaseUserApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_ADMIN;
    const TO_MANY_REQUESTS_CHECK_ENABLED = false;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    const ERROR_MAX_ALLOW_APPLICATIONS = 'The tariff has reached the maximum of applications';

    protected array $apiAuthorizePathExcludes = [
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/add', self::MODULE_CURRENT), 'postAppAdd');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/list', self::MODULE_CURRENT), 'getAppList');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/id/{appId}', self::MODULE_CURRENT), 'getAppId');
        static::routesAdd($routes, ['OPTIONS', 'PUT'],sprintf('/api/%s/app/id/{appId}/change-title', self::MODULE_CURRENT), 'putAppIdChangeTitle');
        static::routesAdd($routes, ['OPTIONS', 'PUT'],sprintf('/api/%s/app/id/{appId}/change-keys', self::MODULE_CURRENT), 'putAppIdChangeKeys');
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/add",
     *   summary="Application add",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="title", type="string", example="My first test application", description="Title for new application"),
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
     * @return array
     * @throws \Exception
     */
    public function postAppAdd(Request $request): array
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
                $request->request->all()
            );
            $validator->setRules([
                'title' => ['require','string','min:3','max:64'],
            ]);
            $validator->setRuleMessages([
                'title' => 'Invalid value, require string min(3), max(64)',
            ]);

            $validator->validateOrFail();

            /**
             * Читаем конфиг пользователей по настройкам
             */
            $appTariffUserConfigModel = AppTariffUserConfigModel::configModelFind(
                $this->getPdoExtKernel(),
                UserAuthorizeModel::Instance()->userId
            );

            $maxAllowApplications = 0;
            if (!is_null($appTariffUserConfigModel)) {
                $maxAllowApplications = $appTariffUserConfigModel->configValue->maxAllowApplications ?? 0;
            }

            if ($maxAllowApplications <= 0) {
                throw new ValidatorException('', [
                    'maxAllowApplications' => self::ERROR_MAX_ALLOW_APPLICATIONS,
                ]);
            }

            /**
             * Получаем текущее значение кол-во приложений
             */
            $currentApplication = AdminAppService::getAppCount(
                $this->getPdoExtKernel(),
                UserAuthorizeModel::Instance()->userId
            );
            /**
             * Проверяем ограничение
             */
            if ($currentApplication >= $maxAllowApplications) {
                throw new ValidatorException('', [
                    'maxAllowApplications' => self::ERROR_MAX_ALLOW_APPLICATIONS,
                ]);
            }

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AdminAppService::postAppAdd(
            $this->getPdoExtKernel(),
            UserAuthorizeModel::Instance()->userId,
            $validator->getAttribute('title')
        );
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/list",
     *   summary="Applications list",
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
    public function getAppList(Request $request): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        if (self::TO_MANY_REQUESTS_CHECK_ENABLED) {
            HttpHelper::checkTooManyRequestsOrFail(
                $this->getRedisKernel(),
                $this->getLogger(),
                $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, __METHOD__);
        }

        return AdminAppService::getAppList(
            $this->getPdoExtKernel(),
            UserAuthorizeModel::Instance()->userId
        );
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/id/{appId}",
     *   summary="Get app information",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\Parameter(name="appId",
     *     in="path",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="integer")
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
    public function getAppId(Request $request, int $appId): array
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
                'appId' => ['require','regex:^([0-9]{1,20})$', function($v){
                    return AppModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
            ]);
            $validator->setRuleMessages([
                'appId' => 'Invalid value',
            ]);

            $validator->validateOrFail();

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AdminAppService::getAppId(
            $this->getPdoExtKernel(),
            UserAuthorizeModel::Instance()->userId,
            $validator->getAttribute('appId')
        );
    }

    /**
     * @OA\Put(
     *   tags={"App"},
     *   path="/app/id/{appId}/change-title",
     *   summary="Application change by id title",
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
     *            @OA\Property(property="title", type="string", example="My changed title application", description="Title for application"),
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
    public function putAppIdChangeTitle(Request $request, int $appId): array
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
                'appId' => ['require','regex:^([0-9]{1,20})$', function($v){
                    return AppModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
                'title' => ['require','string','min:3','max:64'],
            ]);
            $validator->setRuleMessages([
                'appId' => 'Invalid value',
                'title' => 'Invalid value, require string min(3), max(64)',
            ]);

            $validator->validateOrFail();

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AdminAppService::putAppIdChangeTitle(
            $this->getPdoExtKernel(),
            UserAuthorizeModel::Instance()->userId,
            $validator->getAttribute('appId'),
            $validator->getAttribute('title')
        );
    }

    /**
     * @OA\Put(
     *   tags={"App"},
     *   path="/app/id/{appId}/change-keys",
     *   summary="Application change by id private/public keys",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\Parameter(name="appId",
     *     in="path",
     *     required=true,
     *     example="",
     *     @OA\Schema(type="integer")
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
    public function putAppIdChangeKeys(Request $request, int $appId): array
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
                'appId' => ['require','regex:^([0-9]{1,20})$', function($v){
                    return AppModel::exists($this->getRedisKernel(), $this->pdoExtKernel, $this->getLogger(), $v);
                }],
            ]);
            $validator->setRuleMessages([
                'appId' => 'Invalid value',
            ]);

            $validator->validateOrFail();

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AdminAppService::putAppIdChangeKeys(
            $this->getPdoExtKernel(),
            UserAuthorizeModel::Instance()->userId,
            $validator->getAttribute('appId')
        );
    }

}