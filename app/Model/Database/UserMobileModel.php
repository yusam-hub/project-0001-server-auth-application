<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property int $mobileId
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static UserMobileModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserMobileModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserMobileModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static UserMobileModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class UserMobileModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_USERS_MOBILES;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_MOBILE_ID = 'mobileId';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
