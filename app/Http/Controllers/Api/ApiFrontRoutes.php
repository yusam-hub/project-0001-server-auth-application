<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Front\FrontAppControllerApi;
use App\Http\Controllers\Api\Front\FrontControllerApi;
use App\Http\Controllers\Api\Front\FrontUserControllerApi;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

class ApiFrontRoutes extends BaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        FrontControllerApi::routesRegister($routes);
        FrontUserControllerApi::routesRegister($routes);
        FrontAppControllerApi::routesRegister($routes);
    }

}