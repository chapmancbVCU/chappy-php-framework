<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Notifications;

/**
 * Supports ability to create new notifications migration file. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/notifications#notification-migration">here</a>.
 */
class NotificationsMigrationCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('notifications:migration')
            ->setDescription('Generates a new migration for notifications table')
            ->setHelp('php console notifications:migration');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
       return Notifications::notificationsMigration();
    }
}