<?php

namespace App\Http\Controllers\Api;

use App\Model\Authorize\TokenAppUserAuthorizeModel;
use App\Model\Database\AppUserKeyModel;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAuthAppUserTokenHelper;

abstract class BaseTokenApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;

    const TOKEN_KEY_NAME = 'X-Token';
    const AUTH_ERROR_CODE_40101 = 40101;
    const AUTH_ERROR_CODE_40102 = 40102;
    const AUTH_ERROR_CODE_40103 = 40103;
    const AUTH_ERROR_CODE_40104 = 40104;
    const AUTH_ERROR_CODE_40105 = 40105;
    const AUTH_ERROR_MESSAGES = [
        self::AUTH_ERROR_CODE_40101 => 'Invalid identifiers in head',
        self::AUTH_ERROR_CODE_40102 => 'Fail load by identifiers',
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

            $appUserTokeHead = JwtAuthAppUserTokenHelper::fromJwtAsHeads($jwtToken);

            if (is_null($appUserTokeHead->uid) || is_null($appUserTokeHead->aid) || is_null($appUserTokeHead->did)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40101], self::AUTH_ERROR_CODE_40101);
            }

            $appUserKeyModel = AppUserKeyModel::findModelByAttributes($this->getPdoExtKernel(),[
                'appId' => $appUserTokeHead->aid,
                'userId' => $appUserTokeHead->uid,
                'deviceUuid' => $appUserTokeHead->did
            ]);
            if (is_null($appUserKeyModel)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], self::AUTH_ERROR_CODE_40102);
            }

            $appUserTokenPayload = JwtAuthAppUserTokenHelper::fromJwtAsPayload($jwtToken, $appUserKeyModel->publicKey);

            if (is_null($appUserTokenPayload->aid) || is_null($appUserTokenPayload->uid) || is_null($appUserTokenPayload->did) || is_null($appUserTokenPayload->iat) || is_null($appUserTokenPayload->exp)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40103], self::AUTH_ERROR_CODE_40103);
            }

            if ($appUserTokenPayload->uid != $appUserTokeHead->uid || $appUserTokenPayload->aid != $appUserTokeHead->aid || $appUserTokenPayload->did != $appUserTokeHead->did) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40104], self::AUTH_ERROR_CODE_40104);
            }

            $serverTime = time();

            if ($serverTime < $appUserTokenPayload->iat and $serverTime > $appUserTokenPayload->exp) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40105], self::AUTH_ERROR_CODE_40105);
            }

            TokenAppUserAuthorizeModel::Instance()->userId = $appUserTokenPayload->uid;
            TokenAppUserAuthorizeModel::Instance()->appId = $appUserTokenPayload->aid;
            TokenAppUserAuthorizeModel::Instance()->deviceUuid = $appUserTokenPayload->did;

        } catch (\Throwable $e) {

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                'token' => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        }
    }
}