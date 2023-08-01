<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_USERS_MOBILES;
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
    `userId` bigint(20) unsigned NOT NULL COMMENT 'Пользователь',
    `mobileId` bigint(20) unsigned NOT NULL COMMENT 'Мобильный',
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата изменения записи',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_mobileId` (`mobileId`) USING BTREE,
    UNIQUE KEY `idx_userId_mobileId` (`userId`,`mobileId`) USING BTREE,
    CONSTRAINT `fkUserId_:table` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
    CONSTRAINT `fkMobileId_:table` FOREIGN KEY (`mobileId`) REFERENCES `mobiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Мобильные пользователей';

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table' => $this->tableName
        ]);
    }

};