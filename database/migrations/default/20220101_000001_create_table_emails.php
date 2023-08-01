<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_EMAILS;
    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->connectionName.'.dbName');
    }

    public function getQuery(): string
    {
        $query = <<<MYSQL
DROP TABLE IF EXISTS `:database`.`:table`;

CREATE TABLE IF NOT EXISTS `:database`.`:table` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `email` varchar(64) NOT NULL COMMENT 'E-mail',
    `verifiedAt` datetime DEFAULT NULL COMMENT 'Дата проверки',
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата изменения записи',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='E-mails';
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table' => $this->tableName
        ]);
    }

};