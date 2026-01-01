<?php
declare(strict_types=1);
namespace Core\Traits;
use Core\Lib\Utilities\DateTime;

/**
 * Creates the $created_at and $updated_at fields and performs the operation 
 * that updates timestamps.
 */
trait HasTimestamps {
    /**
     * Time record was created.
     * @var [type]
     */
    public $created_at;

    /**
     * Time record was updated.
     * @var string
     */
    public $updated_at;

    /**
     * Sets values for timestamp fields.
     *
     * @return void
     */
    public function timeStamps(): void {
        $now = DateTime::timeStamps();
        $this->updated_at = $now;
        if($this->isNew()) {
            $this->created_at = $now;
        }
    }
}