<?php

namespace Core\Lib\Queue;

interface QueueDriverInterface {
    public function delete($jobId): void;
    public function pop(string $queue): ?array;
    public function push(string $queue, array $payload): void;
    public function release(string $queue, array $payload, int $delay = 0): void;
}