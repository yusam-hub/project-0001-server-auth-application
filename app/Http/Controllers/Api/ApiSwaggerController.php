<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Request;

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
            __DIR__ . DIRECTORY_SEPARATOR . ucfirst($module),
            app_ext()->getRootDir('/vendor/yusam-hub/app-ext/src/SymfonyExt/Http/Controllers/Api/' . ucfirst($module))
        ];
    }
}