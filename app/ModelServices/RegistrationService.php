<?php

namespace App\ModelServices;

use App\Helpers\EmailMobileHelper;
use App\Model\Database\EmailModel;
use App\Model\Database\UserEmailModel;
use App\Model\Database\UserModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

class RegistrationService
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
from users_emails ue, users u, emails e
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
            $emailModel->save();
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
                $emailModel->save();
            }

            $userModel = new UserModel();
            $userModel->setPdoExtKernel($pdoExtKernel);
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
     * @param string $emailOrMobile
     * @return int|null
     */
    public static function getRegistrationType(string $emailOrMobile): ?int
    {
        if (EmailMobileHelper::isEmail($emailOrMobile)) {
            return self::REGISTRATION_BY_EMAIL;
        } elseif (EmailMobileHelper::isMobile($emailOrMobile)) {
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
from users_mobiles um, users u, mobiles m, country_mobile_prefixes cmp
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
}