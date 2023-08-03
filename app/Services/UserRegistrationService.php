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
     * @return int|null
     */
    public static function findUserByEmail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email
    ): ?int
    {
        $email = strtolower($email);

        $sqlRow = <<<MYSQL
select 
    u.id
from 
    users_emails ue, users u, emails e
where
    ue.userId = u.id and ue.emailId = e.id
    and e.email = ?
limit 0,1
MYSQL;
        return $pdoExtKernel
            ->pdoExt()
            ->fetchOneColumn(strtr($sqlRow, [
            ]), 'id', [
                $email
            ]);
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $email
     * @return EmailModel
     */
    public static function findOrCreateEmail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email
    ): EmailModel
    {
        $email = strtolower($email);
        $emailModel = EmailModel::findModelByAttributes($pdoExtKernel, [
            'email' => $email
        ]);
        if (is_null($emailModel)) {
            $emailModel = new EmailModel();
            $emailModel->setPdoExtKernel($pdoExtKernel);
            $emailModel->email = $email;
            $emailModel->saveOrFail();
        }
        return $emailModel;
    }

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
            $emailModel = static::findOrCreateEmail($pdoExtKernel, $email);

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
     * @return int|null
     */
    public static function findUserByMobile(
        PdoExtKernelInterface $pdoExtKernel,
        string $mobilePrefix,
        string $num
    ): ?int
    {
        $sqlRow = <<<MYSQL
select 
    u.id
from 
    users_mobiles um, users u, mobiles m, country_mobile_prefixes cmp
where
    um.userId = u.id and um.mobileId = m.id and m.countryMobilePrefixId = cmp.id
    and cmp.mobilePrefix = ?
    and m.num = ?
limit 0,1
MYSQL;
        return $pdoExtKernel
            ->pdoExt()
            ->fetchOneColumn(strtr($sqlRow, [

            ]), 'id', [
                $mobilePrefix,
                $num
            ]);
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $mobilePrefix
     * @param string $num
     * @return MobileModel
     */
    public static function findOrCreateMobile(
        PdoExtKernelInterface $pdoExtKernel,
        string $mobilePrefix,
        string $num
    ): MobileModel
    {
        $countryMobilePrefixModel = CountryMobilePrefixModel::findModelByAttributesOrFail($pdoExtKernel, [
            'mobilePrefix' => $mobilePrefix
        ]);
        $mobileModel = MobileModel::findModelByAttributes($pdoExtKernel, [
            'countryMobilePrefixId' => $countryMobilePrefixModel->id,
            'num' => $num
        ]);
        if (is_null($mobileModel)) {
            $mobileModel = new MobileModel();
            $mobileModel->setPdoExtKernel($pdoExtKernel);
            $mobileModel->countryMobilePrefixId = $countryMobilePrefixModel->id;
            $mobileModel->num = $num;
            $mobileModel->saveOrFail();
        }
        return $mobileModel;
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
            $mobileModel = static::findOrCreateMobile($pdoExtKernel, $mobilePrefix, $num);

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