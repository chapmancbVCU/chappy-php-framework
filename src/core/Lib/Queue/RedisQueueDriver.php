<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use Predis\Client;

class RedisQueueDriver implements QueueDriverInterface {
    protected Client $redis;

    public function __construct(Client $redis) {
        $this->redis = $redis;
    }

    public function push(string $queue, array $payload): void {
        $this->redis->lpush($queue, [json_encode($payload)]);
    }

    public function pop(string $queue): ?array {
        $result = $this->redis->brpop([$queue], 5);
        if ($result) {
            [, $payload] = $result;
            return ['id' => null, 'payload' => json_decode($payload, true)];
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
        // nothing needed for Redis
    }
}