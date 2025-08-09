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
}