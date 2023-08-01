<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_APPS_USERS_KEYS;
    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->connectionName.'.dbName');
    }

    public function getQuery(): string
    {
        $query = <<<MYSQL
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `:database`.`:table`;

CREATE TABLE IF NOT EXISTS `:database`.`:table` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `appId` bigint(20) unsigned NOT NULL COMMENT 'Приложение',
    `userId` bigint(20) unsigned NOT NULL COMMENT 'Пользователь',
    `publicKey` text DEFAULT NULL COMMENT 'Публичный ключ',
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата изменения записи',
    PRIMARY KEY (`id`),
    KEY `idx_userId` (`userId`) USING BTREE,
    UNIQUE KEY `idx_appId_userId` (`appId`,`userId`) USING BTREE,
    CONSTRAINT `fkAppId_:table` FOREIGN KEY (`appId`) REFERENCES `apps` (`id`),
    CONSTRAINT `fkUserId_:table` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ключи пользователей для приложений';

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table' => $this->tableName
        ]);
    }

};