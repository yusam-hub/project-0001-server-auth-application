<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {


    \App\Http\Controllers\Web\WebDevRoutes::routesRegister($routes);

    \App\Http\Controllers\Api\ApiSwaggerController::routesRegister($routes);

    \YusamHub\AppExt\SymfonyExt\Http\Controllers\Api\Debug\DebugController::routesRegister($routes);
    \App\Http\Controllers\Api\ApiV1Routes::routesRegister($routes);
};