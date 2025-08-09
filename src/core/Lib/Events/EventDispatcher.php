<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use Core\Lib\Events\Contracts\QueuePreferences;
use Core\Lib\Events\Contracts\ShouldQueue;
use Core\Lib\Queue\Jobs\QueuedListenerJob;
use Core\Lib\Queue\QueueManager;

class EventDispatcher {
    protected array $listeners = [];

    /**
     * Listens for an event.
     *
     * @param string $eventName The event name.
     * @param callable|string|array $listener The listener.
     * @return void
     */
    public function listen(string $eventName, callable|string|array $listener): void {
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

        if(empty($this->listeners[$eventName])) return;

        foreach($this->listeners[$eventName] as $listener) {
            // Case 1: "Listener\FQCN"
            if(is_string($listener)) {
                $listenerClass = $listener;
                $instance = new $listenerClass();

                if ($this->isEnqueueListener($listenerClass, $instance, $event)) {
                    continue; // was queued, skip sync execution
                }

                // Assume handle(UserEvent $e)
                $instance->handle($event);
                continue;
            }

            // Case 2: [$classOrObj, 'method']
            if(is_array($listener) && count($listener) === 2) {
                [$target, $method] = $listener;
                $listenerClass = is_object($target) ? get_class($target) : (string)$target;
                $instance = is_object($target) ? $target : new $listenerClass();

                if ($this->isEnqueueListener($listenerClass, $instance, $event)) {
                    continue; // was queued, skip sync execution
                }

                $instance->$method($event);
                continue;
            }
        }
    }

    private function enqueueListener(string $listenerClass, object $instance, object $event): void {
        $opts = [
            'delay'         => ($instance instanceof QueuePreferences) ? $instance->delay : 0,
            'backoff'       => ($instance instanceof QueuePreferences) ? $instance->backoff() : 0,
            'maxAttempts'   => ($instance instanceof QueuePreferences) ? $instance->maxAttempts() : 0
        ];

        $job = QueuedListenerJob::from($listenerClass, $event, $opts);
        $queue = new QueueManager();
        $queueName = ($instance instanceof QueuePreferences && method_exists($instance, 'viaQueue'))
            ? ($instance->viaQueue() ?? 'default')
            : 'default';
        
        $queue->push($queueName, $job->toPayload(), $job->delay());
    }

    private function isEnqueueListener(string $listenerClass, object $instance, object $event): bool {
        if($instance instanceof ShouldQueue) {
            $this->enqueueListener($listenerClass, $instance, $event);
            return true;
        }
        return false;
    }
}