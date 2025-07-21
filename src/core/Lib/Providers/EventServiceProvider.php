<?php
declare(strict_types=1);
namespace Core\Lib\Providers;

use Core\Lib\Events\UserRegistered;
use Core\Lib\Events\EventDispatcher;
use Core\Lib\Events\AccountDeactivated;
use Core\Lib\Listeners\SendRegistrationEmail;
use Core\Lib\Listeners\SendPasswordResetEmail;
use Core\Lib\Events\UserPasswordResetRequested;
use Core\Lib\Listeners\SendAccountDeactivatedEmail;

/**
 * Internal EventServiceProvider for builtin events.
 */
class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application's built-in framework 
     * events.
     */
    protected array $listen = [
        UserPasswordResetRequested::class => [
            SendPasswordResetEmail::class,
        ],
        AccountDeactivated::class => [
            SendAccountDeactivatedEmail::class
        ],
        UserRegistered::class => [
            SendRegistrationEmail::class,
    ],
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
}