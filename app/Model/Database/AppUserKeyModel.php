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

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
