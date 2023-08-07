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

INSERT INTO `:database`.`:table_emails` (`id`,`email`,`verifiedAt`) VALUES:table_email_values;

TRUNCATE `:database`.`:table_country_mobile_prefixes`;

INSERT INTO `:database`.`:table_country_mobile_prefixes` (`id`,`countryCode2`,`countryCode3`,`mobilePrefix`) VALUES:table_country_mobile_prefix_values;

TRUNCATE `:database`.`:table_mobiles`;

INSERT INTO `:database`.`:table_mobiles` (`id`,`countryMobilePrefixId`,`num`,`verifiedAt`) VALUES:table_mobile_values;

TRUNCATE `:database`.`:table_users`;

INSERT INTO `:database`.`:table_users` (`id`) VALUES(1);

TRUNCATE `:database`.`:table_users_emails`;

INSERT INTO `:database`.`:table_users_emails` (`id`,`userId`,`emailId`) VALUES(1,1,1);

TRUNCATE `:database`.`:table_users_mobiles`;

INSERT INTO `:database`.`:table_users_mobiles` (`id`,`userId`,`mobileId`) VALUES(1,1,1);

TRUNCATE `:database`.`:table_apps`;

INSERT INTO `:database`.`:table_apps` (`id`,`userId`,`title`) VALUES(1,1,'Root');

TRUNCATE `:database`.`:table_user_configs`;

INSERT INTO `:database`.`:table_user_configs` (`id`,`userId`,`configName`,`configValue`) VALUES:table_user_config_values;

TRUNCATE `:database`.`:table_socials`;

INSERT INTO `:database`.`:table_socials` (`id`,`abbr`,`title`) VALUES(1,'telegram','Telegram');

TRUNCATE `:database`.`:table_mobile_socials`;

INSERT INTO `:database`.`:table_mobile_socials` (`id`,`mobileId`,`socialId`,`socialExternalId`) VALUES:table_mobile_social_values;

COMMIT;

SET FOREIGN_KEY_CHECKS=1;
MYSQL;

        $rootEmail = app_ext_config('seeds.rootEmail');
        $rootMobile = app_ext_config('seeds.rootMobile');
        $rootTelegramId = app_ext_config('seeds.rootTelegramId');

        return strtr($query, [
            ':database' => $this->getDatabaseName(),
            ':table_country_mobile_prefixes' => TABLE_COUNTRY_MOBILE_PREFIXES,
            ':table_country_mobile_prefix_values' => implode(",", [
                "(1,'RU','RUS','+7')",
                "(2,'IN','IND','+91')",
                "(3,'UA','UKR','+380')",
            ]),
            ':table_emails' => TABLE_EMAILS,
            ':table_email_values' => implode(",", [
                sprintf("(1,'%s',NOW())", $rootEmail)
            ]),
            ':table_mobiles' => TABLE_MOBILES,
            ':table_mobile_values' => implode(",", [
                sprintf("(1,%d,'%s',NOW())", 1, ltrim($rootMobile, '+7'))
            ]),
            ':table_users' => TABLE_USERS,
            ':table_users_emails' => TABLE_USERS_EMAILS,
            ':table_users_mobiles' => TABLE_USERS_MOBILES,
            ':table_apps' => TABLE_APPS,
            ':table_user_configs' => TABLE_USER_CONFIGS,
            ':table_user_config_values' => implode(",", [
                sprintf("(1,1,'app-tariff','%s')",
                    json_encode(
                        (array) (
                            new \App\Model\Database\UserConfigs\AppTariffProperties([
                                'maxAllowApplications' => 1000
                            ])
                        )
                    )
                )
            ]),
            ':table_socials' => TABLE_SOCIALS,
            ':table_mobile_socials' => TABLE_MOBILE_SOCIALS,
            ':table_mobile_social_values' => implode(",", [
                sprintf("(1,1,1,%d)", $rootTelegramId)
            ]),
        ]);
    }

};