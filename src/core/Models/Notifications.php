<?php
namespace Core\Models;
use Core\Model;

/**
 * Implements features of the Notifications class.
 */
class Notifications extends Model {
    /**
     * The name of the notifications table.
     * @var string
     */
    protected static $_table = 'notifications';
    
    // Fields from your database
    /** Created at. */
    public $created_at;

    /** Notification data. */
    public $data;

    /**
     * The id for this notification.
     * @var int
     */
    public $id;

    /**
     * Notification type.
     * @var string
     */
    public $notifiable_type;

    /**
     * Id of user to be notified.
     * @var int
     */
    public $notifiable_id;

    /**
     * Read at flag.
     * @var int
     */
    public $read_at;

    /**
     * The type of notification.
     *
     * @var string
     */
    public $type;

    /** Updated at. */
    public $updated_at;

    /**
     * Implementation of beforeSave from base class.
     *
     * @return void
     */
    public function beforeSave(): void {
        $this->timeStamps();
    }

    /**
     * Mark notification record as read.
     *
     * @return void
     */
    public function markAsRead(): void {
        $this->read_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Mark notification record as read by id.
     *
     * @param string|int $id Id of notification to mark as read.
     * @return bool True if record is saved.  Otherwise we return false.
     */
    public static function markAsReadById(string|int $id): bool {
        $record = self::findFirst(['conditions' => 'id = ?', 'bind' => [$id]]);
        if ($record) {
            $record->read_at = date('Y-m-d H:i:s');
            return $record->save();
        }
        return false;
    }

    /**
     * Permanently delete old notifications from this model's table.
     *
     * Computes a UTC cutoff timestamp for the given number of days and issues a
     * single parameterized DELETE against {@see static::$_table}. When $onlyRead
     * is true, only notifications that have been marked as read (`read_at IS NOT NULL`)
     * are pruned; otherwise all notifications older than the cutoff are removed.
     *
     * Performance note: add an index on `created_at` (and optionally `(read_at, created_at)`)
     * to keep this operation fast on large tables.
     *
     * @param int  $days      Number of days to retain. Rows with `created_at` earlier than
     *                        now minus this many days are deleted. Must be >= 1.
     * @param bool $onlyRead  When true, restricts pruning to rows where `read_at` is not null.
     *                        Defaults to false (delete both read and unread).
     *
     * @return int Number of rows deleted.
     *
     * @throws \InvalidArgumentException If $days is less than 1.
     */
    public static function notificationsToPrune(int $days, bool $onlyRead = false): int {
        if ($days < 1) {
            throw new \InvalidArgumentException('Days must be >= 1');
        }

        // UTC + stable format
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify("-{$days} days")
            ->format('Y-m-d H:i:s');

        $db = static::getDb(); // or DB::getInstance()

        $where = $onlyRead
            ? 'read_at IS NOT NULL AND created_at < ?'
            : 'created_at < ?';

        $db->query("DELETE FROM " . static::$_table . " WHERE {$where}", [$cutoff]);

        return $db->count(); // number of rows deleted
    }
}
