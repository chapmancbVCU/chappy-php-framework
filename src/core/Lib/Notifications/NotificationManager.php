<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

/**
 * Manages the registration for notification channels.
 */
final class NotificationManager {
    /**
     * Determines if service is booted.
     *
     * @var bool Flag to determine if service is booted.
     */
    protected static bool $booted = false;

    /**
     * Boots notification service on application start.
     *
     * @return void
     */
    public static function boot(): void {
        if(self::$booted) return;

        $providerClasses = require CHAPPY_BASE_PATH . DS . 'config' . DS . 'providers.php';

        foreach($providerClasses as $providerClass) {
            if(!class_exists($providerClass)) continue;
            $provider = new $providerClass();

            if(method_exists($provider, 'bootNotifications')) {
                $provider->bootNotifications();
            }
        }

        self::$booted = true;
    }
}