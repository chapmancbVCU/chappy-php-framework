<?php
declare(strict_types=1);
namespace Console\Helpers;

/**
 * Supports operations related to Events/Listeners.
 */
class Events {
    /**
     * Path for event classes.
     */
    private const EVENT_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Events'.DS;

    /**
     * Path for listener classes.
     */
    private const LISTENER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'Listeners'.DS;

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