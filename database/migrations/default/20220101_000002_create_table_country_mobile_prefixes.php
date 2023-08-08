<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_COUNTRY_MOBILE_PREFIXES;
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
    `countryCode2` varchar(2) NOT NULL COMMENT 'Код страны 2',
    `countryCode3` varchar(3) NOT NULL COMMENT 'Код страны 3',
    `mobilePrefix` varchar(10) NOT NULL COMMENT 'Префикс страны',
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата изменения записи',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_countryCode2_:table` (`countryCode2`) USING BTREE,
    UNIQUE KEY `idx_countryCode3_:table` (`countryCode3`) USING BTREE,
    UNIQUE KEY `idx_mobilePrefix_:table` (`countryCode3`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочник мобильные префиксы стран';

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table' => $this->tableName
        ]);
    }

};