<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Migrate;
use Console\Helpers\DBSeeder;
use Console\Helpers\Tools;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports ability to run a migration file.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#overview">here</a>.
 */
class RunMigrationCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate')
            ->setDescription('Runs a Database Migration!')
            ->setHelp('Runs a Database Migration')
            ->addOption('seed', null, InputOption::VALUE_NONE, 'Seed flag')
            ->addOption('seeder', null, InputOption::VALUE_OPTIONAL, 'Specify name of a seeder class', false);
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        if(Tools::isProduction() && !Migrate::confirmMigrationInProduction($this->question())) {
            console_info("Cancelling operation.");
            return self::SUCCESS;
        }

        $status = Migrate::migrate();
        if($status == self::SUCCESS && $this->getOption('seed')) {
            return DBSeeder::seed($this->input, $this->question());
        }

        return $status;
    }
}
