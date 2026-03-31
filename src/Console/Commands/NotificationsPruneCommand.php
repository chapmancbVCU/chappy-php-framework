<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Notifications;
use Symfony\Component\Console\Input\InputOption;

/**
 * Supports ability to run a migration file.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/notifications#notification-prune">here</a>.
 */
class NotificationsPruneCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('notifications:prune')
            ->setDescription('Prunes old notifications from database')
            ->setHelp('Run php console notifications:prune')
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Days to retain', 90);
    }
 
    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $days = $this->getOption('days');
        $message = "Enter number of days to retain.";
        $attributes = ['required', 'noSpecialChars', 'integer'];
        Notifications::argOptionValidate($days, $message, $this->question(), $attributes, true);
        return Notifications::prune((int)$days);
    }
}
