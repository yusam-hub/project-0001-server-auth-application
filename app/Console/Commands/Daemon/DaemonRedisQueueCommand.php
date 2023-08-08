<?php

namespace App\Console\Commands\Daemon;

use App\Console\Daemons\RedisQueueDaemon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YusamHub\AppExt\SymfonyExt\Console\Commands\BaseConsoleCommand;

class DaemonRedisQueueCommand extends BaseConsoleCommand
{
    protected function configure(): void
    {
        $this
            ->setName('daemon:redis-queue')
            ->setDescription('daemon:redis-queue:description')
            ->setHelp('daemon:redis-queue:help')
            ->addOption('queue', null, InputOption::VALUE_REQUIRED, 'Name of queue, default=default','default')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $daemon = new RedisQueueDaemon(
            new \YusamHub\Daemon\DaemonConsole(),
            true,
            $input->getOption('queue'),
        );
        return $daemon->run(new \YusamHub\Daemon\DaemonOptions(['rest' => 1]));
    }
}
