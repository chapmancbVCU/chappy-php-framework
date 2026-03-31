<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\ConsoleLogger;
use Console\Helpers\Notifications;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports operations for testing a notification. 
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/notifications#cli-commands">here</a>.
 */
class NotificationTestCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('notifications:test')
            ->setDescription('Tests a notification through a specified channel')
            ->setHelp('php console notification:test <notification-name>')
            ->addArgument(
                'notification-name', 
                InputArgument::REQUIRED, 
                'Pass the name for the notification to test')
            ->addOption(
                'user', 
                null, 
                InputOption::VALUE_REQUIRED, 
                'User id, email, or username')
            ->addOption(
                'channels', 
                null, 
                InputOption::VALUE_REQUIRED, 
                'Comma separated list of channel names')
            ->addOption(
                'dry-run', 
                null, 
                InputOption::VALUE_NONE, 
                'Do not send, just output')
            ->addOption(
                'with', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'Key:value pairs, comma-separated',
                false
            );
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        ConsoleLogger::setOutput($this->output);
        $notificationName = $this->input->getArgument('notification-name');
        $message = "Enter name of notification class.";
        $attributes = ['max:50', 'classExists:'.Notifications::NOTIFICATION_NAMESPACE];
        Notifications::argOptionValidate($notificationName, $message, $this->question(), $attributes);
        $className = Notifications::notificationClass($notificationName);

        $channels = Notifications::resolveChannelsOverride($this->input, $this->question());
        $overrides = Notifications::resolveOverridesFromWith($this->input, $this->question());
        $notifiable = Notifications::resolveNotifiable($this->input, $this->question());

        if($notifiable === 'dummy') {
            $notifiable = Notifications::dummy();   
        } 

        $notification = new $className($notifiable);
        $payload = Notifications::buildPayload($this->input, $overrides);

        if(Notifications::dryRun($notifiable, $notification, $payload, $channels)) {
            return self::SUCCESS;
        }

        Notifications::sendViaNotifiable($notifiable, $notification, $payload, $channels);
        return self::SUCCESS;
    }
}