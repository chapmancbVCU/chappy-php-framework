<?php
namespace Console\Commands;

use Console\Helpers\Queue;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new Job class by running make:job.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/queue#job-class">here</a>.
 */
class MakeJobCommand extends Command
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
            ->addArgument('job-name', InputArgument::REQUIRED, 'Pass the name for the new job');
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
        $jobName = Str::ucfirst($input->getArgument('job-name'));
        return Queue::makeJob($jobName);
    }
}