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

    /**
     * Enqueue an event listener for asynchronous execution.
     *
     * Builds a job payload from the listener and event, applies queue preferences
     * (delay, backoff, max attempts) if the listener implements {@see QueuePreferences},
     * and pushes the job to the specified queue via the {@see QueueManager}.
     *
     * @param string $listenerClass Fully qualified class name of the listener.
     * @param object $instance      The instantiated listener object.
     * @param object $event         The event object being dispatched.
     *
     * @return void
     */
    private function enqueueListener(string $listenerClass, object $instance, object $event): void {
        $opts = [
            'delay'         => ($this->hasQueuePreferences($instance)) ? $instance->delay() : 0,
            'backoff'       => ($this->hasQueuePreferences($instance)) ? $instance->backoff() : 0,
            'max_attempts'   => ($this->hasQueuePreferences($instance)) ? $instance->maxAttempts() : 0
        ];

        $job = QueuedListenerJob::from($listenerClass, $event, $opts);
        $queue = new QueueManager();
        $queueName = $this->setQueueName($instance);
        $queue->push($job->toPayload(), $queueName, $job->delay());
    }

    /**
     * Determine if the listener should be enqueued and enqueue it if applicable.
     *
     * If the listener implements {@see ShouldQueue}, it will be dispatched to
     * the queue system instead of being executed immediately.
     *
     * @param string $listenerClass Fully qualified class name of the listener.
     * @param object $instance      The instantiated listener object.
     * @param object $event         The event object being dispatched.
     *
     * @return bool True if the listener was enqueued, false otherwise.
     */
    private function isEnqueueListener(string $listenerClass, object $instance, object $event): bool {
        if($instance instanceof ShouldQueue) {
            $this->enqueueListener($listenerClass, $instance, $event);
            return true;
        }
        return false;
    }

    /**
     * Check if a listener provides custom queue preferences.
     *
     * @param object $instance The instantiated listener object.
     *
     * @return bool True if the listener implements {@see QueuePreferences}, false otherwise.
     */
    private function hasQueuePreferences(object $instance) {
        return $instance instanceof QueuePreferences;
    }

    /**
     * Resolve the queue name for a queued listener or job.
     *
     * If the given instance implements {@see QueuePreferences} and defines
     * a {@see QueuePreferences::viaQueue()} method, its return value will
     * be used as the queue name. If `viaQueue()` returns null or the method
     * does not exist, the queue name defaults to "default".
     *
     * @param object $instance The listener or job instance.
     *
     * @return string The resolved queue name.
     */
    private function setQueueName(object $instance) {
        return ($this->hasQueuePreferences($instance) && method_exists($instance, 'viaQueue'))
            ? ($instance->viaQueue() ?? 'default')
            : 'default';
    }
}