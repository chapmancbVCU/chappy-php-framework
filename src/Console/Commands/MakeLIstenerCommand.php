<?php
namespace Console\Commands;

use Console\Helpers\Tools;
use Console\Helpers\Events;
use Core\Lib\Logging\Logger;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new event class.
 */
class MakeListenerCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:listener')
            ->setDescription('Generates a new listener class')
            ->setHelp('php console make:listener <listener-name>')
            ->addArgument('listener-name', InputArgument::REQUIRED, 'Pass the name for the new listener')
            ->addOption('event', null, InputOption::VALUE_REQUIRED, 'Event name for listener', false)
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
        $listenerName = Str::ucfirst($input->getArgument('listener-name'));
        $eventName = Str::ucfirst($input->getOption('event'));

        if(!$eventName) {
            Tools::info('Please provide name of the event', Logger::WARNING, Tools::BG_YELLOW);
            return Command::FAILURE;
        }

        if(!Events::verifyListenerParams($eventName, $listenerName)) {
            Tools::info(
                'Either event option was not provided or both event and listener names are the same', 
                Logger::WARNING, 
                Tools::BG_YELLOW
            );
            return Command::FAILURE;
        }
        
        $queue = $input->getOption('queue');
        if($queue) {
            return Events::makeListener($eventName, $listenerName, $queue);    
        }
        return Events::makeListener($eventName, $listenerName);
    }
}