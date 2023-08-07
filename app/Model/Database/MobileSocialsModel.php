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
 * @method static MobileSocialsModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileSocialsModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileSocialsModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static MobileSocialsModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class MobileSocialsModel extends BasePdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_MOBILE_SOCIALS;

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
