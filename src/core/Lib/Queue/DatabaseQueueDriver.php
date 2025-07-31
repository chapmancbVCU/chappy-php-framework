<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use Core\Lib\Utilities\DateTime;
use Core\Models\Queue;

/**
 * Implements the QueueDriverInterface.  This driver implements functions 
 * that support database queue operations.
 */
class DatabaseQueueDriver implements QueueDriverInterface {
    /**
     * Deletes a job from the queue.
     *
     * @param mixed $jobId The unique identifier of the job to delete.
     * @return void
     */
    public function delete($jobId): void {
        $job =Queue::findById((int)$jobId);
        if($job) {
            $job->delete();
        }
    }

    /**
     * Retrieves and reserves the next available job from the specified queue.
     *
     * @param string $queue The name of the queue to pop from.
     * @return array|null The job payload as an associative array, or null if no job is available.
     */
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

    /**
     * Pushes a new job onto the specified queue.
     *
     * @param string $queue The name of the queue to push the job to.
     * @param array $payload The job payload, typically containing the class name and data.
     * @return void
     */
    public function push(string $queue, array $payload): void {
        $job = new Queue();
        $job->queue = $queue;
        $job->payload = json_encode($payload);
        $job->available_at = DateTime::timeStamps();
        $job->attempts = 0;
        $job->save();
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
        $job = new Queue();
        $job->queue = $queue;
        $job->payload = json_encode($payload);
        $job->available_at = date('Y-m-d H:i:s', time() + $delay);
        $job->attempts = 0;
        $job->save();
    }

    
}