<?php

namespace App\Http\Controllers\Web\Dev;

use App\Http\Controllers\Web\WebBaseHttpController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class WebDevHomeController extends WebBaseHttpController
{
    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'GET'], '/','actionHomeIndex');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function actionHomeIndex(Request $request): string
    {
        return '';
    }
}