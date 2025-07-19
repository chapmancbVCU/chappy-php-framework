<?php
declare(strict_types=1);
namespace Core\Lib\Events;

class EventDispatcher {
    protected array $listeners = [];

    /**
     * Listens for an event.
     *
     * @param string $eventName The event name.
     * @param callable $listener The listener.
     * @return void
     */
    public function listen(string $eventName, callable $listener): void {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatches an event
     *
     * @param object $event The event to be dispatches.
     * @return void
     */
    public function dispatch(object $event): void {
        $eventName = get_class($event);

        if(!empty($this->listeners[$eventName])) {
            foreach($this->listeners[$eventName] as $listener) {
                $listener($event);
            }
        }
    }
}