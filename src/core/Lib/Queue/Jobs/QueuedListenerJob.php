<?php
declare(strict_types=1);
namespace Core\Lib\Queue\Jobs;

use Core\Lib\Utilities\Config;
use Core\Lib\Utilities\DateTime;
use Core\Lib\Queue\QueueableJobInterface;

final class QueuedListenerJob implements QueueableJobInterface {

    private string $listenerClass = '';
    private string $eventClass = '';
    private array  $eventPayload = [];
    private int|array $backoff = 0;
    private int $delay = 0;
    private int $maxAttempts = 0;

    // IMPORTANT: worker calls new Job($data)
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

    public function handle(): void {
        $event = $this->rehydrateEvent();
        $listener = new ($this->listenerClass)();
        $listener->handle($event);
    }

    public function delay(): int {
        return $this->delay;
    }

    public function backoff(): int|array {
        return $this->backoff() ?: 0;
    }

    public function maxAttempts(): int {
        return $this->maxAttempts ?: (int) Config::get('queue.max_attempts', 3);
    }

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

    private function rehydrateEvent(): object {
        if (method_exists($this->eventClass, 'fromPayload')) {
            return ($this->eventClass)::fromPayload($this->eventPayload);
        }

        // fallback ONLY for events with no-arg ctor + public props
        $event = new ($this->eventClass)();
        foreach ($this->eventPayload as $k => $v) $event->$k = $v;
        return $event;
    }

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