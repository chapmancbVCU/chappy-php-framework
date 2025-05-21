<?php
namespace Console\Helpers;

class MigrationStatus {
    // Instance variables
    private $batch;
    private $name;
    private $status;

    public function __construct(string $batch, string $name, string $status = '') {
        $this->batch = $batch;
        $this->name = $name;
        $this->status = $status;
    }
}