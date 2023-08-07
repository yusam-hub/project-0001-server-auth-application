<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property string $abbr
 * @property string $title
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static SocialsModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static SocialsModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static SocialsModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static SocialsModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class SocialsModel extends BasePdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_SOCIALS;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_ABBR = 'abbr';
    const ATTRIBUTE_NAME_TITLE = 'title';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    const SOCIAL_TELEGRAM_ABBR = 'telegram';
    const SOCIALS = [
        self::SOCIAL_TELEGRAM_ABBR
    ];

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
