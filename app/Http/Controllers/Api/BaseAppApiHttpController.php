<?php

namespace App\Http\Controllers\Api;

use App\Model\Authorize\AppAuthorizeModel;
use App\Model\Database\AppModel;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Project0001ClientAuthSdk\Tokens\JwtAuthAppTokenHelper;


abstract class BaseAppApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
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

        if (!empty($sign)) {
            $appId = intval($token);
            $serviceKey = $sign;

            $appModel = AppModel::findModel($this->getPdoExtKernel(), $appId);

            if (is_null($appModel)) {
                throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                    self::TOKEN_KEY_NAME => 'Invalid value',
                    self::SIGN_KEY_NAME => 'Invalid value',
                ]);
            }

            if ($appModel->serviceKey !== $serviceKey) {
                throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                    self::TOKEN_KEY_NAME => 'Invalid value',
                    self::SIGN_KEY_NAME => 'Invalid value',
                ]);
            }

            AppAuthorizeModel::Instance()->appId = $appId;
            return;
        }

        try {

            $appId = JwtAuthAppTokenHelper::getAppFromJwtHeads($token);

            if (is_null($appId)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40101], self::AUTH_ERROR_CODE_40101);
            }

            $appModel = AppModel::findModel($this->getPdoExtKernel(), $appId);
            if (is_null($appModel)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40102], self::AUTH_ERROR_CODE_40102);
            }

            $appTokenPayload = JwtAuthAppTokenHelper::fromJwtAsPayload($token, $appModel->publicKey);

            if (is_null($appTokenPayload->aid) || is_null($appTokenPayload->iat) || is_null($appTokenPayload->exp) || is_null($appTokenPayload->hb)) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40103], self::AUTH_ERROR_CODE_40103);
            }

            if ($appTokenPayload->aid != $appId) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40104], self::AUTH_ERROR_CODE_40104);
            }

            $serverTime = curl_ext_time_utc();

            if ($serverTime < $appTokenPayload->iat and $serverTime > $appTokenPayload->exp) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40105], self::AUTH_ERROR_CODE_40105);
            }

            if (strtoupper($request->getMethod()) === 'GET') {
                $content = http_build_query($request->query->all());
            } else {
                $content = $request->getContent();
            }
            if (md5($content) !== $appTokenPayload->hb) {
                throw new \Exception(self::AUTH_ERROR_MESSAGES[self::AUTH_ERROR_CODE_40106], self::AUTH_ERROR_CODE_40106);
            }

            AppAuthorizeModel::Instance()->appId = $appId;

        } catch (\Throwable $e) {

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                'token' => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

        }
    }
}