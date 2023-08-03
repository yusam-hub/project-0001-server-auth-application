<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $appId
 * @property int $userId
 * @property string $deviceUuid
 * @property string $publicKey
 * @property string|null $lastUsedAt
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static AppUserKeyModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppUserKeyModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppUserKeyModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static AppUserKeyModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class AppUserKeyModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_APPS_USERS_KEYS;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_APP_ID = 'appId';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_DEVICE_UUID = 'deviceUuid';
    const ATTRIBUTE_NAME_PUBLIC_KEY = 'publicKey';
    const ATTRIBUTE_NAME_LAST_USED_AT = 'lastUsedAt';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';
    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
