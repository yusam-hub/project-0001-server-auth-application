<?php

namespace App\Http\Controllers\Api\Token;

use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseTokenApiHttpController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TokenAppControllerApi extends BaseTokenApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_TOKEN;
    const TO_MANY_REQUESTS_CHECK_ENABLED = false;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    protected array $apiAuthorizePathExcludes = [
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');
    }

}