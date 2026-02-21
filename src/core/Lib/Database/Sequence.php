<?php
declare(strict_types=1);
namespace Core\Lib\Database;

/**
 * Provides support for sequencing of database field values.
 */
class Sequence {

    /**
     * The index for determining sequence.
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * Associative array containing data to be sequenced.
     *
     * @var array
     */
    protected array $sequence;

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
     * @param array<string, mixed> $data Data to be saved in new database record.
     * @param array $attributes The attributes used to override default definition 
     * values.
     * @return array An associative array of attributes that will be merged 
     * into the model's data.
     */
    public function __invoke(array $data = [], array $attributes = []) {
        $value = $this->sequence[$this->index % count($this->sequence)];
        $this->index++;
        return $value;
    }
}