<?php
namespace Console\Commands;

use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Tests a notification')
            ->setHelp('php console notification:test <notification-name>')
            ->addArgument(
                'notification-name', 
                InputArgument::REQUIRED, 
                'Pass the name for the notification to test'
            )
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Version of class for queues');
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
        return Command::SUCCESS;
    }
}