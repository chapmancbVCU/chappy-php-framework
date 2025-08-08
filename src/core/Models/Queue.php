<?php
namespace Core\Models;
use Core\DB;
use Exception;
use Core\Model;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Config;
use Core\Lib\Utilities\DateTime;
use Core\Lib\Queue\QueueableJobInterface;

/**
 * Implements features of the Queue class.
 */
class Queue extends Model {
    // Set to name of database table.
    protected static $_table = 'queue';

    // Fields from your database
    public $attempts;
    public $available_at;
    public $created_at;
    public $id;
    public $queue;
    public $reserved_at;
    public $payload;

    private static function calcRetryDelay(self $job, ?array $payload): int {
        $jobClass = $payload['job'] ?? null;

        if(self::isQueueableClass($jobClass)) {
            $jobData = $payload['data'] ?? [];
            $jobInstance = new $jobClass($jobData);
            $backoff = $jobInstance->backoff();
            return self::resolveBackoffDelay($backoff, $job);
            
        }
        return 10;
    }

    /**
     * Implements beforeSave.
     *
     * @return void
     */
    public function beforeSave(): void {
        $this->timeStamps();
    }

    /**
     * Handles tasks related to exceptions and retry of jobs.
     *
     * @param Exception $e The exception.
     * @param array $job The array of jobs
     * @return void
     */
    public static function exceptionMessaging(Exception $e, array $queueJob): void {
        Tools::info("Job failed: " . $e->getMessage(), 'warning');
        $payload = $queueJob['payload'] ?? [];
        $job = self::findJob($queueJob);

        if($job) {
            $job->exception = $e->getMessage() . "\n" . $e->getTraceAsString();
            if($job->attempts >= self::maxAttempts($payload)) {
                $job->failed_at = self::failedAt();
            } else {
                self::updateAttempts($job);
                $delay = self::calcRetryDelay($job, $payload);
                $job->available_at = self::setAvailableAt($delay, $job);
            }

            $job->save();
        }
    }

    private static function failedAt() {
        Tools::info('Job permanently failed and marked as failed.', 'warning');
        return DateTime::timeStamps();
    }
    
    /**
     * Find first with lock
     *
     * @param string $queueName
     * @return self|null
     */
    private static function findFirstWithLock(string $queueName): ?self {
        return static::findFirst([
            'conditions' => 'queue = ? AND reserved_at IS NULL AND failed_at IS NULL AND available_at <= ?',
            'bind'       => [$queueName, date('Y-m-d H:i:s')],
            'order'      => 'id',
            'limit'      => 1,
            'lock'       => true
        ]);
    }

    private static function findJob(array $queueJob): ?self {
        return Arr::exists($queueJob, 'id') 
            ? self::findById($queueJob['id']) 
            : null;
    }

    /**
     * Test if job as exceeded limit for maximum allowed attempts.
     *
     * @param DB $db Instance of DB class.
     * @param self $job Queue model.
     * @return bool True if exceeded maximum allowed attempts, otherwise false.
     */
    private static function hasExceededMaxAttempts(DB $db, self $job): bool {
        $payload = json_decode($job->payload, true);
        $maxAttempts = $payload['max_attempts'] ?? Config::get('queue.max_attempts', 3);

        if($job->attempts >= $maxAttempts) {
            static::updateWhere(
                ['failed_at' => DateTime::timeStamps()],
                ['conditions'=> 'id = ?', 'bind' => [$job->id]]
            );
            $db->commit();
            return true;
        }
        return false;
    }

    private static function isQueueableClass(mixed $jobClass): bool {
        return $jobClass && class_exists($jobClass) && is_subclass_of($jobClass, QueueableJobInterface::class);
    }

    private static function maxAttempts(array $payload): int {
        return $payload['max_attempts'] ?? Config::get('queue.max_attempts', 3);
    }
    
    /**
     * Updates reserved_at field.
     *
     * @param DB $db Instance of DB class.
     * @param self $job Queue model.
     * @return self This instance of Queue model passed as parameter.
     */
    private static function reservedAt(DB $db, self $job): self {
        static::updateWhere(
            ['reserved_at' => DateTime::timeStamps()],
            ['conditions' => 'id = ?', 'bind' => [$job->id]]
        );

        $db->commit();
        return $job;
    }
    
    /**
     * Attempts to atomically reserve the next available job from the queue.
     *
     * This method wraps the operation in a database transaction and uses
     * a SELECT ... FOR UPDATE query (when supported) to safely fetch the
     * next unreserved job in the specified queue. Once a job is found,
     * it immediately updates the `reserved_at` timestamp to mark it as
     * reserved and prevent other workers from picking it up.
     *
     * Usage example:
     * ```php
     * $job = Queue::reserveNext('default');
     * if ($job) {
     *     // Process the job...
     * }
     * ```
     *
     * @param string $queueName The name of the queue to pull from (e.g. "default").
     *
     * @return static|null Returns an instance of the job model with the job data
     *                     if a job was reserved, or `null` if no available job
     *                     was found at this time.
     *
     * @throws Exception If there is a database error during selection or update,
     *                    the transaction is rolled back and the exception is rethrown.
     */
    public static function reserveNext(string $queueName): ?self {
        $db = static::getDb();

        try {
            $db->beginTransaction();
            $job = self::findFirstWithLock($queueName);
            
            if($job) {
                if(self::hasExceededMaxAttempts($db, $job)) {
                    return null;
                }
                return self::reservedAt($db, $job);
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        return null;
    }

    private static function resolveBackoffDelay(mixed $backoff, self $job): int {
        if(is_array($backoff)) {
            $delay = $backoff[$job->attempts - 1] ?? end($backoff);
        } else if (is_int($backoff)) {
            $delay = $backoff;
        }
        return $delay;
    }

    private static function setAvailableAt(int $delay, self $job) {
        Tools::info("Job will be retried. Attempt: {$job->attempts}", 'warning');
        return DateTime::nowPlusSeconds($delay);
    }

    /**
     * Updates information about attempts.
     *
     * @param QueueModel $job The job whose attempts we want to update.
     * @return void
     */
    private static function updateAttempts(self $job): void {
        $job->attempts += 1;
        $decoded = json_decode($job->payload, true);
        $decoded['attempts'] = $job->attempts;
        $job->payload = json_encode($decoded);
    }
}
