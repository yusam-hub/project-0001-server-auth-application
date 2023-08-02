<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;

class EmailModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_EMAILS;

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
