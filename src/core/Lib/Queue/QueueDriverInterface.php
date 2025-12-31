<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

/**
 * Interface QueueDriverInterface
 *
 * Defines the contract for a queue driver implementation.
 * A queue driver is responsible for pushing, popping, releasing,
 * and deleting jobs from a queue backend (e.g., database, Redis).
 */
interface QueueDriverInterface {
    /**
     * Deletes a job from the queue.
     *
     * @param mixed $jobId The unique identifier of the job to delete.
     * @return void
     */
    public function delete($jobId): void;

    /**
     * Retrieves and reserves the next available job from the specified queue.
     *
     * @param string $queue The name of the queue to pop from.
     * @return array|null The job payload as an associative array, or null if no job is available.
     */
    public function pop(string $queue): ?array;

    /**
     * Pushes a new job onto the specified queue.
     *
     * @param string $queue The name of the queue to push the job to.
     * @param array $payload The job payload, typically containing the class name and data.
     * @return void
     */
    public function push(string $queue, array $payload): void;

    /**
     * Releases a job back onto the queue after a failure or delay.
     *
     * @param string $queue The name of the queue to release the job to.
     * @param array $payload The job payload to requeue.
     * @param int $delay Delay in seconds before the job becomes available again.
     * @return void
     */
    public function release(string $queue, array $payload, int $delay = 0): void;
}