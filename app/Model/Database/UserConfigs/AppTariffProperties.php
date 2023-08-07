<?php

namespace App\Model\Database\UserConfigs;

use YusamHub\JsonExt\JsonObject;

class AppTariffProperties extends JsonObject
{
    public ?int $maxAllowApplications = null;
}