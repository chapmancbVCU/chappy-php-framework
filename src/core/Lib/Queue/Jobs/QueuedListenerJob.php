<?php
declare(strict_types=1);
namespace Core\Lib\Queue\Jobs;

use Core\Lib\Utilities\Config;
use Core\Lib\Utilities\DateTime;
use Core\Lib\Queue\QueueableJobInterface;


/**
 * Job wrapper that executes an event listener asynchronously.
 *
 * This job carries the listener FQCN, the event FQCN, and a serialized payload
 * to rebuild the event at runtime. It also exposes queue-related preferences
 * such as delay, backoff, and max attempts.
 *
 * Typical lifecycle:
 *  - Created via {@see QueuedListenerJob::from()} when dispatching an event.
 *  - Enqueued by your queue manager with its payload from {@see toPayload()}.
 *  - Executed by a worker calling {@see handle()} which rehydrates the event
 *    and invokes the listener.
 *
 * @final
 * @implements QueueableJobInterface
 */
final class QueuedListenerJob implements QueueableJobInterface {
    /**
     * Fully-qualified class name of the listener to execute.
     *
     * @var string
     */
    private string $listenerClass = '';

    /**
     * Fully-qualified class name of the event to be rehydrated.
     *
     * @var string
     */
    private string $eventClass = '';

    /**
     * Serialized representation of the event's state.
     *
     * If the event defines a static fromPayload(array $data): self method,
     * that will be used to rebuild it; otherwise a best-effort hydration
     * using a no-arg constructor and public properties is attempted.
     *
     * @var array<string,mixed>
     */
    private array  $eventPayload = [];

    /**
     * Backoff delay(s) used between retry attempts.
     *
     * Either a single integer (seconds) or an array of increasing delays
     * (e.g., [10, 30, 60]) applied per attempt.
     *
     * @var int|array<int,int>
     */
    private int|array $backoff = 0;

    /**
     * Initial delay before the job becomes available to workers (seconds).
     *
     * @var int
     */
    private int $delay = 0;

    /**
     * Maximum number of attempts before the job is considered failed.
     *
     * @var int
     */
    private int $maxAttempts = 0;

    /**
     * Construct a job from a serialized array (as provided by the worker/driver).
     *
     * Expected keys in $data:
     *  - listener (string): listener FQCN
     *  - event (string|array): event FQCN or array with ['class' => string]
     *  - payload (array): serialized event payload
     *  - delay (int): initial delay in seconds
     *  - backoff (int|int[]): retry backoff seconds or array of per-attempt delays
     *  - max_attempts (int): max attempts before failure
     *
     * @param array<string,mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->listenerClass = $data['listener'] ?? '';
        $eventField          = $data['event'] ?? '';
        $this->eventClass    = is_string($eventField)
            ? $eventField
            : (is_array($eventField) ? (string)($eventField['class'] ?? '') : '');
        $this->eventPayload  = $data['payload']  ?? [];
        $this->backoff       = $data['backoff']  ?? 0;
        $this->maxAttempts   = $data['max_attempts'] ?? 0;
        $this->delay         = (int)($data['delay'] ?? 0);
    }

    /**
     * Factory method to build a job from a listener class and an event instance.
     *
     * If the event exposes a toPayload(): array method, it will be used to
     * serialize the event data; otherwise an empty array is stored.
     *
     * Supported $opts keys:
     *  - delay (int)            seconds before the job is available
     *  - backoff (int|int[])    retry backoff(s)
     *  - max_attempts (int)     max attempts before failure
     *
     * @param string $listenerClass Fully-qualified listener class name.
     * @param object $event         Event instance to serialize.
     * @param array<string,mixed> $opts
     *
     * @return self
     */
    public static function from(string $listenerClass, object $event, array $opts = []): self {
        $payload = method_exists($event, 'toPayload') 
            ? $event->toPayload()
            : [];

        return new self([
            'listener'      => $listenerClass,
            'event'         => $event::class,
            'payload'         => $payload,
            'delay'         => (int)($opts['delay'] ?? 0),
            'backoff '      => $opts['backoff'] ?? 0,
            'max_attempts'    => (int)($opts['max_attempts'] ?? 0),
        ]);
    }

    /**
     * Execute the job: rehydrate the event and call the listener's handle().
     *
     * @return void
     */
    public function handle(): void {
        $event = $this->rehydrateEvent();
        $listener = new ($this->listenerClass)();
        $listener->handle($event);
    }

    /**
     * Get the initial delay (in seconds) before the job becomes available.
     *
     * @return int
     */
    public function delay(): int {
        return $this->delay;
    }

    /**
     * Get the retry backoff delay(s).
     *
     * @return int|array<int,int> Either a single delay in seconds or an array of delays per attempt.
     */
    public function backoff(): int|array {
        return $this->backoff() ?: 0;
    }

    /**
     * Get the maximum number of attempts for this job.
     *
     * Falls back to the configured default (queue.max_attempts) when not set.
     *
     * @return int
     */
    public function maxAttempts(): int {
        return $this->maxAttempts ?: (int) Config::get('queue.max_attempts', 3);
    }

    /**
     * Convert this job to a driver-ready payload.
     *
     * Format:
     *  - job (string): job FQCN
     *  - data (array): job-specific data to reconstruct the job instance
     *  - available_at (int): UNIX timestamp when the job becomes available
     *  - max_attempts (int): maximum number of processing attempts
     *
     * @return array{
     *     job: class-string,
     *     data: array{
     *       listener:string,
     *       event:string,
     *       payload:array<string,mixed>,
     *       delay:int,
     *       backoff:int|array<int,int>,
     *       max_attempts:int
     *     },
     *     available_at:int,
     *     max_attempts:int
     * }
     */
    public function toPayload(): array {
        return [
            'job' => static::class,
            'data' => [
                'listener' => $this->listenerClass,
                'event' => $this->eventClass,
                'payload' => $this->eventPayload,
                'delay' => $this->delay,
                'backoff' => $this->backoff,
                'max_attempts' => $this->maxAttempts,
            ],
            'available_at' => DateTime::nowPlusSeconds($this->delay()),
            'max_attempts' => $this->maxAttempts(),
        ];
    }

    /**
     * Rebuild the event instance from the stored payload.
     *
     * If the event class defines a static fromPayload(array $data): self method,
     * it will be used. Otherwise, a no-argument constructor is invoked and
     * public properties are populated from payload keys.
     *
     * @return object The rehydrated event instance.
     */
    private function rehydrateEvent(): object {
        if (method_exists($this->eventClass, 'fromPayload')) {
            return ($this->eventClass)::fromPayload($this->eventPayload);
        }

        // fallback ONLY for events with no-arg ctor + public props
        $event = new ($this->eventClass)();
        foreach ($this->eventPayload as $k => $v) $event->$k = $v;
        return $event;
    }

    /**
     * Reconstruct a job from exported state (used by var_export()).
     *
     * @param array<string,mixed> $state
     * @return self
     */
    public static function __set_state(array $state): self {
        return new self([
            'listener'      => $state['listenerClass']   ?? '',
            'event'         => $state['eventClass']      ?? '',
            'payload'       => $state['eventPayload']    ?? [],
            'delay'         => $state['delay']           ?? 0,
            'backoff'       => $state['backoff']         ?? 0,
            'max_attempts'  => $state['maxAttempts']     ?? 0,
        ]);
    }
}