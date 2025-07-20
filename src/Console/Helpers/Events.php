<?php
declare(strict_types=1);
namespace Console\Helpers;


/**
 * Supports operations related to Events/Listeners.
 */
class Events {
    protected static string $eventName  = CHAPPY_BASE_PATH.DS.'app'.DS.'Events'.DS;
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

class '.$eventName.'
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
';
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
     * Creates a new event.
     *
     * @param string $eventName The name for the event.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEvent(string $eventName): int {
        Tools::pathExists(self::$providerPath);
        $fullPath = self::$providerPath.$eventName.'.php';
        return Tools::writeFile(
            $fullPath,
            self::eventTemplate($eventName),
            'Event'
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
}