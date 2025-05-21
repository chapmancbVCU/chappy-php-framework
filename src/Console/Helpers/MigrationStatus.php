<?php
declare(strict_types=1);
namespace Console\Helpers;

class MigrationStatus {
    // Instance variables
    private $batch;
    private $name;
    private $status;

    /**
     * Setups the MigrationStatus object
     *
     * @param string $batch The batch value for a particular migration.
     * @param string $name The name of the migration class.
     * @param bool $isRan Default value is false.  It represents whether or 
     * not a migration has been ran.
     */
    public function __construct(string $batch, string $name, bool $isRan = false) {
        $this->batch = $batch;
        $this->name = $name;
        $this->status = ($isRan) ? 'Ran' : 'Pending';
    }

    /**
     * Getter function for batch;
     *
     * @return string $batch The value for batch.
     */
    public function getBatch(): string {
        return $this->batch;
    }

    /**
     * Getter function for migration class' name.
     *
     * @return string $name The name of the migration class.
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Getter function for status of the migration.
     *
     * @return string $status The status of the migration represented as a 
     * string.
     */
    public function getStatus(): string {
        return $this->status;
    }
}