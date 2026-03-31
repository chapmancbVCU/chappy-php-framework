<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Migrate;

/**
 * Reports status of migrations.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#status">here</a>.
 */
class MigrateStatusCommand extends ConsoleCommand
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        return Migrate::status();
    }
}