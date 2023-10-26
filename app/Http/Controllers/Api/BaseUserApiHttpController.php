<?php

namespace App\Http\Controllers\Api;

use App\Model\Authorize\UserAuthorizeModel;
use App\Model\Database\UserModel;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Debug\Debug;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAuthUserTokenHelper;

abstract class BaseUserApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;

    const TOKEN_KEY_NAME = 'X-Token';
    const SIGN_KEY_NAME = 'X-Sign';
    const AUTH_ERROR_CODE_40101 = 40101;
    const AUTH_ERROR_CODE_40102 = 40102;
    const AUTH_ERROR_CODE_40103 = 40103;
    const AUTH_ERROR_CODE_40104 = 40104;
    const AUTH_ERROR_CODE_40105 = 40105;
    const AUTH_ERROR_CODE_40106 = 40106;
    const AUTH_ERROR_MESSAGES = [
        self::AUTH_ERROR_CODE_40101 => 'Invalid identifier in head',
        self::AUTH_ERROR_CODE_40102 => 'Fail load by identifier',
        self::AUTH_ERROR_CODE_40103 => 'Fail load payload data',
        self::AUTH_ERROR_CODE_40104 => 'Fail use payload data as identifier',
        self::AUTH_ERROR_CODE_40105 => 'Token expired',
        self::AUTH_ERROR_CODE_40106 => 'Invalid hash body',
    ];

    protected function getContent(Request $request): string
    {
        if (strtoupper($request->getMethod()) === 'GET') {
            $content = http_build_query($request->query->all());
        } else {
            $content = $request->getContent();
        }
        return $content;
    }

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

        $token = $request->headers->get(self::TOKEN_KEY_NAME,'');
        $sign = $request->headers->get(self::SIGN_KEY_NAME,'');

        try {
            if (!empty($sign)) {
                $userId = intval($token);
                $serviceKey = $sign;

                $userModel = UserModel::findModel($this->getPdoExtKernel(), $userId);

                if (is_null($userModel)) {
                    throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                        self::TOKEN_KEY_NAME => 'Invalid value',
                        self::SIGN_KEY_NAME => 'Invalid value',
                    ]);
                }

                if ($userModel->serviceKey !== $serviceKey) {
                    throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                        self::TOKEN_KEY_NAME => 'Invalid value',
                        self::SIGN_KEY_NAME => 'Invalid value',
                    ]);
                }

                UserAuthorizeModel::Instance()->userId = $userId;
                return;
            }

            $serverTime = curl_ext_time_utc();
            JWT::$timestamp = $serverTime;

            $userId = JwtAuthUserTokenHelper::getUserIdFromJwtHeads($token);

            if (is_null($userId)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40101], self::AUTH_ERROR_CODE_40101);
            }

            $userModel = UserModel::findModel($this->getPdoExtKernel(), $userId);
            if (is_null($userModel)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], self::AUTH_ERROR_CODE_40102);
            }

            $userTokenPayload = JwtAuthUserTokenHelper::fromJwtAsPayload($token, $userModel->publicKey);

            if (is_null($userTokenPayload->uid) || is_null($userTokenPayload->iat) || is_null($userTokenPayload->exp) || is_null($userTokenPayload->hb)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40103], self::AUTH_ERROR_CODE_40103);
            }

            if ($userTokenPayload->uid != $userId) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40104], self::AUTH_ERROR_CODE_40104);
            }

            if ($serverTime < $userTokenPayload->iat and $serverTime > $userTokenPayload->exp) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40105], self::AUTH_ERROR_CODE_40105);
            }

            if ($this->getContent($request) !== $userTokenPayload->hb) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40106], self::AUTH_ERROR_CODE_40106);
            }

            UserAuthorizeModel::Instance()->userId = $userId;

        } catch (\Throwable $e) {

            if ($e instanceof HttpUnauthorizedAppExtRuntimeException) {
                throw $e;
            }

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                self::TOKEN_KEY_NAME => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode(),
                'class' => get_class($e)
            ]);

        }
    }
}