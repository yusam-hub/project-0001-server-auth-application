<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property int $emailId
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static UserEmailModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserEmailModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserEmailModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static UserEmailModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class UserEmailModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_USERS_EMAILS;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_EMAIL_ID = 'emailId';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';
    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $email
     * @return int|null
     */
    public static function findUserIdByEmail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email
    ): ?int
    {
        $userModel = static::findUserModelByEmail($pdoExtKernel, $email);
        if (!is_null($userModel)) {
            return $userModel->id;
        }
        return null;
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $email
     * @return UserModel|null
     */
    public static function findUserModelByEmail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email
    ): ?UserModel
    {
        $email = strtolower($email);

        $sqlRow = <<<MYSQL
select 
    u.*
from 
    :current_table_name ue, users u, emails e
where
    ue.userId = u.id and ue.emailId = e.id and e.email = ?
limit 0,1
MYSQL;
        return $pdoExtKernel
            ->pdoExt()
            ->fetchOne(strtr($sqlRow, [
                ':current_table_name' => self::CURRENT_TABLE_NAME
            ]), [
                $email
            ], UserModel::class);
    }
}
