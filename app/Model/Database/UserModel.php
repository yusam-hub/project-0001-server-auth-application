<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property string|null $keyHash
 * @property string|null $publicKey
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static UserModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static UserModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class UserModel extends BasePdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_USERS;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_KEY_HASH = 'keyHash';
    const ATTRIBUTE_NAME_PUBLIC_KEY = 'publicKey';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';
    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
