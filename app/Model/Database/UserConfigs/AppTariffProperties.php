<?php

namespace App\Model\Database\UserConfigs;

use YusamHub\JsonExt\JsonObject;

class AppTariffProperties extends JsonObject
{
    public ?int $maxAllowApplications = null;

    public function __construct(array $properties = [])
    {
        foreach($properties as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }
}