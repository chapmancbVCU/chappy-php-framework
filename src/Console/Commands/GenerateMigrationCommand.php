<?php
namespace Console\Commands;
 
use Console\Helpers\Tools;
use Console\Helpers\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new migration file.
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
            ->setHelp('Generates a new Database Migration')
            ->addArgument('table_name', InputArgument::REQUIRED, 'Pass the table\'s name.')
            ->addOption('update', null, InputOption::VALUE_NONE, 'Update flag')
            ->addOption('rename', null, InputOption::VALUE_REQUIRED, 'to', false);
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
        if($input->getOption('update') && !$input->getOption('rename')) {
            return Migrate::makeUpdateMigration($input);
        } else if($input->getOption('rename') && !$input->getOption('update')) {
            return Migrate::makeRenameMigration($input);
        } else if(!$input->getOption('rename') && !$input->getOption('update')){
            return Migrate::makeMigration($input);
        } else {
            Tools::info('Cannot accept update and rename options at the same time.', 'error', 'red');
            return Command::FAILURE;
        }
    }
}
