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
SET FOREIGN_KEY_CHECKS=0;

SET autocommit = 0;

START TRANSACTION;

TRUNCATE `:database`.`:table_emails`;

INSERT INTO `:database`.`:table_emails` (`id`,`email`,`verifiedAt`) VALUES(1,'root@root',NOW());

TRUNCATE `:database`.`:table_country_mobile_prefixes`;

INSERT INTO `:database`.`:table_country_mobile_prefixes` (`id`,`countryCode2`,`countryCode3`,`mobilePrefix`) VALUES(1,'RU','RUS','+7'),(2,'IN','IND','+91'),(3,'UA','UKR','+380');

TRUNCATE `:database`.`:table_mobiles`;

INSERT INTO `:database`.`:table_mobiles` (`id`,`countryMobilePrefixId`,`num`,`verifiedAt`) VALUES(1,1,'0000000000',NOW()),(1,2,'0000000000',NOW()),(1,3,'0000000000',NOW());

TRUNCATE `:database`.`:table_users`;

INSERT INTO `:database`.`:table_users` (`id`) VALUES(1);

TRUNCATE `:database`.`:table_users_emails`;

INSERT INTO `:database`.`:table_users_emails` (`id`,`userId`,`emailId`) VALUES(1,1,1);

TRUNCATE `:database`.`:table_users_mobiles`;

INSERT INTO `:database`.`:table_users_mobiles` (`id`,`userId`,`mobileId`) VALUES(1,1,1);

TRUNCATE `:database`.`:table_apps`;

INSERT INTO `:database`.`:table_apps` (`id`,`userId`,`title`) VALUES(1,1,'Root');

COMMIT;

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table_country_mobile_prefixes' => TABLE_COUNTRY_MOBILE_PREFIXES,
            ':table_emails' => TABLE_EMAILS,
            ':table_mobiles' => TABLE_MOBILES,
            ':table_users' => TABLE_USERS,
            ':table_users_emails' => TABLE_USERS_EMAILS,
            ':table_users_mobiles' => TABLE_USERS_MOBILES,
            ':table_apps' => TABLE_APPS,
        ]);
    }

};