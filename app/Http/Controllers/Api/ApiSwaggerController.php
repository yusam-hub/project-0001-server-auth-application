<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Front\FrontAppControllerApi;
use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\Api\OpenApiExt;

class ApiSwaggerController extends \YusamHub\AppExt\SymfonyExt\Http\Controllers\ApiSwaggerController
{
    const MODULE_DEBUG = 'debug';
    const MODULE_FRONT = 'front';

    const MODULES = [
        self::MODULE_DEBUG,
        self::MODULE_FRONT,
    ];

    protected static function getSwaggerModules(): array
    {
        return self::MODULES;
    }

    protected function getOpenApiScanPaths(Request $request, string $module): array
    {
        return [
            __DIR__ . DIRECTORY_SEPARATOR . ucfirst($module)
        ];
    }

    protected function getReplaceKeyValuePairForModule(Request $request, string $module): array
    {
        $port = (int) ($request->server->get('SERVER_PORT') ?? $request->getPort());
        if (in_array($port, [80,443])) {
            $port = 0;
        }
        return [
            '__OA_INFO_TITLE__' => sprintf(app_ext_config('api.infoTitle','Api %s Server'), ucfirst($module)),
            '__OA_INFO_VERSION__' => app_ext_config('api.infoVersion', '1.0.0'),
            '__OA_SERVER_HOSTNAME__' => $request->getHost() . ($port ? ':'.$port : ''),
            '__OA_SERVER_PATH__' => trim(app_ext_config('api.apiBaseUri'), '/') . '/' . strtolower($module),
            '__OA_SERVER_SCHEMA__' => $request->getScheme(),
            //'__OA_SECURITY_SCHEME_TOKEN_HEADER_NAME__' => app_ext_config('api.tokenKeyName', 'X-Token'),
            //'__OA_SECURITY_SCHEME_SIGN_HEADER_NAME__' => app_ext_config('api.signKeyName', 'X-Sign'),
            '__OA_METHOD_GET_HOME_PATH__' => '/',
        ];
    }

    public function getSwaggerUiOpenApi(Request $request, string $module): string
    {
        $openApiExt = new OpenApiExt([
            'paths' => $this->getOpenApiScanPaths($request, $module),
            'replaceKeyValuePair' => $this->getReplaceKeyValuePairForModule($request, $module)
        ]);

        try {
            //todo: use cache for production
            return $openApiExt->generateOpenApi(
                [
                    'SecurityScheme'
                ]
            );

        } catch (\Throwable $e) {

            $this->error($e->getMessage());

            return '{}';
        }
    }
}