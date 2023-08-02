<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property string $title
 * @property string $publicKey
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static AppModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static AppModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class AppModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_APPS;

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
