<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\Api\OpenApiExt;

class ApiSwaggerController extends \YusamHub\AppExt\SymfonyExt\Http\Controllers\ApiSwaggerController
{
    const MODULE_USER = 'user';
    const MODULE_ADMIN = 'admin';

    const MODULES = [
        self::MODULE_USER,
        self::MODULE_ADMIN,
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
}