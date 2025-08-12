<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

final class NotificationManager {
    protected static bool $booted = false;

    public static function boot(): void {
        if(self::$booted) return;

        $providerClasses = require CHAPPY_BASE_PATH . DS . 'config' . DS . 'providers.php';

        foreach($providerClasses as $providerClass) {
            if(!class_exists($providerClass)) continue;
            $provider = new $providerClass();

            if(method_exists($provider, 'bootNotifications')) {
                
            }
        }
    }
}