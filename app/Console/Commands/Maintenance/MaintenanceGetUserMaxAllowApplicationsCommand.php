<?php

namespace App\Console\Commands\Maintenance;

use App\Model\Authorize\UserAuthorizeModel;
use App\Model\Database\UserConfigs\AppTariffUserConfigModel;
use App\Model\Database\UserModel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YusamHub\AppExt\SymfonyExt\Console\Commands\BaseConsoleCommand;

class MaintenanceGetUserMaxAllowApplicationsCommand extends BaseConsoleCommand
{
    protected function configure(): void
    {
        $this
            ->setName('maintenance:get-user-max-allow-applications')
            ->setDescription('maintenance:get-user-max-allow-applications:description')
            ->setHelp('maintenance:get-user-max-allow-applications:help')
            ->addArgument('userId', InputArgument::REQUIRED)
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

        $userModel = UserModel::findModelOrFail(app_ext_db_global(), $userId);


        $appTariffUserConfigModel = AppTariffUserConfigModel::configModelFindOrCreate(
            app_ext_db_global(),
            $userModel->id
        );

        $output->writeln(sprintf('maxAllowApplications = %s', $appTariffUserConfigModel->configValue->maxAllowApplications));

        return self::SUCCESS;
    }
}