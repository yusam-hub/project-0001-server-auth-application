<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property string $title
 * @property string|null $keyHash
 * @property string|null $publicKey
 * @property string|null $serviceKey
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static AppModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static AppModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class AppModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_APPS;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_TITLE = 'title';
    const ATTRIBUTE_NAME_KEY_HASH = 'keyHash';
    const ATTRIBUTE_NAME_PUBLIC_KEY = 'publicKey';
    const ATTRIBUTE_NAME_SERVICE_KEY = 'serviceKey';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
