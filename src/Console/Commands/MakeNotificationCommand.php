<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Notifications;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates a new Notification class by running make:notification.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/notifications#writing-a-notification">here</a>.
 */
class MakeNotificationCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:notification')
            ->setDescription('Generates a new notification class')
            ->setHelp('php console make:notification <notification-name>')
            ->addArgument(
                'notification-name', 
                InputArgument::OPTIONAL, 
                'Pass the name for the new notification'
            )->addOption(
                'channels', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'Comma separated list of channel names with no spaces'
            );
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $notificationName = Str::ucfirst($this->getArgument('notification-name'));
        $message = "Enter name for new notification.";
        if($notificationName) {
            Notifications::argOptionValidate($notificationName, $message, $this->question(), ['max:50']);
        } else {
            $notificationName = Notifications::prompt($message, $this->question(), ['max:50']);
        }

        $channels = $this->getOption('channels');
        $message = "Enter comma separated list of channels.";
        $attributes = [
            'required', 
            'notReservedKeyword', 
            'list:Core\\Lib\\Notifications\\Notification:channelValues:all'
        ];
        
        if($channels) {
            Notifications::argOptionValidate($channels, $message, $this->question(), $attributes, true);
        } else {
            $channels = Notifications::prompt($message, $this->question(), $attributes, [], null, true);
        }

        $channels = Notifications::channels($channels);
        
        return Notifications::makeNotification($channels, $notificationName);
    }
}