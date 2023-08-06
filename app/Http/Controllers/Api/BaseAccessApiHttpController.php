<?php

namespace App\Http\Controllers\Api;

use App\Model\Authorize\AccessAuthorizeModel;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;

abstract class BaseAccessApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;
    const TOKEN_KEY_NAME = 'X-Token';
    const AUTH_ERROR_CODE_40101 = 40101;
    const AUTH_ERROR_CODE_401011 = 401011;
    const AUTH_ERROR_CODE_40102 = 40102;
    const AUTH_ERROR_CODE_401021 = 401021;
    const AUTH_ERROR_CODE_40103 = 40103;
    const AUTH_ERROR_CODE_40104 = 40104;
    const AUTH_ERROR_CODE_40105 = 40105;
    const AUTH_ERROR_MESSAGES = [
        self::AUTH_ERROR_CODE_40101 => 'Invalid identifiers in head',
        self::AUTH_ERROR_CODE_401011 => 'Invalid application in head',
        self::AUTH_ERROR_CODE_40102 => 'Fail load by identifiers',
        self::AUTH_ERROR_CODE_401021 => 'Fail loaded identifiers',
        self::AUTH_ERROR_CODE_40103 => 'Fail load payload data',
        self::AUTH_ERROR_CODE_40104 => 'Fail use payload data as identifiers',
        self::AUTH_ERROR_CODE_40105 => 'Token expired',
    ];

    /**
     * @param Request $request
     * @return void
     */
    protected function apiAuthorizeHandle(Request $request): void
    {
        if (property_exists($this, 'apiAuthorizePathExcludes')) {
            if (in_array($request->getRequestUri(), $this->apiAuthorizePathExcludes)) {
                return;
            }
        }

        $jwtToken = $request->headers->get(self::TOKEN_KEY_NAME,'');

        try {

           /* $demoTokenHead = JwtDemoTokenHelper::fromJwtAsHeads($jwtToken);

            if (is_null($demoTokenHead->aid) || is_null($demoTokenHead->uid) || is_null($demoTokenHead->did)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40101], self::AUTH_ERROR_CODE_40101);
            }*/

            /**
             * todo: сделать кеширование хранить там ключи!!!!
             *       чтобы каждый запрос пользователя мы не обращались к серверу авторизации!!!
             */
            /*$clientAuthApiAppSdk = new ClientAuthApiAppSdk();
            if ($clientAuthApiAppSdk->getIdentifierId() !== $demoTokenHead->aid) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_401011], self::AUTH_ERROR_CODE_401011);
            }

            $userKey = $clientAuthApiAppSdk->getUserKey($demoTokenHead->uid, $demoTokenHead->did);
            if (is_null($userKey)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], self::AUTH_ERROR_CODE_40102);
            }

            if (!isset($userKey['data']['keyHash']) || !isset($userKey['data']['publicKey'])) {
                $this->debug(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_401021], $userKey);
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_401021], self::AUTH_ERROR_CODE_401021);
            }

            $demoTokenPayload = JwtDemoTokenHelper::fromJwtAsPayload($jwtToken, $userKey['data']['publicKey']);
            if (
                is_null($demoTokenPayload->aid)
                ||
                is_null($demoTokenPayload->uid)
                ||
                is_null($demoTokenPayload->did)
                ||
                is_null($demoTokenPayload->phs)
                ||
                is_null($demoTokenPayload->iat) || is_null($demoTokenPayload->exp)
            ) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40103], self::AUTH_ERROR_CODE_40103);
            }

            if (
                $demoTokenPayload->aid != $demoTokenHead->aid
                ||
                $demoTokenPayload->uid != $demoTokenHead->uid
                ||
                $demoTokenPayload->did != $demoTokenHead->did
                ||
                $demoTokenPayload->phs != $userKey['data']['keyHash']
            ) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40104], self::AUTH_ERROR_CODE_40104);
            }

            $serverTime = time();

            if ($serverTime < $demoTokenPayload->iat and $serverTime > $demoTokenPayload->exp) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40105], self::AUTH_ERROR_CODE_40105);
            }

            DemoAuthorizeModel::Instance()->userId = $demoTokenPayload->uid;*/

            AccessAuthorizeModel::Instance()->userId = 0;
            AccessAuthorizeModel::Instance()->appId = 0;
            AccessAuthorizeModel::Instance()->deviceUuid = 0;

        } catch (\Throwable $e) {

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                'token' => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        }
    }
}