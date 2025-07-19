<?php
declare(strict_types=1);

namespace Core\Lib\Providers;

/**
 * Abstract class for event service providers.
 */
abstract class ServiceProvider {

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
    public function boot(): void {

    }
}