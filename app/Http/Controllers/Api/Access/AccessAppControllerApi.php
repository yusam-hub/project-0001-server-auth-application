<?php

namespace App\Http\Controllers\Api\Access;

use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseUserApiHttpController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;


class AccessAppControllerApi extends BaseUserApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_ACCESS;
    const TO_MANY_REQUESTS_CHECK_ENABLED = true;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 60;

    protected array $apiAuthorizePathExcludes = [
    ];

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/token', self::MODULE_CURRENT), 'postAppToken');
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/token",
     *   summary="Get access token to application",
     *   deprecated=false,
     *   security={{"XTokenScheme":{}}},
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="appId", type="integer", example="", description=""),
     *            @OA\Property(property="userId", type="integer", example="", description=""),
     *            @OA\Property(property="deviceUuid", type="string", example="", description="Unique device uuid"),
     *        ),
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @param int $appId
     * @return array
     */
    public function postAppToken(Request $request, int $appId): array
    {
        return [];
    }
}