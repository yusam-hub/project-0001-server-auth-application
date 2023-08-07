<?php

namespace App\Console\Daemons;

use App\ClientApi\ClientTelegramSdk;
use App\Console\Daemons\Jobs\TelegramJob;
use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\Interfaces\DaemonJobInterface;
use YusamHub\RedisExt\RedisExt;

class TelegramDaemon extends Daemon
{
    protected array $fetchedJobs = [];

    const REDIS_KEY_TELEGRAM_OFFSET = 'telegram_offset';

    protected function getLastOffset(): int
    {
        if (app_ext_redis_global()->redisExt()->has(self::REDIS_KEY_TELEGRAM_OFFSET)) {
            return app_ext_redis_global()->redisExt()->get(self::REDIS_KEY_TELEGRAM_OFFSET);
        }
        return 0;
    }

    protected function fetchJobs(): array
    {
        $lastOffset = $this->getLastOffset();

        $clientTelegramSdk = new ClientTelegramSdk();
        $getUpdates = $clientTelegramSdk->getUpdates($lastOffset);

        if (isset($getUpdates['result']) && is_array($getUpdates['result'])) {

            $out = [];

            if (count($getUpdates['result']) > 0) {

                foreach ($getUpdates['result'] as $update) {
                    $out[] = new TelegramJob($update);
                    $lastOffset = intval($update['update_id']);
                }

                app_ext_redis_global()->redisExt()->put(self::REDIS_KEY_TELEGRAM_OFFSET, $lastOffset + 1);
            }

            return $out;
        }

        sleep(2);
        return [];
    }

    /**
     * @return DaemonJobInterface|null
     */
    protected function getNextJob(): ?DaemonJobInterface
    {
        if (empty($this->fetchedJobs)) {
            $this->fetchedJobs = $this->fetchJobs();
        }

        $job = array_shift($this->fetchedJobs);

        if ($job instanceof DaemonJobInterface) {
            return $job;
        }

        return null;
    }
}