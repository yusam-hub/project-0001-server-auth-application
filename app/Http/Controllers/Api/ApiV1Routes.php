<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\V1ControllerApi;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

class ApiV1Routes extends BaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        V1ControllerApi::routesRegister($routes);
    }

}