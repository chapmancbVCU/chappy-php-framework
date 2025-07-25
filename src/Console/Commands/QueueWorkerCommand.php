<?php
namespace Console\Commands;
 
use Console\Helpers\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes a queue worker
 */
class QueueWorkerCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('queue:worker')
            ->setDescription('Starts a new Queue worker')
            ->setHelp('Run php console queue:worker');
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
        Queue::worker();
        return Command::SUCCESS;
    }
}
