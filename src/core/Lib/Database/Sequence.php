<?php
declare(strict_types=1);
namespace Core\Lib\Database;

/**
 * Provides support for sequencing of database field values.
 */
class Sequence {
    protected $index = 0;
    protected $sequence;

    /**
     * Constructor for the Sequence class.
     *
     * @param array ...$sequence An associative array of attributes that will 
     * be merged into the model's data.
     */
    public function __construct(array ...$sequence) {
        $this->sequence = $sequence;
    }
    
    /**
     * Uses modulo operator to determine sequence.
     *
     * @return array An associative array of attributes that will be merged 
     * into the model's data.
     */
    public function __invoke() {
        $value = $this->sequence[$this->index % count($this->sequence)];
        $this->index++;
        return $value;
    }
}