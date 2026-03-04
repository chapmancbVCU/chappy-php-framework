<?php
declare(strict_types=1);

namespace Console;

use Console\HasValidators;

/**
 * Class that can be extended by helpers when validators needs to be used.
 */
class Console {
    use HasValidators;

    /**
     * Creates new instance of Console class.
     *
     * @param string $fieldName The name of the field to be validated.
     */
    public function __construct(string $fieldName = "") {
        $this->fieldName($fieldName);
    }

    /**
     * Returns instance of this or child helper class.
     *
     * @param string $fieldName The name of the field to be validated.
     * @return static
     */
    public static function getInstance(string $fieldName = ""): static {
        return new static($fieldName);
    }
}