<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use Core\Lib\Utilities\DateTime;
use Core\Models\Queue;

/**
 * Implements the QueueDriverInterface.  This driver implements functions 
 * that support queue database operations.
 */
class DatabaseQueueDriver implements QueueDriverInterface {
    public function push(string $queue, array $payload): void {
        $job = new Queue();
        $job->queue = $queue;
        $job->payload = json_encode($payload);
        $job->available_at = DateTime::timeStamps();
        $job->attempts = 0;
        $job->save();
    }

    public function pop(string $queue): ?array {
        $job = Queue::reserveNext($queue);
        if($job) {
            return [
                'id' => $job->id,
                'payload' => json_decode($job->payload, true)
            ];
        }
        return null;
    }

    public function release(string $queue, array $payload, int $delay = 0): void {
        $job = new Queue();
        $job->queue = $queue;
        $job->payload = json_encode($payload);
        $job->available_at = date('Y-m-d H:i:s', time() + $delay);
        $job->attempts = 0;
        $job->save();
    }

    public function delete($jobId): void {
        $job =Queue::findById((int)$jobId);
        if($job) {
            $job->delete();
        }
    }
}