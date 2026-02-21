<?php
declare(strict_types=1);
namespace Core\Exceptions\FactorySeeder;
use Core\Exceptions\FrameworkRuntimeException;

/**
 * Handles exceptions related to Logger severity levels.
 */
final class FactorySeederException extends FrameworkRuntimeException {
    /**
     * Create a new FactorySeederException.
     *
     * @param string $message Optional human-readable details.
     * @param \Throwable|null $previous Optional underlying cause for exception chaining.
     */
    public function __construct(
        string $message = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            0,
            $previous
        );
        error($message);
    }
}