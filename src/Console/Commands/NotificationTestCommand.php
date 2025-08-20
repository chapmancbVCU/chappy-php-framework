<?php
namespace Console\Commands;

use Console\Helpers\Tools;
use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Console\Helpers\Notifications;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations for testing a notification.
 */
class NotificationTestCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('notification:test')
            ->setDescription('Tests a notification through a specified channel')
            ->setHelp('php console notification:test <notification-name>')
            ->addArgument(
                'notification-name', 
                InputArgument::REQUIRED, 
                'Pass the name for the notification to test'
            )
            ->addOption(
                'user', 
                null, 
                InputOption::VALUE_NONE, 
                'User id or E-mail')
            ->addOption(
                'channels', 
                null, 
                InputOption::VALUE_REQUIRED, 
                'Comma separated list of channel names'
            )->addOption(
                'dry-run', 
                null, 
                InputOption::VALUE_NONE, 
                'Do not send, just output'
            )->addOption(
                'with', 
                null, 
                InputOption::VALUE_REQUIRED, 
                'Key:value pairs, comma-separated'
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
        $notificationName = $input->getArgument('notification-name');
        $className = Notifications::notificationClass($notificationName);

        if(!Notifications::notificationClassExists($className)) {
            Tools::info("The {$className} does not exist.", 'warning', 'yellow');
            return Command::FAILURE;
        }

        $channels = Notifications::resolveChannelsOverride($input);
        $overrides = Notifications::resolveOverridesFromWith($input);
        
        return Command::SUCCESS;
    }
}