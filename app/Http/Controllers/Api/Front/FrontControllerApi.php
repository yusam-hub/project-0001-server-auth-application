<?php

namespace App\Http\Controllers\Api\Front;

use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class FrontControllerApi extends BaseApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_FRONT;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s', self::MODULE_CURRENT), 'getApiHome');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/register-by-email', self::MODULE_CURRENT), 'postUserRegisterByEmail');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/confirm-by-email', self::MODULE_CURRENT), 'postUserConfirmByEmail');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getApiHome(Request $request): array
    {
        return [];
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/register-by-email",
     *   summary="User register by e-mail",
     *   deprecated=false,
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="email", type="string", example="example@domain.zone", description="E-mail of new user registration"),
     *        ),
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     */
    public function postUserRegisterByEmail(Request $request): array
    {
        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'email' => ['require','string','min:6','email'],
            ]);
            $validator->setRuleMessages([
                'email' => 'Invalid value, require string, min 6 chars, valid email'
            ]);

            $validator->validateOrFail();
        } catch (ValidatorException $e) {
            throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors(), 'Invalid request');
        }

        return [
            'email' => $validator->getAttribute('email'),
            'hash' => md5('result of sending')
        ];
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/confirm-by-email",
     *   summary="User confirm registation by email",
     *   deprecated=false,
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="email", type="string", example="example@domain.zone", description="E-mail of new user registration"),
     *            @OA\Property(property="otp", type="string", example="", description="One time password"),
     *            @OA\Property(property="hash", type="string", example="", description="Hash string returned on registered user"),
     *        ),
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{}},
     *   ))),
     *   @OA\Response(response=400, description="Bad Request", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     *   @OA\Response(response=401, description="Unauthorized", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     */
    public function postUserConfirmOtpByEmail(Request $request): array
    {
        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'email' => ['require','string','min:6','email'],
                'otp' => ['require','string','min:6'],
                'hash' => ['require','string','min:6'],
            ]);
            $validator->setRuleMessages([
                'email' => 'Invalid value, require string, min 6 chars, valid email',
                'otp' => 'Invalid value, require string, min 6 chars',
                'hash' => 'Invalid value, require string, min 6 chars',
            ]);

            $validator->validateOrFail();
        } catch (ValidatorException $e) {
            throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors(), 'Invalid request');
        }

        return [
            'email' => $validator->getAttribute('email'),
            'otp' => $validator->getAttribute('otp')
        ];
    }
}