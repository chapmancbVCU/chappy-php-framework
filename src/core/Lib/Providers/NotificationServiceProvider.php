<?php
declare(strict_types=1);
namespace Core\Lib\Providers;

use Core\Lib\Notifications\ChannelRegistry;

final class NotificationServiceProvider {
    public function register(): void {

    }

    public function boot(): void {
        $channels = config('notifications.channels', []);
        foreach($channels as $name => $class) {
            ChannelRegistry::register($name, $class);
        }
    }
}