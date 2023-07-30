<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Front\ApiFrontController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

class ApiFrontRoutes extends BaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        ApiFrontController::routesRegister($routes);
    }

}