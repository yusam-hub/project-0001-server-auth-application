<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use App\Http\Controllers\Api\BaseUserTokenApiHttpController;
use App\Model\Authorize\FrontAppAuthorizeModel;
use App\Model\Database\AppModel;
use App\Services\AdminAppService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

/**
 * @OA\SecurityScheme(
 *      securityScheme="XTokenScheme",
 *      type="apiKey",
 *      in="header",
 *      name="X-User-Token"
 * )
 */
class AdminControllerApi extends BaseApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_ADMIN;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s', self::MODULE_CURRENT), 'getApiHome');
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