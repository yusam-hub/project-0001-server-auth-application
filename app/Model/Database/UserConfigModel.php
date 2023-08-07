<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\JsonExt\JsonObject;

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
abstract class UserConfigModel extends BasePdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_USER_CONFIGS;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_CONFIG_NAME = 'configName';
    const ATTRIBUTE_NAME_CONFIG_VALUE = 'configValue';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected ?object $configValueJsonObject = null;

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }

    public function modelExtInit(): void
    {
        parent::modelExtInit();
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

    public function modelExtAttributeGet(string $name, $default = null, bool $exceptionNotExists = true)
    {
        $value = parent::modelExtAttributeGet($name, $default, $exceptionNotExists);

        if ($name === self::ATTRIBUTE_NAME_CONFIG_VALUE) {

            if (is_null($this->configValueJsonObject)) {
                $this->configValueJsonObject = $this->newConfigValueJsonObject($value);
            }

            return $this->configValueJsonObject;

        }

        return $value;
    }

    abstract protected function newConfigValueJsonObject(string $value);

    /**
     * @throws \ReflectionException
     */
    protected function triggerBeforeMethodSave(): void
    {
        if ($this->configValueJsonObject instanceof JsonObject) {
            static::modelExtAttributeSet(self::ATTRIBUTE_NAME_CONFIG_VALUE, $this->configValueJsonObject->toJson());
            $this->configValueJsonObject = null;
        }
    }
}
