<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property string $configName
 * @property string|null $configValue
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static UserConfigModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserConfigModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserConfigModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static UserConfigModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class UserConfigModel extends BasePdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_USER_CONFIGS;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_CONFIG_NAME = 'configName';
    const ATTRIBUTE_NAME_CONFIG_VALUE = 'configValue';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param string $configName
     * @return UserConfigModel|null
     */
    public static function configModelFind(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        string $configName
    ): ?static
    {
        $model = static::findModelByAttributes(
            $pdoExtKernel,
            [
                'userId' => $userId,
                'configName' => $configName
            ]
        );
        if (!is_null($model)) {
            return $model;
        }
        return null;
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param string $configName
     * @return UserConfigModel
     */
    public static function configModelFindOrCreate(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        string $configName
    ): static
    {
        $model = static::configModelFind(
            $pdoExtKernel,
            $userId,
            $configName
        );
        if (is_null($model)) {
            $model = new static();
            $model->setPdoExtKernel($pdoExtKernel);
            $model->userId = $userId;
            $model->configName = $configName;
        }
        return $model;
    }
}
