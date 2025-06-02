<?php
namespace Console\Commands;
use Console\Helpers\Migrate;
use Console\Helpers\DBSeeder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Supports ability to drop all tables and recreate them.
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
        $status = Migrate::dropAllTables();
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