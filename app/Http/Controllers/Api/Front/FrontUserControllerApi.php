<?php

namespace App\Http\Controllers\Api\Front;

use App\Helpers\HttpHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use App\Model\Database\EmailModel;
use App\Model\Database\UserModel;
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
    const OTP_ACTION_USER_RESTORE_REGISTER = 2;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/init-registration', self::MODULE_CURRENT), 'postUserInitRegistration');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/confirm-registration', self::MODULE_CURRENT), 'postUserConfirmRegistration');

        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/init-restore-registration', self::MODULE_CURRENT), 'postUserInitRestoreRegistration');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/user/confirm-restore-registration', self::MODULE_CURRENT), 'postUserConfirmRestoreRegistration');

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
                'emailOrMobile' => ['require','string', function($v) {
                    return StringHelper::isEmail($v);
                }],
            ]);
            $validator->setRuleMessages([
                'emailOrMobile' => 'Invalid value, require valid email or mobile prefix + 10 digits',
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
            $redisData = [
                'uniqueUserDevice' => $uniqueUserDevice,
                'otpAction' => $otpAction,
                'emailOrMobile' => strtolower($validator->getAttribute('emailOrMobile')),
                'otp' => random_int(10000, 99999)
            ];

            $hash = md5(microtime(true) . json_encode($redisData));

            $this->getRedisKernel()->redisExt()->put($hash, $redisData, self::DEFAULT_TOO_MANY_REQUESTS_TTL);

            app_ext_logger('otp')->debug(__METHOD__, $redisData);

            /**
             * todo: send email or mobile otp throw queue
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
                'emailOrMobile' => ['require','string', function($v) {
                    return StringHelper::isEmail($v);
                }],
                'hash' => ['require','string','size:32', function($v) {
                    return $this->getRedisKernel()->redisExt()->has($v);
                }],
                'otp' => ['require','string','size:5'],
            ]);
            $validator->setRuleMessages([
                'emailOrMobile' => 'Invalid value, require valid email or mobile prefix + 10 digits',
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

            $redisData = $this->getRedisKernel()->redisExt()->get($validator->getAttribute('hash'));

            $this->getRedisKernel()->redisExt()->del($validator->getAttribute('hash'));

            if (
                isset($redisData['uniqueUserDevice']) && $redisData['uniqueUserDevice'] == $uniqueUserDevice
                &&
                isset($redisData['otpAction']) && $redisData['otpAction'] == $otpAction
                &&
                isset($redisData['emailOrMobile']) && $redisData['emailOrMobile'] == strtolower($validator->getAttribute('emailOrMobile'))
                &&
                isset($redisData['otp']) && $redisData['otp'] == $validator->getAttribute('otp')
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
     *   path="/user/init-registration",
     *   summary="User init registration",
     *   deprecated=false,
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="emailOrMobile", type="string", example="example@domain.zone|+73337777777", description="E-mail or mobile of new user registration"),
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
    public function postUserInitRegistration(Request $request): array
    {
        return $this->sendOtpForAction($request, self::OTP_ACTION_USER_REGISTER, __METHOD__);
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/confirm-registration",
     *   summary="User confirm registation",
     *   deprecated=false,
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="emailOrMobile", type="string", example="example@domain.zone|+73337777777", description="E-mail or mobile of new user registration"),
     *            @OA\Property(property="hash", type="string", example="", description="Hash string returned on init registration"),
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
    public function postUserConfirmRegistration(Request $request): array
    {
        return $this->confirmOtpForAction($request, self::OTP_ACTION_USER_REGISTER, __METHOD__, function(Validator $validator) {
            if (RegistrationModelService::findUserByEmail($this->getPdoExtKernel(), $validator->getAttribute('emailOrMobile'))) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'emailOrMobile' => 'Registration fail, email with user is exists',
                ]);
            }

            $openSsl = (new OpenSsl())->newPrivatePublicKeys();

            $userModel = RegistrationModelService::addUserByEmailOrFail(
                $this->getPdoExtKernel(),
                $validator->getAttribute('emailOrMobile'),
                $openSsl->getPublicKey(),
            );

            return [
                'userId' => $userModel->id,
                'privateKey' => $openSsl->getPrivateKey(),
            ];
        });
    }


    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/init-restore-registration",
     *   summary="User init restore registration",
     *   deprecated=false,
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="emailOrMobile", type="string", example="example@domain.zone|+73337777777", description="E-mail or mobile of exists user"),
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
    public function postUserInitRestoreRegistration(Request $request): array
    {
        return $this->sendOtpForAction($request, self::OTP_ACTION_USER_RESTORE_REGISTER, __METHOD__);
    }

    /**
     * @OA\Post(
     *   tags={"User"},
     *   path="/user/confirm-restore-registration",
     *   summary="User confirm restore registation",
     *   deprecated=false,
     *   @OA\RequestBody(description="Properties", required=true,
     *        @OA\JsonContent(type="object",
     *            @OA\Property(property="emailOrMobile", type="string", example="example@domain.zone|+73337777777", description="E-mail or mobile of exists user"),
     *            @OA\Property(property="hash", type="string", example="", description="Hash string returned on init registration"),
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
    public function postUserConfirmRestoreRegistration(Request $request): array
    {
        return $this->confirmOtpForAction($request, self::OTP_ACTION_USER_RESTORE_REGISTER, __METHOD__, function(Validator $validator) {
            $userId = RegistrationModelService::findUserByEmail($this->getPdoExtKernel(), $validator->getAttribute('emailOrMobile'));
            if (is_null($userId)) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'emailOrMobile' => 'Restore registration fail, email with user is not exists',
                ]);
            }

            $openSsl = (new OpenSsl())->newPrivatePublicKeys();

            $userModel = UserModel::findModel($this->getPdoExtKernel(), $userId);
            if (is_null($userModel)) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'emailOrMobile' => 'Restore registration fail, user is not exists',
                ]);
            }

            $userModel->publicKey = $openSsl->getPrivateKey();
            $userModel->saveOrFail();

            return [
                'userId' => $userModel->id,
                'privateKey' => $openSsl->getPrivateKey(),
            ];
        });
    }
}