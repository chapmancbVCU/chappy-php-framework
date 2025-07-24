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
        $pdo = $db->getPDO();
        try {
            $pdo->beginTransaction();

            $sql = "SELECT * FROM " . static::$_table . " 
                    WHERE queue = ? 
                    AND reserved_at IS NULL 
                    AND available_at <= ? 
                    ORDER BY id LIMIT 1 FOR UPDATE";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$queueName, date('Y-m-d H:i:s')]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $update = $pdo->prepare("UPDATE " . static::$_table . " SET reserved_at = ? WHERE id = ?");
                $update->execute([date('Y-m-d H:i:s'), $result['id']]);
                $pdo->commit();

                $job = new static();
                $job->assign($result);
                return $job;
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
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
