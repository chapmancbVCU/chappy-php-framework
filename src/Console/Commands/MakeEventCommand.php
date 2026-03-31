<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Events;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generates a new event class by typing make:event.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/events#creating">here</a>.
 */
class MakeEventCommand extends ConsoleCommand
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:event')
            ->setDescription('Generates a new event class')
            ->setHelp('php console make:event <event-name>')
            ->addArgument('event-name', InputArgument::OPTIONAL, 'Pass the name for the new event')
            ->addOption('queue', null, InputOption::VALUE_NONE, 'Version of class for queues');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $eventName = $this->getArgument('event-name');
        $queue = $this->getOption('queue');
        $message = "Enter name for new event.";

        if($eventName) {
            Events::argOptionValidate($eventName, $message, $this->question(), ['max:50']);
        } else {
            $eventName = Events::prompt($message, $this->question(), ['max:50']);
            $queue = Events::queueEvent($queue, $this->question());
        }
        
        if($queue) {
            return Events::makeEvent($eventName, $queue);
        }
        return Events::makeEvent($eventName);
    }
}