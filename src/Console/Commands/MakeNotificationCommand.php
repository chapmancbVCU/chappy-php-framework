<?php
namespace Console\Commands;

use Console\Helpers\Notifications;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new Notification class by running make:notification.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/notifications#writing-a-notification">here</a>.
 */
class MakeNotificationCommand extends Command
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notificationName = Str::ucfirst($input->getArgument('notification-name'));
        $message = "Enter name for new notification.";
        if($notificationName) {
            Notifications::argOptionValidate($notificationName, $message, $input, $output, ['max:50']);
        } else {
            $notificationName = Notifications::prompt($message, $input, $output, ['max:50']);
        }

        $channels = $input->getOption('channels');
        $message = "Enter comma separated list of channels.";
        $attributes = ['required', 'notReservedKeyword', 'channelOptions'];
        if($channels) {
            Notifications::argOptionValidate($channels, $message, $input, $output, $attributes, true);
        } else {
            $channels = Notifications::prompt($message, $input, $output, $attributes, [], null, true);
        }

        $channels = Notifications::channels($channels);
        
        return Notifications::makeNotification($channels, $notificationName);
    }
}