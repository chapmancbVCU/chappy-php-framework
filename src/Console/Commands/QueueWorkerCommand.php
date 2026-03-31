<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Executes a queue worker.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/queue#worker">here</a>.
 */
class QueueWorkerCommand extends ConsoleCommand
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $once = $this->getOption('once');

        $max = $this->getOption('max');
        if($max || $max === '') {
            $message = "Enter value for max jobs.";
            Queue::argOptionValidate($max, $message, $this->question(), ['integer', 'required'], true);
        }

        $queueName = $this->getOption('queue');
        if($queueName || $queueName === '') {
            $message = "Enter name for the queue you want to use.";
            Queue::argOptionValidate($queueName, $message, $this->question(), ['max:50', 'queue'], true);
        }

        if($once && $max) {
            console_warning('You can only set one option at a time');
            return Command::FAILURE;
        } 
        
        $iterations = Queue::iterations($max, $once);

        if($queueName) {
            return Queue::worker($iterations, $queueName);
        }
        return Queue::worker($iterations);

    }
}
