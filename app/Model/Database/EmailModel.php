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
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_EMAILS;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_EMAIL = 'email';
    const ATTRIBUTE_NAME_VERIFIED_AT = 'verifiedAt';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $email
     * @return int|null
     */
    public static function getIdByEmail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email
    ): ?int
    {
        $sqlRow = <<<MYSQL
select 
    id
from
    :current_table_name
where
    email = ?
limit 0,1
MYSQL;
                return $pdoExtKernel
                    ->pdoExt(self::CURRENT_CONNECTION_NAME)
                    ->fetchOneColumn(
                        strtr($sqlRow, [
                            ':current_table_name' => self::CURRENT_TABLE_NAME
                        ]),
                        'id',
                        [
                            strtolower($email)
                        ]
                    );
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $email
     * @return EmailModel
     */
    public static function findOrCreateEmail(
        PdoExtKernelInterface $pdoExtKernel,
        string $email
    ): EmailModel
    {
        $email = strtolower($email);
        $emailModel = EmailModel::findModelByAttributes($pdoExtKernel, [
            'email' => $email
        ]);
        if (is_null($emailModel)) {
            $emailModel = new EmailModel();
            $emailModel->setPdoExtKernel($pdoExtKernel);
            $emailModel->email = $email;
            $emailModel->saveOrFail();
        }
        return $emailModel;
    }
}
