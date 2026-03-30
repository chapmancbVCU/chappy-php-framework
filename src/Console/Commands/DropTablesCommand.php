<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Migrate;

/**
 * Supports ability to drop all tables by using the migrate:drop:all command.  
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/database_operations#drop-all">here</a>.
 */
class DropTablesCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:drop:all')
            ->setDescription('Drops all database tables')
            ->setHelp('Drops all database tables');
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $confirm = Migrate::confirmDropAllTables($this->question());
        if($confirm) return Migrate::dropAllTables();
        return self::SUCCESS;
    }
}
