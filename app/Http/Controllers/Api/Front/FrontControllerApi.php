<?php

namespace App\Http\Controllers\Api\Front;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class FrontControllerApi extends BaseApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_FRONT;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 600;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'GET'],sprintf('/api/%s', self::MODULE_CURRENT), 'getApiHome');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/register-by-email', self::MODULE_CURRENT), 'postUserRegisterByEmail');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/confirm-registration-by-email', self::MODULE_CURRENT), 'postUserConfirmRegistrationByEmail');
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
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function postUserRegisterByEmail(Request $request): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        HttpHelper::checkTooManyRequestsOrFail($this->getLogger(), $uniqueUserDevice,self::DEFAULT_TOO_MANY_REQUESTS_TTL, __METHOD__);

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

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        try {
            $savedData = [
                'email' => $validator->getAttribute('email'),
                'uniqueUserDevice' => $uniqueUserDevice,
                'otp' => random_int(10000, 99999)
            ];

            $hash = md5(microtime(true) . json_encode($savedData));

            app_ext_redis_global()->redisExt()->put($hash, $savedData, self::DEFAULT_TOO_MANY_REQUESTS_TTL);

            /**
             * todo: send email otp throw queue
             */

            $this->debug(__METHOD__, $savedData);

            return [
                'hash' => $hash,
            ];
        } catch (\Throwable $e) {

            if ($e instanceof HttpBadRequestAppExtRuntimeException) {
                throw $e;
            }

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/confirm-registration-by-email",
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
     *   @OA\Response(response=429, description="Too Many Requests", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/ResponseErrorDefault"))),
     * );
     */

    /**
     * @param Request $request
     * @return array
     */
    public function postUserConfirmRegistrationByEmail(Request $request): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        HttpHelper::checkTooManyRequestsOrFail($this->getLogger(), $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, __METHOD__);

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'email' => ['require','string','min:6','email'],
                'otp' => ['require','string','size:5'],
                'hash' => ['require','string','size:32', function($v) {
                    return app_ext_redis_global()->redisExt()->has($v);
                }],
            ]);
            $validator->setRuleMessages([
                'email' => 'Invalid value',
                'otp' => 'Invalid value',
                'hash' => 'Invalid value',
            ]);

            $validator->validateOrFail();

        } catch (\Throwable $e) {

            if ($e instanceof ValidatorException) {
                throw new HttpBadRequestAppExtRuntimeException($e->getValidatorErrors());
            }

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }

        try {

            $savedData = app_ext_redis_global()->redisExt()->get($validator->getAttribute('hash'));

            app_ext_redis_global()->redisExt()->del($validator->getAttribute('hash'));

            if (
                isset($savedData['uniqueUserDevice']) && $savedData['uniqueUserDevice'] === $uniqueUserDevice
                &&
                isset($savedData['email']) && $savedData['email'] === $validator->getAttribute('email')
                &&
                isset($savedData['otp']) && $savedData['otp'] == $validator->getAttribute('otp')
            ) {

                /**
                 * todo: если пользователь с почтой такой есть, то вернуть ошибку и отправть на восстановление
                 *
                 * todo: создать ключи, сохранить публичный, приватный отдать пользователю
                 *       добавить пользователя,
                 *       добавить почту
                 *       вернуть userId, privateKey
                 */
                return [

                ];
            }

            throw new HttpBadRequestAppExtRuntimeException([
                'email' => 'Invalid value',
                'otp' => 'Invalid value',
            ]);

        } catch (\Throwable $e) {

            if ($e instanceof HttpBadRequestAppExtRuntimeException) {
                throw $e;
            }

            $this->error($e->getMessage(), [
                'errorFile' => $e->getFile() . ':' . $e->getLine(),
                'errorTrace' => $e->getTrace()
            ]);

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }
    }
}