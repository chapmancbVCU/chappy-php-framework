<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new listener class by running make:listener.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/events#event-flag">here</a>.
 */
class MakeListenerCommand extends ConsoleCommand
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $listenerName = $this->getArgument('listener-name');
        $listenerAttributes = ['max:50', 'fieldName:listener-name'];

        // Process listener name.
        if($listenerName) {
            Events::argOptionValidate($listenerName, Events::LISTENER_PROMPT, $this->question(), $listenerAttributes);
            $isArgument = true;
        } else {
            $isArgument = false;
            $listenerName = Events::prompt(Events::LISTENER_PROMPT, $this->question(), $listenerAttributes);
        }
        $listenerNameInput = Str::ucfirst($listenerName);

        // Process event name.
        $eventName = $this->getOption('event');
        $eventAttributes = ['max:50', 'fieldName:event', "different:{$listenerNameInput}"];
        if($eventName) {
            Events::argOptionValidate($eventName, Events::EVENT_PROMPT, $this->question(), $eventAttributes);
        } else {
            $eventName = Events::prompt(Events::EVENT_PROMPT, $this->question(), $eventAttributes);
        }
        $eventName = Str::ucfirst($eventName);
        
        // Determine if listener will use queue.
        $queue = $this->getOption('queue');
        if($queue) {
            return Events::makeListener($eventName, $listenerNameInput, $queue);    
        }

        if(!$isArgument) {
            $queue = Events::queuePrompt($queue, $this->question());
        }
        return Events::makeListener($eventName, $listenerNameInput, $queue);
    }
}