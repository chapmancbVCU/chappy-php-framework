<?php
declare(strict_types=1);
namespace Console\Helpers;


/**
 * Supports operations related to Events/Listeners.
 */
class Events {
    protected static string $providerPath = CHAPPY_BASE_PATH.DS.'app'.DS.'Providers'.DS;

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
     * Creates a new event service provider
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