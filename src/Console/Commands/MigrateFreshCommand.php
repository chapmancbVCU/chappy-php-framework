<?php
namespace Console\Commands;

use Console\Helpers\Migrate;
use Console\Helpers\DBSeeder;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to drop all tables and recreate them by running migrate:fresh.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#migrate-fresh">here</a>.
 */
class MigrateFreshCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:fresh')
            ->setDescription('Drops all tables and runs a Database Migration!')
            ->setHelp('Drops all tables and runs a Database Migration')
            ->addOption('seed', null, InputOption::VALUE_NONE, 'Seed flag')
            ->addOption('seeder', null, InputOption::VALUE_OPTIONAL, 'Specify the name of a seeder class');
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
        if(Tools::isProduction() && !Migrate::confirmMigrationInProduction($input, $output)) {
            console_info("Cancelling operation.");
            return Command::SUCCESS;
        }
        
        $status = Migrate::dropAllTables();
        if($status == Command::FAILURE) {
            return $status;
        }
        
        $status = Migrate::migrate();
        if($status == Command::SUCCESS && $input->getOption('seed')) {
            return DBSeeder::seed($input, $output);
        }

        return $status;
    }
}