<?php

namespace App\Http\Controllers\Api;

use App\AuthorizeServers\UserTokenServer;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Project0001ClientAuthSdk\Exceptions\JsonAuthRuntimeException;
use YusamHub\Project0001ClientAuthSdk\Servers\Models\UserTokenAuthorizeModel;

abstract class BaseUserApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;

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

        $userTokenServer = new UserTokenServer(
            $request->headers->get(UserTokenServer::TOKEN_KEY_NAME,''),
            $request->headers->get(UserTokenServer::SIGN_KEY_NAME,''),
            $this->getContent($request)
        );

        $userTokenServer->setBaseApiHttpController($this);

        try {

            UserTokenAuthorizeModel::Instance()->assign($userTokenServer->getAuthorizeModelOrFail());

        } catch (\Throwable $e) {

            if ($e instanceof JsonAuthRuntimeException) {
                throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException(json_decode($e->getMessage(), true));
            }

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                UserTokenServer::TOKEN_KEY_NAME => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode(),
                'class' => get_class($e)
            ]);

        }
    }
}