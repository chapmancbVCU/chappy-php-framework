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
     * Returns instance of this or child helper class.
     *
     * @return static
     */
    public static function getInstance(): static {
        return new static();
    }
}