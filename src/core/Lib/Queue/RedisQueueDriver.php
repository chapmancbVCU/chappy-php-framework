<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use Redis;

class RedisQueueDriver {
    protected Redis $redis;

    public function __construct(Redis $redis) {
        $this->redis = $redis;
    }

    public function push(string $queue, array $payload): void {
        $this->redis->lPush($queue, json_encode($payload));
    }

    public function pop(string $queue): ?array {
        // Blocking pop with timeout
        $result = $this->redis->brPop([$queue], 5); // wait up to 5s
        if ($result) {
            [, $payload] = $result;
            return ['id'=>null, 'payload'=>json_decode($payload, true)];
        }
        return null;
    }

    public function release(string $queue, array $payload, int $delay = 0): void {
        if ($delay > 0) {
            sleep($delay);
        }
        $this->push($queue, $payload);
    }

    public function delete($jobId): void {
        // Not needed for Redis; nothing to delete after pop
    }
}