<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseUserApiHttpController;
use App\Model\Authorize\UserAuthorizeModel;
use App\Model\Database\AppModel;
use App\Model\Database\AppUserKeyModel;
use App\Services\AdminAppService;
use App\Services\UserAppService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\Helpers\ExceptionHelper;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAccessTokenHelper;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAuthAppTokenHelper;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class UserAppControllerApi extends BaseUserApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_USER;
    const TO_MANY_REQUESTS_CHECK_ENABLED = true;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    protected array $apiAuthorizePathExcludes = [
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/id/{appId}/key-refresh', self::MODULE_CURRENT), 'postAppIdRefresh');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/id/{appId}/key-list', self::MODULE_CURRENT), 'getAppIdKeyList');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/access-token', self::MODULE_CURRENT), 'postAppAccessToken');
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/id/{appId}/key-refresh",
     *   summary="Refresh keys for access user to application id",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}},{"XSignScheme":{}}},
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
                'appId' => ['require','regex:^([0-9]{1,20})$', function($v){
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

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        return UserAppService::postAppIdRefresh(
            $this->getPdoExtKernel(),
            $validator->getAttribute('appId'),
            UserAuthorizeModel::Instance()->userId,
            $validator->getAttribute('deviceUuid')
        );
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/id/{appId}/key-list",
     *   summary="Application id key list for user",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}},{"XSignScheme":{}}},
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
    public function getAppIdKeyList(Request $request, int $appId): array
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
                [
                    'appId' => $appId
                ]
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

        return UserAppService::getAppIdKeyList(
            $this->getPdoExtKernel(),
            UserAuthorizeModel::Instance()->userId,
            $validator->getAttribute('appId')
        );
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/access-token",
     *   summary="Create access token for application id",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}},{"XSignScheme":{}}},
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="assertion", type="string", example="", description=""),
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
     */
    public function postAppAccessToken(Request $request): array
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
                'assertion' => ['require','string', function($v){
                    list($id, $serviceKey) = explode(":",$v);
                    if (!empty($id) && !empty($serviceKey)) {
                        return true;
                    }
                    $accessTokenHead = JwtAccessTokenHelper::fromJwtAsHeads($v);
                    return (!is_null($accessTokenHead->uid) && !is_null($accessTokenHead->aid) && !is_null($accessTokenHead->did));
                }],
            ]);
            $validator->setRuleMessages([
                'assertion' => 'Invalid value',
            ]);

            $validator->validateOrFail();

            list($id,$serviceKey) = explode(":",$validator->getAttribute('assertion'));

            $serverTime = curl_ext_time_utc();

            if (!empty($id) && !empty($serviceKey)) {

                $appUserKeyModel = AppUserKeyModel::findModel($this->getPdoExtKernel(), $id);
                if (!is_null($appUserKeyModel) && $appUserKeyModel->serviceKey !== $serviceKey) {
                    throw new ValidatorException('', [
                        'assertion' => 'Invalid value',
                    ]);
                }

                $expire = 600;
                $accessTokenPayload = [
                    'type' => 'service-key',
                    'expired' => $serverTime + $expire,
                    'userId' => $appUserKeyModel->userId,
                    'appId' => $appUserKeyModel->appId,
                    'deviceUuid' => $appUserKeyModel->deviceUuid
                ];
                $accessToken = md5(json_encode($accessTokenPayload) . microtime());

            } else {

                $accessTokenHead = JwtAccessTokenHelper::fromJwtAsHeads($validator->getAttribute('assertion'));

                if (is_null($accessTokenHead->aid) || is_null($accessTokenHead->uid) || is_null($accessTokenHead->did)) {
                    throw new ValidatorException(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40101], [], self::AUTH_ERROR_CODE_40101);
                }

                $appUserKeyModel = AppUserKeyModel::findModelByAttributes($this->pdoExtKernel, [
                    'appId' => $accessTokenHead->aid,
                    'userId' => $accessTokenHead->uid,
                    'deviceUuid' => $accessTokenHead->did,
                ]);
                if (is_null($appUserKeyModel)) {
                    throw new ValidatorException(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], [], self::AUTH_ERROR_CODE_40102);
                }

                $accessTokenPayload = JwtAccessTokenHelper::fromJwtAsPayload($validator->getAttribute('assertion'), $appUserKeyModel->publicKey);
                if (
                    is_null($accessTokenPayload->aid)
                    ||
                    is_null($accessTokenPayload->uid)
                    ||
                    is_null($accessTokenPayload->did)
                    ||
                    is_null($accessTokenPayload->pkh)
                    ||
                    is_null($accessTokenPayload->iat) || is_null($accessTokenPayload->exp)
                ) {
                    throw new ValidatorException(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40103], [], self::AUTH_ERROR_CODE_40103);
                }

                if (
                    $accessTokenPayload->aid != $accessTokenHead->aid
                    ||
                    $accessTokenPayload->uid != $accessTokenHead->uid
                    ||
                    $accessTokenPayload->did != $accessTokenHead->did
                    ||
                    $accessTokenPayload->pkh != $appUserKeyModel->keyHash
                ) {
                    throw new ValidatorException(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40104], [], self::AUTH_ERROR_CODE_40104);
                }



                if ($serverTime < $accessTokenPayload->iat and $serverTime > $accessTokenPayload->exp) {
                    throw new ValidatorException(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40105], [], self::AUTH_ERROR_CODE_40105);
                }

                $appModel = AppModel::findModel($this->pdoExtKernel, $accessTokenPayload->aid);
                if (is_null($appModel)) {
                    throw new ValidatorException(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], [], self::AUTH_ERROR_CODE_40102);
                }

                $expire = $accessTokenPayload->exp - $serverTime;
                $accessTokenPayload = [
                    'type' => 'jwt-key',
                    'expired' => $serverTime + $expire,
                    'userId' => $accessTokenPayload->uid,
                    'appId' => $accessTokenPayload->aid,
                    'deviceUuid' => $accessTokenPayload->did
                ];
                $accessToken = md5(json_encode($accessTokenPayload) . microtime());

            }

            $this->getRedisKernel()->connection()->put(
                $accessToken,
                $accessTokenPayload,
                $expire
            );

            return [
                'expire' => $expire,
                'accessToken' => $accessToken
            ];

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }
    }
}