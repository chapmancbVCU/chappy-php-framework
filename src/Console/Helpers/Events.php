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
        return <<<PHP
<?php
namespace App\Events;

use App\Models\Users;

/**
 * Document class here.
 */
class {$eventName}
{
    public \$user;

    /**
     * Constructor
     *
     * @param User \$user User associated with event.
     */
    public function __construct(Users \$user)
    {
        \$this->user = \$user;
    }
}
PHP;
    }

    /**
     * Template for event service provider.
     *
     * @param string $providerName The name of the event service provider.
     * @return string The content of the event service provider.
     */
    public static function eventServiceProviderTemplate(string $providerName): string {
        return <<<PHP
<?php
namespace App\Providers;

use Core\Lib\Events\EventDispatcher;
use Core\Lib\Providers\ServiceProvider;

/**
 * Provider for the {$providerName} service.
 */
class {$providerName} extends ServiceProvider {
    protected array \$listen = [
        // Add app-specific events here.
    ];

    /**
     * Register your events with the dispatcher.
     *
     * @param EventDispatcher \$dispatcher The dispatcher.
     * @return void
     */
    public function boot(EventDispatcher \$dispatcher): void {
        parent::boot(\$dispatcher);
    }
}
PHP;
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
     * Handle the event.
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
     * Creates a new listener class.
     *
     * @param string $eventName The name of the event.
     * @param string $listenerName The name of the listener.
     * @param bool $queue If true then function creates version of file for 
     * queues.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeListener(string $eventName, string $listenerName, bool $queue = false): int {
        Tools::pathExists(self::$listenerPath);
        $fullPath = self::$listenerPath.$listenerName.'.php';

        $content = ($queue) 
            ? self::queueListenerTemplate($eventName, $listenerName) 
            : self::listenerTemplate($eventName, $listenerName);


        return Tools::writeFile($fullPath, $content,'Listener');
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

    public static function queueListenerTemplate(string $eventName, string $listenerName): string {
        return '<?php
namespace Core\Lib\Listeners;

use App\Events\\'.$eventName.';
use Core\Lib\Events\Contracts\ShouldQueue;
use Core\Lib\Events\Contracts\QueuePreferences;

/**
 * Add description for class here
 */
class '.$listenerName.' implements ShouldQueue, QueuePreferences {
    /**
     * Handle the event.
     *
     * @param '.$eventName.' $event The event.
     * @return void
     */
    public function handle('.$eventName.' $event) : void {
        $user = $event->user;
    }

    /**
     * Set name of queue to be used.
     *
     * @return string|null
     */
    public function viaQueue(): ?string { 
        return \'default\'; 
    }

    /**
     * Set the delay in seconds.
     *
     * @return int The delay in seconds.
     */
    public function delay(): int { 
        return 60; 
    }

    /**
     * Get backoff for job.  Can be an array of integers or a single in 
     * seconds.
     *
     * @return int|array The backoff times.
     */
    public function backoff(): int|array { 
        return [10, 30, 60];
    }

    /**
     * Gets number of maximum allowed attempts.
     *
     * @return int The maximum allowed number of attempts.
     */
    public function maxAttempts(): int { 
        return 5; 
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