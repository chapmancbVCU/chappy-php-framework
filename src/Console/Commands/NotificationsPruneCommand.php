<?php
namespace Console\Commands;
use Console\Helpers\Migrate;
use Console\Helpers\DBSeeder;
use Console\Helpers\Notifications;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to run a migration file.
 */
class NotificationsPruneCommand extends Command
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int)$input->getOption('days');
        return Notifications::prune($days);
    }
}
