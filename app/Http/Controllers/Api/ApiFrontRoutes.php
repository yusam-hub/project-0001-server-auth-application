<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Admin\AdminAppControllerApi;
use App\Http\Controllers\Api\Admin\AdminControllerApi;
use App\Http\Controllers\Api\User\UserAccountControllerApi;
use App\Http\Controllers\Api\User\UserControllerApi;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Controllers\BaseHttpController;

class ApiFrontRoutes extends BaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        AdminControllerApi::routesRegister($routes);
        AdminAppControllerApi::routesRegister($routes);

        UserControllerApi::routesRegister($routes);
        UserAccountControllerApi::routesRegister($routes);
    }

}