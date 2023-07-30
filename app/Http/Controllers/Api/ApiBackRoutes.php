<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Back\ApiBackController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

class ApiBackRoutes extends BaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        ApiBackController::routesRegister($routes);
    }

}