<?php
namespace Console\Commands;
 
use Console\Helpers\Tools;
use Console\Helpers\Migrate;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new migration file.  
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#creating-a-new-table">here</a>.
 */
class GenerateMigrationCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:migration')
            ->setDescription('Generates a Database Migration!')
            ->setHelp('make:migration <table_name>, --rename flag to rename table or --update flag for update table migration')
            ->addArgument('table_name', InputArgument::REQUIRED, 'Pass the table\'s name.')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Update table')
            ->addOption('rename', null, InputOption::VALUE_REQUIRED, 'Rename table', false);
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
        if($input->getOption('update') && $input->getOption('rename')) {
            console_warning('Cannot accept update and rename options at the same time.');
            return Command::FAILURE;
        }

        if($input->getOption('rename')) return Migrate::makeRenameMigration($input);
        else if($input->getOption('update')) return Migrate::makeUpdateMigration($input);
        else return Migrate::makeMigration($input);
    }
}
