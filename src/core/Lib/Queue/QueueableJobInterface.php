<?php
declare(strict_types=1);

namespace Core\Lib\Queue;

/**
 * Interface that all queueable jobs must implement.
 */
interface QueueableJobInterface {
    public function backoff(): int|array;
    /**
     * Sets delay for a job.
     *
     * @return int The time for delay in seconds or Unix timestamp.
     */
    public function delay(): int;

    /**
     * The logic that should be executed when the job is processed.
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Undocumented function
     *
     * @return void
     */
    public function maxAttempts(): int;

    /**
     * Converts the job into a payload array for storage in the queue.
     * Must include the fully qualified class name and job data.
     *
     * @return array
     */
    public function toPayload(): array;
}