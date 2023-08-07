<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_MOBILE_SOCIALS;
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
    `mobileId` bigint(20) unsigned NOT NULL COMMENT 'Ссылка на мобильный номер',
    `socialId` bigint(20) unsigned NOT NULL COMMENT 'Ссылка на социальную сеть',
    `socialExternalId` bigint(20) unsigned NOT NULL COMMENT 'Идентификатор в социальной сети',
    `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания записи',
    `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата изменения записи',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_mobileId_socialId_:table` (`mobileId`,`socialId`) USING BTREE,
    UNIQUE KEY `idx_socialId_socialExternalId:table` (`socialId`,`socialExternalId`) USING BTREE,    
    CONSTRAINT `fk_mobileId_:table` FOREIGN KEY (`mobileId`) REFERENCES `mobiles` (`id`),
    CONSTRAINT `fk_socialId_:table` FOREIGN KEY (`socialId`) REFERENCES `socials` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Связка мобильного с социальными сетями';

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table' => $this->tableName
        ]);
    }

};