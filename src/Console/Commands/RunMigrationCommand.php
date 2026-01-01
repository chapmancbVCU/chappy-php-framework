<?php
namespace Console\Commands;
use Console\Helpers\Migrate;
use Console\Helpers\DBSeeder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to run a migration file.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#overview">here</a>.
 */
class RunMigrationCommand extends Command
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
        $status = Migrate::migrate();
        if($status == Command::SUCCESS && $input->getOption('seed')) {
            return DBSeeder::seed();
        }

        return $status;
    }
}
