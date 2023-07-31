<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_API_USERS;
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
    `apiToken` varchar(32) NOT NULL COMMENT 'API токен',
    `apiSign` varchar(255) NOT NULL COMMENT 'API подпись, секретный ключ',
    `description` varchar(32) NOT NULL COMMENT 'Описание',
    `blockedAt` datetime DEFAULT NULL COMMENT 'Дата блокировки',
    `blockedDescription` varchar(64) DEFAULT NULL COMMENT 'Описание блокировки',
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата изменения записи',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_apiToken` (`apiToken`) USING BTREE,
    KEY `idx_id_apiToken` (`id`,`apiToken`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API пользователи';
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table' => $this->tableName
        ]);
    }

};