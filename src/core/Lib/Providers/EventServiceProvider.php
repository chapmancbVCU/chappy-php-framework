<?php
declare(strict_types=1);
namespace Core\Lib\Providers;

use Core\Lib\Events\EventDispatcher;
use Core\Lib\Events\UserPasswordResetRequested;
use Core\Lib\listeners\SendPasswordResetEmail;

/**
 * Internal EventServiceProvider for builtin events.
 */
class EventServiceProvider extends ServiceProvider {

}