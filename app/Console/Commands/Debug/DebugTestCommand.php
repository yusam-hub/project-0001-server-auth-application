<?php

namespace App\Console\Commands\Debug;

use App\Model\Database\UserConfigs\AppTariffUserConfigModel;
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
        $appTariffUserConfigModel = AppTariffUserConfigModel::configModelFindOrCreate(app_ext_db_global(), 1);
        var_dump($appTariffUserConfigModel->configValue->maxAllowApplications);
        $appTariffUserConfigModel->configValue->maxAllowApplications = 1000;
        $appTariffUserConfigModel->saveOrFail();
        var_dump($appTariffUserConfigModel->configValue->maxAllowApplications);

        return self::SUCCESS;
    }
}