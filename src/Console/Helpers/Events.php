<?php
declare(strict_types=1);
namespace Console\Helpers;


/**
 * Supports operations related to Events/Listeners.
 */
class Events {
    protected static string $eventPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Events'.DS;
    protected static string $listenerPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Listeners'.DS;
    protected static string $providerPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Providers'.DS;

    /**
     * Template for new event.
     *
     * @param string $eventName The name for the new event class.
     * @return string The content for the new event class.
     */
    public static function eventTemplate(string $eventName): string {
        return '<?php
namespace App\Events;

use App\Models\Users;

/**
 * Document class here.
 */
class '.$eventName.'
{
    public $user;

    /**
     * Constructor
     *
     * @param User $user User associated with event.
     */
    public function __construct(Users $user)
    {
        $this->user = $user;
    }
}
';
    }

    /**
     * Template for event listener class.
     *
     * @param string $eventName The name of the event.
     * @param string $listenerName The name of the listener.
     * @return string The content for the new listener class.
     */
    public static function listenerTemplate(string $eventName, string $listenerName): string {
        return '<?php
namespace App\Listeners;

use App\Events\\'.$eventName.';

/**
 * Add description for class here
 */
class '.$listenerName.' {
    /**
     * Add description for function here
     *
     * @param '.$eventName.' $event The event.
     * @return void
     */
    public function handle('.$eventName.' $event): void {
        $user = $event->user;
    }
}';
    }

    /**
     * Template for event service provider.
     *
     * @param string $providerName The name of the event service provider.
     * @return string The content of the event service provider.
     */
    public static function eventServiceProviderTemplate(string $providerName): string {
        return '<?php
namespace App\Providers;

use Core\Lib\Events\EventDispatcher;
use Core\Lib\Providers\ServiceProvider;

/**
 * App namespace event service providers
 */
class '.$providerName.' extends ServiceProvider {
    protected array $listen = [
        // Add app-specific events here.
    ];

    /**
     * Register your events with the dispatcher.
     *
     * @param EventDispatcher $dispatcher The dispatcher.
     * @return void
     */
    public function boot(EventDispatcher $dispatcher): void {
        parent::boot($dispatcher);
    }
}';
    }

    /**
     * Creates a new event class.
     *
     * @param string $eventName The name for the event.
     * @param bool $queue If true then function creates version of file for 
     * queues.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEvent(string $eventName, bool $queue = false): int {
        Tools::pathExists(self::$eventPath);
        $fullPath = self::$eventPath.$eventName.'.php';
       
        $content = ($queue) 
            ? self::queueEventTemplate($eventName) 
            : self::eventTemplate($eventName);

        return Tools::writeFile($fullPath, $content,'Event');
    }

    /**
     * Creates a new listener class.
     *
     * @param string $eventName The name of the event.
     * @param string $listenerName The name of the listener.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeListener(string $eventName, string $listenerName): int {
        Tools::pathExists(self::$listenerPath);
        $fullPath = self::$listenerPath.$listenerName.'.php';
        return Tools::writeFile(
            $fullPath,
            self::listenerTemplate($eventName, $listenerName),
            'Listener'
        );
    }

    /**
     * Creates a new event service provider.
     *
     * @param string $providerName The name for the event service provider.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEventServiceProvider(string $providerName): int {
        Tools::pathExists(self::$providerPath);
        $fullPath = self::$providerPath.$providerName.'.php';
        return Tools::writeFile(
            $fullPath,
            self::eventServiceProviderTemplate($providerName),
            'Provider'
        );
    }

    /**
     * Returns template for event class when queue flag is set.
     *
     * @param string $eventName The name of the event.
     * @return string The contents of the event class.
     */
    public static function queueEventTemplate(string $eventName): string {
        return '<?php
namespace Core\Lib\Events;

use App\Models\Users;

/**
 * Document class here.
 */
class '.$eventName.' {
    public $user;

    /**
     * Constructor
     *
     * @param User $user User associated with event.
     */
    public function __construct(Users $user) {
        $this->user = $user;
    }

    /**
     * Adds instance variables to payload.
     *
     * @return array An associative array containing values of instance 
     * variables.
     */
    public function toPayload(): array {
        return [];
    }

    /**
     * Retrieves information from payload array and returns new instance of 
     * this class.
     *
     * @param array $data The payload array.
     * @return self New instance of this class.
     */
    public static function fromPayload(array $data): self {
        $user = Users::findById((int)$data[\'user_id\']);
        return new self($user);
    }
}';
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