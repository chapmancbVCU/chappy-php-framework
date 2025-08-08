<?php
declare(strict_types=1);
namespace Core\Lib\Events;

class EventManager {
    /** 
     * @var EventDispatcher|null 
     */
    protected static ?EventDispatcher $dispatcher = null;

    public static function boot(): void {
        // Only boot once
        if(self::$dispatcher !== null) return;

        // // Boot core provider
        $dispatcher = new EventDispatcher();
        $providerClasses = require CHAPPY_BASE_PATH.DS.'config'.DS.'providers.php';

        foreach($providerClasses as $providerClass) {
            if(class_exists($providerClass)) {
                $provider = new $providerClass();
                if(method_exists($provider, 'boot')) {
                    $provider->boot($dispatcher);
                }
            }
        }
        
        self::$dispatcher = $dispatcher;
    }

    /**
     * Get the dispatcher (after boot)
     *
     * @return EventDispatcher
     */
    public static function dispatcher(): EventDispatcher
    {
        if (self::$dispatcher === null) {
            throw new \RuntimeException('EventManager not booted. Call EventManager::boot() first.');
        }

        return self::$dispatcher;
    }
}