<?php
namespace Console\Commands;
 
use Console\Helpers\Queue;
use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes a queue worker.
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
            ->setHelp('Run php console queue:worker')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Run queue once')
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Max jobs', false)
            ->addOption('queue', null, InputOption::VALUE_REQUIRED, 'Queue name', false);
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
        $once = $input->getOption('once');
        $max = $input->getOption('max');
        $queueName = $input->getOption('queue');

        if($once && $max) {
            Tools::info('You can only set one option at a time', Logger::WARNING, Tools::BG_YELLOW);
            return Command::FAILURE;
        } 
        
        $iterations = Queue::iterations($max, $once);

        if($queueName) {
            return Queue::worker($iterations, $queueName);
        }
        return Queue::worker($iterations);

    }
}
