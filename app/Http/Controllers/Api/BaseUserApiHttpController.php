<?php

namespace App\Http\Controllers\Api;

use App\Model\Authorize\UserAuthorizeModel;
use App\Model\Database\UserModel;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAuthUserTokenHelper;

abstract class BaseUserApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;

    const TOKEN_KEY_NAME = 'X-Token';
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

            UserAuthorizeModel::Instance()->userId = $userId;

        } catch (\Throwable $e) {

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                'token' => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        }
    }
}