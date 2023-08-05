<?php

namespace App\Http\Controllers\Api\Front;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use App\Model\Authorize\FrontAppAuthorizeModel;
use App\Model\Database\AppModel;
use App\Model\Database\UserModel;
use App\Services\AppService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAuthUserTokenHelper;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

/**
 * @OA\SecurityScheme(
 *      securityScheme="XUserTokenScheme",
 *      type="apiKey",
 *      in="header",
 *      name="X-User-Token"
 * )
 */

class FrontAppControllerApi extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;

    const MODULE_CURRENT = ApiSwaggerController::MODULE_FRONT;

    const USER_TOKEN_KEY_NAME = 'X-User-Token';

    const TO_MANY_REQUESTS_CHECK_ENABLED = true;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    const AUTH_ERROR_CODE_40101 = 40101;
    const AUTH_ERROR_CODE_40102 = 40102;
    const AUTH_ERROR_CODE_40103 = 40103;
    const AUTH_ERROR_CODE_40104 = 40104;
    const AUTH_ERROR_CODE_40105 = 40105;
    const AUTH_ERROR_CODE_40106 = 40106;
    const AUTH_ERROR_MESSAGES = [
        self::AUTH_ERROR_CODE_40101 => 'Invalid user identifier in head',
        self::AUTH_ERROR_CODE_40102 => 'Fail load user by identifier',
        self::AUTH_ERROR_CODE_40103 => 'Fail load payload data',
        self::AUTH_ERROR_CODE_40104 => 'IFail use payload data as user identifier',
        self::AUTH_ERROR_CODE_40105 => 'Token expired',
        self::AUTH_ERROR_CODE_40106 => 'Invalid hash body',
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        //static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/list', self::MODULE_CURRENT), 'getAppList');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/add', self::MODULE_CURRENT), 'postAppAdd');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/id/{appId}', self::MODULE_CURRENT), 'getAppId');
        static::routesAdd($routes, ['OPTIONS', 'PUT'],sprintf('/api/%s/app/id/{appId}/change-title', self::MODULE_CURRENT), 'putAppIdChangeTitle');
        static::routesAdd($routes, ['OPTIONS', 'PUT'],sprintf('/api/%s/app/id/{appId}/change-keys', self::MODULE_CURRENT), 'putAppIdChangeKeys');
    }

    protected array $apiAuthorizePathExcludes = [

    ];

    /**
     * @param Request $request
     * @return void
     */
    protected function apiAuthorizeHandle(Request $request): void
    {
        if (in_array($request->getRequestUri(), $this->apiAuthorizePathExcludes)) {
            return;
        }

        $jwtToken = $request->headers->get(self::USER_TOKEN_KEY_NAME,'');

        try {

            $userId = JwtAuthUserTokenHelper::getUserFromJwtHeads($jwtToken);

            if (is_null($userId)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40101], self::AUTH_ERROR_CODE_40101);
            }

            $userModel = UserModel::findModel($this->getPdoExtKernel(), $userId);
            if (is_null($userModel)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], self::AUTH_ERROR_CODE_40102);
            }

            $userTokenPayload = JwtAuthUserTokenHelper::fromJwtAsPayload($jwtToken, $userModel->publicKey);

            if (is_null($userTokenPayload->uid) || is_null($userTokenPayload->iat) || is_null($userTokenPayload->exp) || is_null($userTokenPayload->hb)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40103], self::AUTH_ERROR_CODE_40103);
            }

            if ($userTokenPayload->uid != $userId) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40104], self::AUTH_ERROR_CODE_40104);
            }

            $serverTime = time();

            if ($serverTime < $userTokenPayload->iat and $serverTime > $userTokenPayload->exp) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40105], self::AUTH_ERROR_CODE_40105);
            }

            if (strtoupper($request->getMethod()) === 'GET') {
                $content = $request->getQueryString();
            } else {
                $content = $request->getContent();
            }
            if (md5($content) !== $userTokenPayload->hb) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40106], self::AUTH_ERROR_CODE_40106);
            }

            FrontAppAuthorizeModel::Instance()->userId = $userId;

        } catch (\Throwable $e) {

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                'token' => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        }
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/list",
     *   summary="Applications list",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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

        return AppService::getAppList(
            $this->getPdoExtKernel(),
            FrontAppAuthorizeModel::Instance()->userId
        );
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/add",
     *   summary="Application add",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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

        return AppService::postAppAdd(
            $this->getPdoExtKernel(),
            FrontAppAuthorizeModel::Instance()->userId,
            $validator->getAttribute('title')
        );
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/id/{appId}",
     *   summary="Get app information",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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
                'appId' => ['require','int', function($v){
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

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AppService::getAppId(
            $this->getPdoExtKernel(),
            FrontAppAuthorizeModel::Instance()->userId,
            $validator->getAttribute('appId')
        );
    }

    /**
     * @OA\Put(
     *   tags={"App"},
     *   path="/app/id/{appId}/change-title",
     *   summary="Application change by id title",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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
                'appId' => ['require','int', function($v){
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

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AppService::putAppIdChangeTitle(
            $this->getPdoExtKernel(),
            FrontAppAuthorizeModel::Instance()->userId,
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
     *   security={{"XUserTokenScheme":{}}},
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
                'appId' => ['require','int', function($v){
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

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return AppService::putAppIdChangeKeys(
            $this->getPdoExtKernel(),
            FrontAppAuthorizeModel::Instance()->userId,
            $validator->getAttribute('appId')
        );
    }
}