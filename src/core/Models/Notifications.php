<?php
namespace Core\Models;
use Core\Model;

/**
 * Implements features of the Notifications class.
 */
class Notifications extends Model {

    // Fields you don't want saved on form submit
    // public const blackList = [];

    // Set to name of database table.
    protected static $_table = 'notifications';

    // Soft delete
    // protected static $_softDelete = true;
    
    // Fields from your database
    public $created_at;
    public $data;
    public $id;
    public $notifiable_type;
    public $notifiable_id;
    public $read_at;
    public $type;
    public $updated_at;

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
     * Performs validation for the Notifications model.
     *
     * @return void
     */
    public function validator(): void {
        // Implement your function
    }
}
