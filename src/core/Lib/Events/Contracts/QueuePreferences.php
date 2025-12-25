<?php
declare(strict_types=1);
namespace Core\Lib\Events\Contracts;

/**
 * Defines the contract for event listeners or jobs that can be queued.
 * 
 * Implementing classes can control the queue name, delay, retry behavior,
 * and maximum number of processing attempts for a queued task.
 */
interface QueuePreferences {
    /**
     * Get the backoff time(s) between retry attempts.
     *
     * @return int|array Number of seconds before retry, or an array of increasing delays 
     *                   (e.g., [10, 30, 60]) for multiple retries.
     */
    public function backoff(): int|array;     
    
    /**
     * Get the delay before the job/listener should be processed.
     *
     * @return int Delay in seconds or a UNIX timestamp representing when the job should be available.
     */
    public function delay(): int;             
    
    /**
     * Get the maximum number of attempts for processing this job/listener.
     *
     * @return int Maximum retry attempts before the job/listener is marked as failed.
     */
    public function maxAttempts(): int;       

    /**
     * Get the name of the queue to send the job/event listener to.
     *
     * @return string|null The queue name (e.g., 'mail') or null to use the default queue.
     */
    public function viaQueue(): ?string;     
}