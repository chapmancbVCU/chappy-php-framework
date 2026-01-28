<?php
declare(strict_types=1);
namespace Core\Lib\Queue;

use Core\Lib\Utilities\Config;
use Predis\Client as PredisClient;

/**
 * Manages queue / job dispatch system.
 */
class QueueManager {
    /**
     * The driver that is being used.
     * @var QueueDriverInterface
     */
    protected QueueDriverInterface $driver;
    
    /**
     * Constructor for QueueManager class.
     */
    public function __construct() {
        $driver = Config::get('queue.driver');
        if ($driver === 'database') {
            $this->driver = new DatabaseQueueDriver();
        } elseif($driver === 'redis') {
            $redis = new PredisClient([
                'scheme' => 'tcp',
                'host' => Config::get('queue.redis.host'),
                'port' => Config::get('queue.redis.port'),
            ]);
            $this->driver = new RedisQueueDriver($redis);
        } else {
            throw new \Exception("Unsupported driver: " . $driver);
        }
    }

    /**
     * Delete a job from the queue by its identifier.
     *
     * This permanently removes the job record from the underlying queue
     * storage (e.g., deletes the row from the database or removes the
     * entry from Redis).
     *
     * @param mixed $jobId The unique identifier of the job to delete.
     *                     For database drivers, this is typically the row ID.
     *
     * @return void
     */
    public function delete($jobId): void {
        $this->driver->delete($jobId);
    }

    /**
     * Dispatch a new job onto the queue.
     *
     * This helper wraps push() by constructing a standard payload
     * with the job class and data. The job class should implement
     * a handle(array $data) method for the worker to call.
     *
     * @param string      $jobClass Fully-qualified class name of the job handler.
     * @param array       $data     Data payload for the job.
     * @param string|null $queue    Optional queue name (defaults to "default").
     *
     * @return void
     */
    public function dispatch(string $jobClass, array $data = [], ?string $queue = null): void {
        $queueName = $queue ?? 'default';

        $payload = [
            'job'      => $jobClass,
            'data'     => $data,
            'attempts' => 0,
            'queued_at'=> date('Y-m-d H:i:s')
        ];

        $this->push($payload, $queueName);
    }

    /**
     * Push a raw payload onto the specified queue.
     *
     * This method directly forwards the payload to the configured
     * queue driver (database or Redis) without modification.
     *
     * @param string $queue   The name of the queue to push the job onto (e.g., "default").
     * @param array  $payload An associative array representing the job data and metadata.
     *
     * @return void
     */
    public function push(array $payload, string $queue = 'default'): void {
        $this->driver->push($queue, $payload);
    }

    /**
     * Retrieve (pop) the next available job from the specified queue.
     *
     * The job is reserved by the driver (e.g., marked with a timestamp or
     * removed from the queue in Redis) so other workers cannot process it.
     *
     * @param string $queue The name of the queue to retrieve the job from.
     *
     * @return array|null An associative array of the job payload if available,
     *                    or null if the queue is empty.
     */
    public function pop(string $queue): ?array {
        return $this->driver->pop($queue);
    }

    /**
     * Release a job back onto the specified queue.
     *
     * Useful when a job has failed or needs to be retried after a delay.
     * The payload will be re‑queued and become available again after
     * the specified delay (if supported by the driver).
     *
     * @param string $queue   The name of the queue to release the job onto.
     * @param array  $payload The job payload data to re‑queue.
     * @param int    $delay   Optional delay in seconds before the job becomes available.
     *
     * @return void
     */
    public function release(string $queue, array $payload, int $delay = 0): void {
        $this->driver->release($queue, $payload, $delay);
    }
}