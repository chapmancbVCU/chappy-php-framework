<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use Core\Lib\Providers\EventServiceProvider as CoreEventServiceProvider;

class EventManager {
    /** 
     * @var EventDispatcher|null 
     */
    protected static ?EventDispatcher $dispatcher = null;

    public static function boot(): void {
        // Only boot once
        if(self::$dispatcher !== null) return;

        // Boot core provider
        $dispatcher = new EventDispatcher();
        $coreProvider = new CoreEventServiceProvider();
        $coreProvider->boot($dispatcher);

        // Boot app provider if available.
        if(class_exists(\App\Providers\EventServiceProvider::class)) {
            $appProvider = new \App\Providers\EventServiceProvider();
            $appProvider->boot($dispatcher);
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