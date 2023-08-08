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

TRUNCATE `:database`.`:table_country_mobile_prefixes`;

INSERT INTO `:database`.`:table_country_mobile_prefixes` (`id`,`countryCode2`,`countryCode3`,`mobilePrefix`) VALUES:table_country_mobile_prefix_values;

TRUNCATE `:database`.`:table_socials`;

INSERT INTO `:database`.`:table_socials` (`id`,`abbr`,`title`) VALUES:table_social_values;

COMMIT;

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table_country_mobile_prefixes' => TABLE_COUNTRY_MOBILE_PREFIXES,
            ':table_country_mobile_prefix_values' => implode(",", [
                "(1,'RU','RUS','+7')",
                "(2,'IN','IND','+91')",
                "(3,'UA','UKR','+380')",
            ]),
            ':table_socials' => TABLE_SOCIALS,
            ':table_social_values' => implode(",", [
                "(1,'telegram','Telegram')"
            ]),
        ]);
    }

};