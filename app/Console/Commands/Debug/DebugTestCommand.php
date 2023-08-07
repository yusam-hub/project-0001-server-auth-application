<?php

namespace App\Console\Commands\Debug;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YusamHub\AppExt\SymfonyExt\Console\Commands\BaseConsoleCommand;

class DebugTestCommand extends BaseConsoleCommand
{
    protected function configure(): void
    {
        $this
            ->setName('debug:test')
            ->setDescription('debug:test:description')
            ->setHelp('debug:test:help')
        ;
    }

    /**
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::SUCCESS;
    }
}