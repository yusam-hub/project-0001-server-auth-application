<?php

return new class {

    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->connectionName.'.dbName');
    }

    public function getQuery(): string
    {
        $query = <<<MYSQL
SET autocommit = 0;

START TRANSACTION;

INSERT INTO `:database`.`:table_emails` (`id`,`email`,`verifiedAt`) VALUES(1,'yusam@yandex.ru',NOW());

INSERT INTO `:database`.`:table_country_mobile_prefixes` (`id`,`countryCode2`,`countryCode3`,`mobilePrefix`) VALUES(1,'RU','RUS','+7'),(2,'IN','IND','+91'),(3,'UA','UKR','+380');

INSERT INTO `:database`.`:table_mobiles` (`id`,`countryMobilePrefixId`,`num`,`verifiedAt`) VALUES(1,1,'9376448660',NOW());

INSERT INTO `:database`.`:table_users` (`id`) VALUES(1);

INSERT INTO `:database`.`:table_users_emails` (`id`,`userId`,`emailId`) VALUES(1,1,1);

INSERT INTO `:database`.`:table_users_mobiles` (`id`,`userId`,`mobileId`) VALUES(1,1,1);

COMMIT;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table_country_mobile_prefixes' => TABLE_COUNTRY_MOBILE_PREFIXES,
            ':table_emails' => TABLE_EMAILS,
            ':table_mobiles' => TABLE_MOBILES,
            ':table_users' => TABLE_USERS,
            ':table_users_emails' => TABLE_USERS_EMAILS,
            ':table_users_mobiles' => TABLE_USERS_MOBILES,
        ]);
    }

};