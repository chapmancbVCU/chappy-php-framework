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
        self::createdAt();
    }

    public function createdAt(): void {
        if($this->isNew()) {
            $this->created_at = DateTime::timeStamps();
        }
    }

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
