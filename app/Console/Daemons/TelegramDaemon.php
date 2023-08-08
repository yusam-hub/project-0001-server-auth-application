<?php

namespace App\Console\Daemons;

use App\ClientApi\ClientTelegramSdk;
use App\Console\Daemons\TelegramJobs\TelegramIncomingCommandJob;
use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\Interfaces\DaemonJobInterface;

class TelegramDaemon extends Daemon
{
    protected array $fetchedJobs = [];

    protected function fetchJobs(): array
    {
        $clientTelegramSdk = new ClientTelegramSdk();
        $getUpdates = $clientTelegramSdk->getUpdates();

        if (isset($getUpdates['result']) && is_array($getUpdates['result'])) {

            $out = [];

            if (count($getUpdates['result']) > 0) {

                $lastOffset = 0;
                foreach ($getUpdates['result'] as $update) {
                    $out[] = new TelegramIncomingCommandJob($update);
                    $lastOffset = intval($update['update_id']);
                }

                $clientTelegramSdk->getUpdates($lastOffset+1);
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