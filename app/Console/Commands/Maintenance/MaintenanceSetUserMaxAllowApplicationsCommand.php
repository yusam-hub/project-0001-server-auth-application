<?php

namespace App\Console\Commands\Maintenance;

use App\Model\Database\UserConfigs\AppTariffUserConfigModel;
use App\Model\Database\UserModel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YusamHub\AppExt\SymfonyExt\Console\Commands\BaseConsoleCommand;

class MaintenanceSetUserMaxAllowApplicationsCommand extends BaseConsoleCommand
{
    protected function configure(): void
    {
        $this
            ->setName('maintenance:set-user-max-allow-applications')
            ->setDescription('maintenance:set-user-max-allow-applications:description')
            ->setHelp('maintenance:set-user-max-allow-applications:help')
            ->addArgument('userId', InputArgument::REQUIRED)
            ->addArgument('maxAllowApplications', InputArgument::REQUIRED)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = intval($input->getArgument('userId'));
        $maxAllowApplications = intval($input->getArgument('maxAllowApplications'));

        $userModel = UserModel::findModelOrFail(app_ext_db_global(), $userId);


        $appTariffUserConfigModel = AppTariffUserConfigModel::configModelFindOrCreate(
            app_ext_db_global(),
            $userModel->id
        );
        $appTariffUserConfigModel->configValue->maxAllowApplications = $maxAllowApplications;
        $appTariffUserConfigModel->saveOrFail();

        $output->writeln(sprintf('maxAllowApplications = %s', $appTariffUserConfigModel->configValue->maxAllowApplications));

        return self::SUCCESS;
    }
}