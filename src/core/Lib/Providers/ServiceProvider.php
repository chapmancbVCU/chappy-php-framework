<?php
declare(strict_types=1);

namespace Core\Lib\Providers;

use Core\Lib\Events\EventDispatcher;

/**
 * Abstract class for event service providers.
 */
abstract class ServiceProvider {
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
     *
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