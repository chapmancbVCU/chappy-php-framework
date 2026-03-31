<?php
declare(strict_types=1);
namespace Console\Helpers;

use Console\Console;
use Console\FrameworkQuestion;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports operations related to Events/Listeners.
 */
class Events extends Console {
    /**
     * Path for event classes.
     */
    private const EVENT_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Events'.DS;

    /**
     * The message to present to user when name of event listener is being asked.
     */
    public const EVENT_PROMPT = "Enter name for event associated with listener.";

    /**
     * Path for listener classes.
     */
    private const LISTENER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Listeners'.DS;

    /**
     * The message to present to user when name of event listener is being asked.
     */
    public const LISTENER_PROMPT = "Enter name for new listener.";

    /**
     * Path for provider classes.
     */
    private const PROVIDER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Providers'.DS;

    /**
     * Creates a new event class.
     *
     * @param string $eventName The name for the event.
     * @param bool $queue If true then function creates version of file for 
     * queues.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEvent(string $eventName, bool $queue = false): int {
        Tools::pathExists(self::EVENT_PATH);
        $eventName = Str::ucfirst(($eventName));
        $fullPath = self::EVENT_PATH.$eventName.'.php';
       
        $content = ($queue) 
            ? EventStubs::queueEventTemplate($eventName) 
            : EventStubs::eventTemplate($eventName);

        return Tools::writeFile($fullPath, $content,'Event');
    }

    /**
     * Creates a new event service provider.
     *
     * @param string $providerName The name for the event service provider.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEventServiceProvider(string $providerName): int {
        Tools::pathExists(self::PROVIDER_PATH);
        $providerName = Str::ucfirst($providerName);
        $fullPath = self::PROVIDER_PATH.$providerName.'.php';
        return Tools::writeFile(
            $fullPath,
            EventStubs::eventServiceProviderTemplate($providerName),
            'Provider'
        );
    }

    /**
     * Creates a new listener class.
     *
     * @param string $eventName The name of the event.
     * @param string $listenerName The name of the listener.
     * @param bool $queue If true then function creates version of file for 
     * queues.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeListener(string $eventName, string $listenerName, bool $queue = false): int {
        Tools::pathExists(self::LISTENER_PATH);
        $fullPath = self::LISTENER_PATH.$listenerName.'.php';

        $content = ($queue) 
            ? EventStubs::queueListenerTemplate($eventName, $listenerName) 
            : EventStubs::listenerTemplate($eventName, $listenerName);


        return Tools::writeFile($fullPath, $content,'Listener');
    }

    /**
     * Called when event-name is not provided.  It exits early if --queue flag 
     * is provided.  Otherwise, the user is asked if they want to create a 
     * queued event class.
     *
     * @param mixed $queue The queue option.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return bool If flag is provided then it is returned.  If a response 
     * is provided the string 'queue' is returned.  Otherwise, we return null.
     */
    public static function queueEvent(mixed $queue, FrameworkQuestion $question): bool {
        if($queue) return $queue;
        $message = "Do you want to create a queued event class? (y/n)";
        if(self::confirm($message, $question)) {
            return true;
        }

        return false;
    }
    
    /**
     * Returns value of $queue if option is set.  Otherwise we ask user if they event listener 
     * will be associated with a queue.
     *
     * @param mixed $queue Value of --queue option.
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     * @return mixed The user response
     */
    public static function queuePrompt(mixed $queue, InputInterface $input, OutputInterface $output): mixed {
        if($queue) return $queue;
        $message = "Do you want to create a version of the listener class to support queues? (y/n)";
        return self::confirm($message, $input, $output);
    }

    /**
     * Checks if $eventName and $listerName was provided and that they are not the same.
     *
     * @param string $eventName The name of the event.
     * @param string $listenerName The name of the listener.
     * @return bool If True then both params were provided and not the same.  
     * Otherwise, we return false.
     */
    public static function verifyListenerParams(string $eventName, string $listenerName): bool {
        return $eventName && $listenerName && ($eventName != $listenerName);
    }
}