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
 * Generates a new Notification class.
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
                InputArgument::REQUIRED, 
                'Pass the name for the new notification'
            )->addOption(
                'channel', 
                null, 
                InputOption::VALUE_REQUIRED, 
                'Comma separated list of channel names'
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
        $channels = Notifications::channelOptions($input);
        
        return Command::SUCCESS;
    }
}