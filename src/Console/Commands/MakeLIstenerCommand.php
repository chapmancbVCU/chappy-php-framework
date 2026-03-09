<?php
namespace Console\Commands;

use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new listener class by running make:listener.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/events#event-flag">here</a>.
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
            ->addArgument('listener-name', InputArgument::OPTIONAL, 'Pass the name for the new listener')
            ->addOption('event', null, InputOption::VALUE_OPTIONAL, 'Event name for listener', false)
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
        if($listenerName) {
            Events::argOptionValidate(
                $listenerName, 
                Events::LISTENER_PROMPT, 
                $input, 
                $output, 
                ['max:50', 'fieldName:listener-name']
            );
        }

        $eventName = Str::ucfirst($input->getOption('event'));
        if($eventName) {
            Events::argOptionValidate(
                $eventName, 
                Events::EVENT_PROMPT, 
                $input, 
                $output, 
                ['max:50', 'fieldName:event', "different:{$listenerName}"]
            );
        }
        
        $queue = $input->getOption('queue');
        if($queue) {
            return Events::makeListener($eventName, $listenerName, $queue);    
        }
        return Events::makeListener($eventName, $listenerName);
    }
}