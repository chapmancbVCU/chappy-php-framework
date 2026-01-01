<?php
namespace Console\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Console\Helpers\Migrate;

/**
 * Reports status of migrations.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#status">here</a>.
 */
class MigrateStatusCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:status')
            ->setDescription('Reports status of migrations')
            ->setHelp('php console migrate:status');
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
        return Migrate::status();
    }
}