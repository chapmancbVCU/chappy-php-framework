<?php
namespace Core\Models;
use Core\Model;

/**
 * Implements features of the Notifications class.
 */
class Notifications extends Model {
    // Set to name of database table.
    protected static $_table = 'notifications';
    
    // Fields from your database
    public $created_at;
    public $data;
    public $id;
    public $notifiable_type;
    public $notifiable_id;
    public $read_at;
    public $type;
    public $updated_at;

    /**
     * Implementation of beforeSave from base class.
     *
     * @return void
     */
    public function beforeSave(): void {
        $this->timeStamps();
    }

    public function markAsRead(): void {
        $this->read_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public static function markAsReadById(string|int $id): bool {
    $record = self::findFirst(['conditions' => 'id = ?', 'bind' => [$id]]);
        if ($record) {
            $record->read_at = date('Y-m-d H:i:s');
            return $record->save();
        }
        return false;
    }

}
