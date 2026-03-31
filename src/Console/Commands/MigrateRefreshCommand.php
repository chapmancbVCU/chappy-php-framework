<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\DBSeeder;
use Console\Helpers\Migrate;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports ability to drop tables with down function and recreate them with migrate:refresh.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#migrate-refresh">here</a>.
 */
class MigrateRefreshCommand extends ConsoleCommand
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
            return Command::SUCCESS;
        }

        $step = $this->getOption('step');
        if($step === false) {
            $status = Migrate::refresh();
        } else {
            $message = "Enter number of steps to roll back.";
            $attributes = ['required', 'noSpecialChars', 'number'];
            Migrate::argOptionValidate($step, $message, $this->question(), $attributes, true);
            $status = Migrate::refresh($step);
        }

        if($status == Command::FAILURE) {
            return $status;
        }
        
        $status = Migrate::migrate();
        if($status == Command::SUCCESS && $this->getOption('seed')) {
            return DBSeeder::seed($this->input, $this->question());
        }

        return $status;
    }
}