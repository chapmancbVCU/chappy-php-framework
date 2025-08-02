<?php
namespace Core\Models;
use Core\Model;
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

    public function afterDelete(): void {
        // Implement your function
    }

    public function afterSave(): void {
        // Implement your function
    }

    public function beforeDelete(): void {
        // Implement your function
    }

    public function beforeSave(): void {
        $this->timeStamps();
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
     * @throws \Exception If there is a database error during selection or update,
     *                    the transaction is rolled back and the exception is rethrown.
     */
    public static function reserveNext(string $queueName): ?self {
        $db = static::getDb();
        try {
            $db->beginTransaction();

            // find first with lock
            $job = static::findFirst([
                'conditions' => 'queue = ? AND reserved_at IS NULL AND available_at <= ?',
                'bind'       => [$queueName, date('Y-m-d H:i:s')],
                'order'      => 'id',
                'limit'      => 1,
                'lock'       => true
            ]);

            if ($job) {
                // update reserved_at using your new param-based update
                static::updateWhere(
                    ['reserved_at' => date('Y-m-d H:i:s')],
                    ['conditions' => 'id = ?', 'bind' => [$job->id]]
                );

                $db->commit();
                return $job;
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
        return null;
    }

    /**
     * Performs validation for the Queue model.
     *
     * @return void
     */
    public function validator(): void {
        // Implement your function
    }
}
