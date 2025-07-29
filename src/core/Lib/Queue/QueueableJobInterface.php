<?php
declare(strict_types=1);

namespace Core\Lib\Queue;

/**
 * Interface that all queueable jobs must implement.
 */
interface QueueableJobInterface {
    /**
     * The logic that should be executed when the job is processed.
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Converts the job into a payload array for storage in the queue.
     * Must include the fully qualified class name and job data.
     *
     * @return array
     */
    public function toPayload(): array;
}