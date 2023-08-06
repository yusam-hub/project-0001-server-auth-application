<?php

namespace App\Model\Database\UserConfigs;

use App\Model\Database\UserConfigModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property string $configName
 * @property string|null $configValue
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static AppTariffUserConfigModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppTariffUserConfigModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static AppTariffUserConfigModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static AppTariffUserConfigModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class AppTariffUserConfigModel extends UserConfigModel
{
    const CONFIG_NAME_KEY = 'app-tariff';

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param string $configName
     * @return AppTariffUserConfigModel|null
     */
    public static function configModelFind(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        string $configName = self::CONFIG_NAME_KEY
    ): ?static
    {
        return parent::configModelFind($pdoExtKernel, $userId, $configName);
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param int $userId
     * @param string $configName
     * @return AppTariffUserConfigModel
     */
    public static function configModelFindOrCreate(
        PdoExtKernelInterface $pdoExtKernel,
        int $userId,
        string $configName = self::CONFIG_NAME_KEY
    ): static
    {
        return parent::configModelFindOrCreate($pdoExtKernel, $userId, $configName);
    }
}