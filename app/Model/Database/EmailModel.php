<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property string $email
 * @property string|null $verifiedAt
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static EmailModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static EmailModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static EmailModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static EmailModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class EmailModel extends BasePdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_EMAILS;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_EMAIL = 'email';
    const ATTRIBUTE_NAME_VERIFIED_AT = 'verifiedAt';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
