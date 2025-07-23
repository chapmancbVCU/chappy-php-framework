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
        // Implement your function
    }

    public function createdAt(): void {
        if($this->isNew()) {
            $this->created_at = DateTime::timeStamps();
        }
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
