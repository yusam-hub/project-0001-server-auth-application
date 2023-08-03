<?php

namespace App\ModelServices;

use App\Model\Database\EmailModel;
use App\Model\Database\UserEmailModel;
use App\Model\Database\UserModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

class RegistrationModelService
{
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
    ue.id
from :users_emails ue, users u, emails e
where
    ue.userId = u.id and ue.emailId = e.id
    and e.email = ?
limit 0,1
MYSQL;
        return $pdoExtKernel
            ->pdoExt()
            ->fetchOneColumn(strtr($sqlRow, [
                ':users' => TABLE_USERS
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
}