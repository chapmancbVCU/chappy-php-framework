<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Queue;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new Job class by running make:job.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/queue#job-class">here</a>.
 */
class MakeJobCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:job')
            ->setDescription('Generates a new job class')
            ->setHelp('php console make:job <job-name>')
            ->addArgument('job-name', InputArgument::OPTIONAL, 'Pass the name for the new job');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $jobName = $this->getArgument('job-name');
        $message = "Enter name for new job.";
        if($jobName) {
            Queue::argOptionValidate($jobName, $message, $this->question(), ['max:50']);
        } else {
            $jobName = Queue::prompt($message, $this->question(), ['max:50']);
        }
        return Queue::makeJob(Str::ucfirst($jobName));
    }
}