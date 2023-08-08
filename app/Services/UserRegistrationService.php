<?php

namespace App\Services;

use App\Helpers\EmailMobileHelper;
use App\Model\Database\CountryMobilePrefixModel;
use App\Model\Database\EmailModel;
use App\Model\Database\MobileModel;
use App\Model\Database\UserEmailModel;
use App\Model\Database\UserMobileModel;
use App\Model\Database\UserModel;
use Psr\Log\LoggerInterface;
use YusamHub\AppExt\Redis\RedisKernel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

class UserRegistrationService
{
    const REGISTRATION_BY_EMAIL = 1;
    const REGISTRATION_BY_MOBILE = 2;

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $email
     * @param string $publicKey
     * @return UserModel
     * @throws \Throwable
     */
    public static function addUserByEmailOrFail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email,
        string $publicKey
    ): UserModel
    {
        $pdoExtKernel->pdoExt()->beginTransactionDepth();

        try {
            $emailModel = EmailModel::findOrCreateEmail($pdoExtKernel, $email);

            if (is_null($emailModel->verifiedAt)) {
                $emailModel->verifiedAt = app_ext_date();
                $emailModel->saveOrFail();
            }

            $userModel = new UserModel();
            $userModel->setPdoExtKernel($pdoExtKernel);
            $userModel->keyHash = md5($publicKey);
            $userModel->publicKey = $publicKey;
            $userModel->saveOrFail();

            $userEmailModel = new UserEmailModel();
            $userEmailModel->setPdoExtKernel($pdoExtKernel);
            $userEmailModel->userId = $userModel->id;
            $userEmailModel->emailId = $emailModel->id;
            $userEmailModel->saveOrFail();

            $pdoExtKernel->pdoExt()->commitTransactionDepth();

        } catch (\Throwable $e) {
            $pdoExtKernel->pdoExt()->rollBackTransactionDepth();
            throw $e;
        }
        return $userModel;
    }

    /**
     * @param RedisKernel $redisKernel
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param LoggerInterface $logger
     * @param string $emailOrMobile
     * @param $mobilePrefix
     * @param $num
     * @return int|null
     */
    public static function getRegistrationType(
        RedisKernel $redisKernel,
        PdoExtKernelInterface $pdoExtKernel,
        LoggerInterface $logger,
        string $emailOrMobile,
        &$mobilePrefix,
        &$num
    ): ?int
    {
        $mobilePrefix = null;
        $num = null;
        if (EmailMobileHelper::isEmail($emailOrMobile)) {
            return self::REGISTRATION_BY_EMAIL;
        } elseif (EmailMobileHelper::isMobile($redisKernel, $pdoExtKernel, $logger, $emailOrMobile, $mobilePrefix, $num)) {
            return self::REGISTRATION_BY_MOBILE;
        }
        return null;
    }



    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $mobilePrefix
     * @param string $num
     * @param string $publicKey
     * @return UserModel
     * @throws \Throwable
     */
    public static function addUserByMobileOrFail(
        PdoExtKernelInterface $pdoExtKernel,
        string $mobilePrefix,
        string $num,
        string $publicKey
    ): UserModel
    {
        $pdoExtKernel->pdoExt()->beginTransactionDepth();

        try {
            $mobileModel = MobileModel::findOrCreateMobile($pdoExtKernel, $mobilePrefix, $num);

            if (is_null($mobileModel->verifiedAt)) {
                $mobileModel->verifiedAt = app_ext_date();
                $mobileModel->saveOrFail();
            }

            $userModel = new UserModel();
            $userModel->setPdoExtKernel($pdoExtKernel);
            $userModel->keyHash = md5($publicKey);
            $userModel->publicKey = $publicKey;
            $userModel->saveOrFail();

            $userMobileModel = new UserMobileModel();
            $userMobileModel->setPdoExtKernel($pdoExtKernel);
            $userMobileModel->userId = $userModel->id;
            $userMobileModel->mobileId = $mobileModel->id;
            $userMobileModel->saveOrFail();

            $pdoExtKernel->pdoExt()->commitTransactionDepth();

        } catch (\Throwable $e) {
            $pdoExtKernel->pdoExt()->rollBackTransactionDepth();
            throw $e;
        }
        return $userModel;
    }
}