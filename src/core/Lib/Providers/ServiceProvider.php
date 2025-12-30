<?php
declare(strict_types=1);

namespace Core\Lib\Providers;

use Core\Lib\Events\EventDispatcher;

/**
 * Abstract class for event service providers.
 */
abstract class ServiceProvider {
    /**
     * The event listener mappings for the application's built-in framework 
     * events.
     * @var array
     */
    protected array $listen = [];

    /**
     * Default function to register bindings or event listeners.  Can 
     * be overridden.
     *
     * @return void
     */
    public function register(): void {

    }

    /**
     * Boot any services after registration.  Can be overridden.
     * @param EventDispatcher $dispatcher The dispatcher.
     * @return void
     */
    public function boot(EventDispatcher $dispatcher): void {
        foreach($this->listen as $event => $listeners) {
            foreach($listeners as $listener) {
                $dispatcher->listen($event, [new $listener(), 'handle']);
            }
        }
    }
}