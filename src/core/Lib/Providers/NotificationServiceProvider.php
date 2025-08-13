<?php
declare(strict_types=1);
namespace Core\Lib\Providers;

use Core\Lib\Notifications\ChannelRegistry;

/**
 * Service provider for notifications.
 */
final class NotificationServiceProvider {
    /**
     * For future support for containers.  Bind per-channel deps here.
     *
     * @return void
     */
    public function register(): void {

    }

    /**
     * Register channels for notifications.
     *
     * @return void
     */
    public function bootNotifications(): void {
        $channels = config('notifications.channels', []);
        foreach($channels as $name => $class) {
            ChannelRegistry::register($name, $class);
        }
    }
}