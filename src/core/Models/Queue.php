<?php
namespace Core\Models;
use Core\DB;
use Exception;
use Core\Model;
use Core\Lib\Utilities\Config;
use Core\Lib\Utilities\DateTime;

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

    /**
     * Implements beforeSave.
     *
     * @return void
     */
    public function beforeSave(): void {
        $this->timeStamps();
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
}
