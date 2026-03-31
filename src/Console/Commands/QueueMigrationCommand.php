<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Queue;

/**
 * Supports ability to create new notifications migration file. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/queue#migration">here</a>.
 */
class QueueMigrationCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('queue:migration')
            ->setDescription('Generates a new migration for queue table')
            ->setHelp('php console notifications:migration');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
       return Queue::queueMigration();
    }
}