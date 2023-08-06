<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Request;
use YusamHub\AppExt\Api\OpenApiExt;

class ApiSwaggerController extends \YusamHub\AppExt\SymfonyExt\Http\Controllers\ApiSwaggerController
{
    const MODULE_ADMIN = 'admin';
    const MODULE_USER = 'user';
    const MODULE_APP = 'app';
    const MODULE_ACCESS = 'access';

    const MODULES = [
        self::MODULE_ADMIN,
        self::MODULE_USER,
        self::MODULE_APP,
        self::MODULE_ACCESS
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