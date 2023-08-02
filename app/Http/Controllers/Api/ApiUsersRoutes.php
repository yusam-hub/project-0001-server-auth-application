<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Front\FrontControllerApi;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

class ApiUsersRoutes extends BaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        FrontControllerApi::routesRegister($routes);
    }

}