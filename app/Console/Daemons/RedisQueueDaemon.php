<?php

namespace App\Console\Daemons;

use YusamHub\Daemon\Daemon;
use YusamHub\Daemon\DaemonConsole;
use YusamHub\Daemon\Interfaces\DaemonJobInterface;

class RedisQueueDaemon extends Daemon
{
    /**
     * @var string
     */
    protected string $queue;

    /**
     * @param DaemonConsole $daemonConsole
     * @param bool $isLoop
     * @param string $queue
     */
    public function __construct(DaemonConsole $daemonConsole, bool $isLoop, string $queue)
    {
        $this->queue = $queue;
        parent::__construct($daemonConsole, $isLoop);
        $this->daemonConsole->consoleInfo(sprintf("[%s] Create with params (queue = %s)", get_class($this), $this->queue));
    }

    /**
     * @return DaemonJobInterface|null
     */
    protected function getNextJob(): ?DaemonJobInterface
    {
        app_ext_db_global()->pdoExtClose();
        app_ext_redis_global()->redisExtClose();
        $queue = app_ext_redis_global()->redisExt()->queueShift($this->queue);

        if (!empty($queue) && isset($queue['jobClass'], $queue['jobData'])) {
            $class = $queue['jobClass'];
            if (class_exists($class)) {
                return new $class($queue['jobData']);
            } else {
                app_ext_logger(LOGGING_CHANNEL_REDIS_QUEUE_DAEMON)->error("Class not exists", $queue);
            }
        }

        sleep(2);
        return null;
    }
}