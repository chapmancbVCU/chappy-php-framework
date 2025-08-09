<?php
declare(strict_types=1);
namespace Core\Lib\Queue\Jobs;

use Core\Lib\Queue\QueueableJobInterface;
use Core\Lib\Utilities\Config;

final class QueuedListenerJob implements QueueableJobInterface {
    public function __construct(
        private string $listenerClass,
        private string $eventClass,
        private array $eventPayload,
        private int $delay = 0,
        private int|array $backoff = 0,
        private int $maxAttempts = 0
    ) {}

    public static function from(string $listenerClass, object $event, array $opts = []): self {

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
                'maxAttempts' => $this->maxAttempts,
            ],
        ];
    }

    private function rehydrateEvent(): object {
        $event = new ($this->eventClass)();
        foreach($this->eventPayload as $k => $v) { $event->$k = $v; }
        return $event;
    }

    public static function __set_state(array $state): self {
        return new self(
            $state['listenerClass'],
            $state['eventClass'],
            $state['eventPayload'],
            $state['delay'] ?? 0,
            $state['backoff'] ?? 0,
            $state['max_attempts' ?? 0]
        );
    }
}