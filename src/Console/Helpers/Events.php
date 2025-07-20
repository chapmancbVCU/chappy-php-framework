<?php
declare(strict_types=1);
namespace Console\Helpers;


/**
 * Supports operations related to Events/Listeners.
 */
class Events {
    protected static string $providerPath = CHAPPY_BASE_PATH.DS.'app'.DS.'providers';

    public static function eventServiceProviderTemplate(string $providerName) {
        return '<?php
declare(strict_types=1);
namespace App\Providers;

use Core\Lib\Events\EventDispatcher;

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
        foreach($this->listen as $event => $listeners) {
            foreach($listeners as $listener) {
                $dispatcher->listen($event, [new $listener(), \'handle\']);
            }
        }
    }
}';
    }

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