<?php

namespace App\Http\Controllers\Api\User;

use App\Console\Daemons\RedisQueueJobs\RedisQueueOtpSendJob;
use App\Helpers\HttpHelper;
use App\Http\Controllers\Api\ApiSwaggerController;
use App\Http\Controllers\Api\BaseApiHttpController;
use App\Model\Database\UserEmailModel;
use App\Model\Database\UserMobileModel;
use App\Model\Database\UserModel;
use App\Services\UserRegistrationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use YusamHub\AppExt\Exceptions\HttpBadRequestAppExtRuntimeException;
use YusamHub\AppExt\Exceptions\HttpInternalServerErrorAppExtRuntimeException;
use YusamHub\AppExt\Helpers\ExceptionHelper;
use YusamHub\Helper\OpenSsl;
use YusamHub\Validator\Validator;
use YusamHub\Validator\ValidatorException;

class UserAccountControllerApi extends BaseApiHttpController
{
    const MODULE_CURRENT = ApiSwaggerController::MODULE_USER;
    const TO_MANY_REQUESTS_CHECK_ENABLED = false;
    const DEFAULT_TOO_MANY_REQUESTS_TTL = 600;
    const OTP_ACTION_USER_REGISTER = 1;
    const OTP_ACTION_USER_RESTORE_REGISTER = 2;

    public static function routesRegister(RoutingConfigurator $routes): void
    {
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/account/init-registration', self::MODULE_CURRENT), 'postAccountInitRegistration');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/account/confirm-registration', self::MODULE_CURRENT), 'postAccountConfirmRegistration');

        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/account/init-restore-registration', self::MODULE_CURRENT), 'postAccountInitRestoreRegistration');
        static::routesAdd($routes, ['OPTIONS', 'POST'],sprintf('/api/%s/account/confirm-restore-registration', self::MODULE_CURRENT), 'postAccountConfirmRestoreRegistration');

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

        if (self::TO_MANY_REQUESTS_CHECK_ENABLED) {
            HttpHelper::checkTooManyRequestsOrFail(
                $this->getRedisKernel(),
                $this->getLogger(),
                $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, $method);
        }

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'emailOrMobile' => ['require','string', function($v)  {
                    return !is_null(UserRegistrationService::getRegistrationType($this->getRedisKernel(), $this->getPdoExtKernel(), $this->getLogger(), $v, $mobilePrefix, $num, $mobilePrefixId));
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

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

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

            RedisQueueOtpSendJob::push($redisData['emailOrMobile'], $redisData['otp']);

            return [
                'hash' => $hash,
            ];
        } catch (\Throwable $e) {

            if ($e instanceof HttpBadRequestAppExtRuntimeException) {
                throw $e;
            }

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

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

        if (self::TO_MANY_REQUESTS_CHECK_ENABLED) {
            HttpHelper::checkTooManyRequestsOrFail(
                $this->getRedisKernel(),
                $this->getLogger(),
                $uniqueUserDevice, self::DEFAULT_TOO_MANY_REQUESTS_TTL, $method);
        }

        try {
            $validator = new Validator();
            $validator->setAttributes(
                $request->request->all()
            );
            $validator->setRules([
                'emailOrMobile' => ['require','string', function($v) {
                    return !is_null(UserRegistrationService::getRegistrationType($this->getRedisKernel(), $this->getPdoExtKernel(), $this->getLogger(), $v, $mobilePrefix, $num, $mobilePrefixId));
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

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

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

            $this->error($e->getMessage(), ExceptionHelper::e2a($e));

            throw new HttpInternalServerErrorAppExtRuntimeException();
        }
    }

    /**
     * @OA\Post(
     *   tags={"Account"},
     *   path="/account/init-registration",
     *   summary="Account init registration",
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
    public function postAccountInitRegistration(Request $request): array
    {
        return $this->sendOtpForAction($request, self::OTP_ACTION_USER_REGISTER, __METHOD__);
    }

    /**
     * @OA\Post(
     *   tags={"Account"},
     *   path="/account/confirm-registration",
     *   summary="Account confirm registation",
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
    public function postAccountConfirmRegistration(Request $request): array
    {
        return $this->confirmOtpForAction($request, self::OTP_ACTION_USER_REGISTER, __METHOD__, function(Validator $validator)
        {
            $registrationType = UserRegistrationService::getRegistrationType(
                $this->getRedisKernel(), $this->getPdoExtKernel(), $this->getLogger(),
                $validator->getAttribute('emailOrMobile'),
                $mobilePrefix,
                $num,
                $mobilePrefixId
            );

            if ($registrationType === UserRegistrationService::REGISTRATION_BY_EMAIL) {
                if (UserEmailModel::findUserIdByEmail($this->getPdoExtKernel(), $validator->getAttribute('emailOrMobile'))) {
                    throw new HttpBadRequestAppExtRuntimeException([
                        'emailOrMobile' => 'Registration fail, user is exists',
                    ]);
                }

                $openSsl = (new OpenSsl())->newPrivatePublicKeys();

                $userModel = UserRegistrationService::addUserByEmailOrFail(
                    $this->getPdoExtKernel(),
                    $validator->getAttribute('emailOrMobile'),
                    $openSsl->getPublicKey(),
                );

            } else {
                if (UserMobileModel::findUserIdByMobile($this->getPdoExtKernel(), '', $validator->getAttribute('emailOrMobile'))) {
                    throw new HttpBadRequestAppExtRuntimeException([
                        'emailOrMobile' => 'Registration fail, user is exists',
                    ]);
                }

                $openSsl = (new OpenSsl())->newPrivatePublicKeys();

                $userModel = UserRegistrationService::addUserByMobileOrFail(
                    $this->getPdoExtKernel(),
                    $mobilePrefix,
                    $num,
                    $openSsl->getPublicKey(),
                );
            }

            return [
                'userId' => $userModel->id,
                'keyHash' => $userModel->keyHash,
                'privateKey' => $openSsl->getPrivateKey(),
            ];
        });
    }


    /**
     * @OA\Post(
     *   tags={"Account"},
     *   path="/account/init-restore-registration",
     *   summary="Account init restore registration",
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
    public function postAccountInitRestoreRegistration(Request $request): array
    {
        return $this->sendOtpForAction($request, self::OTP_ACTION_USER_RESTORE_REGISTER, __METHOD__);
    }

    /**
     * @OA\Post(
     *   tags={"Account"},
     *   path="/account/confirm-restore-registration",
     *   summary="Account confirm restore registation",
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
    public function postAccountConfirmRestoreRegistration(Request $request): array
    {
        return $this->confirmOtpForAction($request, self::OTP_ACTION_USER_RESTORE_REGISTER, __METHOD__, function(Validator $validator)
        {
            $registrationType = UserRegistrationService::getRegistrationType(
                $this->getRedisKernel(), $this->getPdoExtKernel(), $this->getLogger(),
                $validator->getAttribute('emailOrMobile'),
                $mobilePrefix,
                $num,
                $mobilePrefixId
            );

            if ($registrationType === UserRegistrationService::REGISTRATION_BY_EMAIL) {
                $userId = UserEmailModel::findUserIdByEmail($this->getPdoExtKernel(), $validator->getAttribute('emailOrMobile'));
            } else {
                $userId = UserMobileModel::findUserIdByMobile($this->getPdoExtKernel(), $mobilePrefix, $num);
            }

            if (is_null($userId)) {
                throw new HttpBadRequestAppExtRuntimeException([
                    'emailOrMobile' => 'Restore registration fail, user is not exists',
                ]);
            }

            $openSsl = (new OpenSsl())->newPrivatePublicKeys();

            $userModel = UserModel::findModelOrFail($this->getPdoExtKernel(), $userId);
            $userModel->publicKey = $openSsl->getPublicKey();
            $userModel->keyHash = md5($userModel->publicKey);
            $userModel->saveOrFail();

            return [
                'userId' => $userModel->id,
                'keyHash' => $userModel->keyHash,
                'privateKey' => $openSsl->getPrivateKey(),
            ];
        });
    }
}