<?php

namespace App\Http\Controllers\Api\Back;

use App\Http\Controllers\Api\BaseApiHttpController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class BackControllerApi extends BaseApiHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'GET'],'/api/back', 'getApiHome');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getApiHome(Request $request): array
    {
        return [];
    }
}