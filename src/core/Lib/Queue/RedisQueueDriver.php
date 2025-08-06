<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use Predis\Client;
use Console\Helpers\Tools;

/**
 * Implements the QueueDriverInterface.  This driver implements functions 
 * that support Redis queue operations.
 */
class RedisQueueDriver implements QueueDriverInterface {
    protected Client $redis;

    public function __construct(Client $redis) {
        $this->redis = $redis;
    }

    /**
     * Deletes a job from the queue.  Is a no-op with Redis.
     *
     * @param mixed $jobId The unique identifier of the job to delete.
     * @return void
     */
    public function delete($jobId): void {}

    /**
     * Retrieves and reserves the next available job from the specified queue.
     *
     * @param string $queue The name of the queue to pop from.
     * @return array|null The job payload as an associative array, or null if no job is available.
     */
    public function pop(string $queue): ?array {
        $result = $this->redis->brpop([$queue], 5);
        if ($result) {
            [, $payload] = $result;
            return ['id' => null, 'payload' => json_decode($payload, true)];
        }
        return null;
    }

    /**
     * Pushes a new job onto the specified queue.
     *
     * @param string $queue The name of the queue to push the job to.
     * @param array $payload The job payload, typically containing the class name and data.
     * @return void
     */
    public function push(string $queue, array $payload): void {
        $this->redis->lpush($queue, [json_encode($payload)]);
    }

    /**
     * Releases a job back onto the queue after a failure or delay.
     *
     * @param string $queue The name of the queue to release the job to.
     * @param array $payload The job payload to requeue.
     * @param int $delay Delay in seconds before the job becomes available again.
     * @return void
     */
    public function release(string $queue, array $payload, int $delay = 0): void {
        if ($delay > 0) {
            Tools::info("Redis release with delay uses `sleep({$delay})`. This blocks the worker. Consider switching to a scheduled queue or DB driver.", 'warning', 'yellow');
            sleep($delay);
        }
        $this->push($queue, $payload);
    }
}