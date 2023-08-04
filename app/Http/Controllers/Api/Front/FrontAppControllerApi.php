<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\SymfonyExt\Http\Interfaces\ControllerMiddlewareInterface;
use YusamHub\AppExt\SymfonyExt\Http\Traits\ControllerMiddlewareTrait;

/**
 * @OA\SecurityScheme(
 *      securityScheme="XUserTokenScheme",
 *      type="apiKey",
 *      in="header",
 *      name="X-User-Token"
 * )
 */

class FrontAppControllerApi extends BaseApiHttpController implements ControllerMiddlewareInterface
{
    use ControllerMiddlewareTrait;

    const MODULE_CURRENT = ApiSwaggerController::MODULE_FRONT;

    const USER_TOKEN_KEY_NAME = 'X-User-Token';

    const TO_MANY_REQUESTS_CHECK_ENABLED = true;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 600;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::controllerMiddlewareRegister(static::class, 'apiAuthorizeHandle');

        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/list', self::MODULE_CURRENT), 'getAppList');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/app/add', self::MODULE_CURRENT), 'postAppAdd');
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s/app/id/{appId}', self::MODULE_CURRENT), 'getAppId');
        static::routesAdd($routes, ['OPTIONS', 'PUT'],sprintf('/api/%s/app/id/{appId}/change', self::MODULE_CURRENT), 'putAppIdChange');
        static::routesAdd($routes, ['OPTIONS', 'PUT'],sprintf('/api/%s/app/id/{appId}/change-keys', self::MODULE_CURRENT), 'putAppIdChangeKeys');
    }

    protected array $apiAuthorizePathExcludes = [

    ];

    /**
     * @param Request $request
     * @return void
     */
    protected function apiAuthorizeHandle(Request $request): void
    {
        if (in_array($request->getRequestUri(), $this->apiAuthorizePathExcludes)) {
            return;
        }

        /**
         * todo: нужно реализовать доступ пользователя к методам,
         *       у пользователя есть приватный ключ,
         *       у сервера есть публичный ключ
         */

        throw new \YusamHub\AppExt\Exceptions\HttpUnauthorizedAppExtRuntimeException([
            'detail' => 'Invalid token value'
        ]);
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/list",
     *   summary="Applications list",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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
     * @return array
     * @throws \Exception
     */
    public function getAppList(Request $request): array
    {
        return [];
    }

    /**
     * @OA\Post(
     *   tags={"App"},
     *   path="/app/add",
     *   summary="Application add",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="title", type="string", example="My first test application", description="Title for new application"),
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
     * @return array
     * @throws \Exception
     */
    public function postAppAdd(Request $request): array
    {
        return [];
    }

    /**
     * @OA\Get(
     *   tags={"App"},
     *   path="/app/id/{appId}",
     *   summary="Get app information",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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
     * @return array
     * @throws \Exception
     */
    public function getAppId(Request $request): array
    {
        return [];
    }

    /**
     * @OA\Put(
     *   tags={"App"},
     *   path="/app/id/{appId}/change",
     *   summary="Application change by id",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="title", type="string", example="My changed title application", description="Title for application"),
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
     * @return array
     * @throws \Exception
     */
    public function putAppIdChange(Request $request): array
    {
        return [];
    }

    /**
     * @OA\Put(
     *   tags={"App"},
     *   path="/app/id/{appId}/change-keys",
     *   summary="Application change by id private/public keys",
     *   deprecated=false,
     *   security={{"XUserTokenScheme":{}}},
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
     * @return array
     * @throws \Exception
     */
    public function putAppIdChangeKeys(Request $request): array
    {
        return [];
    }
}