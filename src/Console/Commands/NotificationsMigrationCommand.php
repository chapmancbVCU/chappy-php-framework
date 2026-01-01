<?php
namespace Console\Commands;

use Console\Helpers\Notifications;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to create new notifications migration file. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/notifications#notification-migration">here</a>.
 */
class NotificationsMigrationCommand extends Command
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
       return Notifications::notificationsMigration();
    }
}