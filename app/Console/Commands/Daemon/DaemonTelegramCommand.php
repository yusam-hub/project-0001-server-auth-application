<?php

namespace App\Console\Commands\Daemon;

use App\Console\Daemons\TelegramDaemon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YusamHub\AppExt\SymfonyExt\Console\Commands\BaseConsoleCommand;

class DaemonTelegramCommand extends BaseConsoleCommand
{
    protected function configure(): void
    {
        $this
            ->setName('daemon:telegram')
            ->setDescription('daemon:telegram:description')
            ->setHelp('daemon:telegram:help')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $daemon = new TelegramDaemon(
            new \YusamHub\Daemon\DaemonConsole(),
            true
        );
        return $daemon->run(new \YusamHub\Daemon\DaemonOptions(['rest' => 1]));
    }
}