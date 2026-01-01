<?php
namespace Console\Commands;

use Console\Helpers\DBSeeder;
use Console\Helpers\Tools;
use Console\Helpers\Migrate;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to drop tables with down function and recreate them with migrate:refresh.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#migrate-refresh">here</a>.
 */
class MigrateRefreshCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:refresh')
            ->setDescription('Drops all tables with down function and runs a Database Migration!')
            ->setHelp('migrate:refresh with --seed to seed db and --step=<steps> as the number of steps to roll back.')
            ->addOption(
                'step',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of steps to roll back',
                false
            )
            ->addOption('seed', null, InputOption::VALUE_NONE, 'Seed flag');
    }
 
    /**
     * Executes the command
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $step = $input->getOption('step');
        if($step === false) {
            $status = Migrate::refresh();
        } else {
            if($step === '') {
                Tools::info('Please enter number of migrations to roll back', Logger::ERROR, Tools::BG_RED);
                return Command::FAILURE;
            }
            $status = Migrate::refresh($step);
        }

        if($status == Command::FAILURE) {
            return $status;
        }
        
        $status = Migrate::migrate();
        if($status == Command::SUCCESS && $input->getOption('seed')) {
            return DBSeeder::seed();
        }

        return $status;
    }
}