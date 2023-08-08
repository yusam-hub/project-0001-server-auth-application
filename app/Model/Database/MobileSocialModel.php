<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $mobileId
 * @property int $socialId
 * @property int $socialExternalId
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static MobileSocialModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileSocialModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileSocialModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static MobileSocialModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class MobileSocialModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_MOBILE_SOCIALS;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_MOBILE_ID = 'mobileId';
    const ATTRIBUTE_NAME_SOCIAL_ID = 'socialId';
    const ATTRIBUTE_NAME_SOCIAL_EXTERNAL_ID = 'socialExternalId';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}