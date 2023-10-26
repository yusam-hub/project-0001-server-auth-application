<?php

namespace App\Http\Controllers\Api;

use App\AuthorizeServers\AppTokenServer;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;
use YusamHub\Project0001ClientAuthSdk\Servers\Models\AppTokenAuthorizeModel;

abstract class BaseAppApiHttpController extends BaseApiHttpController implements ControllerMiddlewareInterface
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

        $appTokenServer = new AppTokenServer(
            $request->headers->get(AppTokenServer::TOKEN_KEY_NAME,''),
            $request->headers->get(AppTokenServer::SIGN_KEY_NAME,''),
            $this->getContent($request)
        );

        $appTokenServer->setBaseApiHttpController($this);

        try {

            AppTokenAuthorizeModel::Instance()->assign($appTokenServer->getAuthorizeModelOrFail());

        } catch (\Throwable $e) {

            if ($e instanceof \RuntimeException) {
                throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException(json_decode($e->getMessage(), true));
            }

            throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
                AppTokenServer::TOKEN_KEY_NAME => 'Invalid value',
                'detail' => $e->getMessage(),
                'code' => $e->getCode(),
                'class' => get_class($e)
            ]);

        }
    }
}