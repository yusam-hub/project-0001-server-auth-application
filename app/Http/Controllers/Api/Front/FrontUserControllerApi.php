<?php

namespace App\Http\Controllers\Api\Front;

use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use App\Model\Database\EmailModel;
use App\ModelServices\RegistrationModelService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\Helper\OpenSsl;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class FrontUserControllerApi extends BaseApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_FRONT;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 600;

    const OTP_ACTION_USER_REGISTER = 1;
    const OTP_ACTION_USER_REFRESH_KEYS = 2;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/init-registration-by-email', self::MODULE_CURRENT), 'postUserInitRegistrationByEmail');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/confirm-registration-by-email', self::MODULE_CURRENT), 'postUserConfirmRegistrationByEmail');
    }

    /**
     * @param Request $request
     * @param int $otpAction
     * @param string $method
     * @return array
     */
    public function sendOtpForAction(Request $request, int $otpAction, string $method): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        HttpHelper::checkTooManyRequestsOrFail(
            $this->getRedisKernel(),
            $this->getLogger(),
            $uniqueUserDevice,self::DEFAULT_TOO_MANY_REQUESTS_TTL, $method);

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'email' => ['require','string','min:6','email'],
            ]);
            $validator->setRuleMessages([
                'email' => 'Invalid value, require string, min 6 chars, valid email',
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
                'uniqueUserDevice' => $uniqueUserDevice,
                'otpAction' => $otpAction,
                'email' => strtolower($validator->getAttribute('email')),
                'otp' => random_int(10000, 99999)
            ];

            $hash = md5(microtime(true) . json_encode($savedData));

            $this->getRedisKernel()->redisExt()->put($hash, $savedData, self::DEFAULT_TOO_MANY_REQUESTS_TTL);

            app_ext_logger('email')->debug(__METHOD__, $savedData);

            /**
             * todo: send email otp throw queue
             */

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
     * @param Request $request
     * @param int $otpAction
     * @param string $method
     * @param callable $callback
     * @return array
     */
    public function confirmOtpForAction(Request $request, int $otpAction, string $method, callable $callback): array
    {
        $uniqueUserDevice = HttpHelper::getUniqueUserDeviceFromRequest($request);

        HttpHelper::checkTooManyRequestsOrFail(
            $this->getRedisKernel(),
            $this->getLogger(),
            $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, $method);

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'email' => ['require','string','min:6','email'],
                'hash' => ['require','string','size:32', function($v) {
                    return $this->getRedisKernel()->redisExt()->has($v);
                }],
                'otp' => ['require','string','size:5'],
            ]);
            $validator->setRuleMessages([
                'email' => 'Invalid value, require string, min 6 chars, valid email',
                'hash' => 'Invalid value',
                'otp' => 'Invalid value',
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

            $savedData = $this->getRedisKernel()->redisExt()->get($validator->getAttribute('hash'));

            $this->getRedisKernel()->redisExt()->del($validator->getAttribute('hash'));

            if (
                isset($savedData['uniqueUserDevice']) && $savedData['uniqueUserDevice'] == $uniqueUserDevice
                &&
                isset($savedData['otpAction']) && $savedData['otpAction'] == $otpAction
                &&
                isset($savedData['email']) && $savedData['email'] == strtolower($validator->getAttribute('email'))
                &&
                isset($savedData['otp']) && $savedData['otp'] == $validator->getAttribute('otp')
            ) {
                return call_user_func_array($callback,[$validator]);
            }

            throw new HttpBadRequestAppExtRuntimeException([
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

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/init-registration-by-email",
     *   summary="User init registration by e-mail",
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
     *        example={"status":"ok","data":{"hash":"string"}},
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
    public function postUserInitRegistrationByEmail(Request $request): array
    {
        return $this->sendOtpForAction($request, self::OTP_ACTION_USER_REGISTER, __METHOD__);
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
     *            @OA\Property(property="hash", type="string", example="", description="Hash string returned on registered user"),
     *            @OA\Property(property="otp", type="string", example="", description="One time password"),
     *        ),
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(
     *        @OA\Property(property="status", type="string", example="ok"),
     *        @OA\Property(property="data", type="array", example="array", @OA\Items(
     *        )),
     *        example={"status":"ok","data":{"userId":"integer","privateKey":"string"}},
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
        return $this->confirmOtpForAction($request, self::OTP_ACTION_USER_REGISTER, __METHOD__, function(Validator $validator) {
            if (RegistrationModelService::findUserByEmail($this->getPdoExtKernel(), $validator->getAttribute('email'))) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'email' => 'Registration fail, email with user is exists',
                ]);
            }

            $openSsl = (new OpenSsl())->newPrivatePublicKeys();

            $userModel = RegistrationModelService::addUserByEmailOrFail(
                $this->getPdoExtKernel(),
                $validator->getAttribute('email'),
                $openSsl->getPublicKey(),
            );

            return [
                'userId' => $userModel->id,
                'privateKey' => $openSsl->getPrivateKey(),
            ];
        });
    }
}