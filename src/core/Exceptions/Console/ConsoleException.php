<?php
declare(strict_types=1);
namespace Core\Exceptions\Console;
use Core\Exceptions\FrameworkException;
use Core\Lib\Logging\Logger;

/**
 * Handles exceptions related to the console.
 */
final class ConsoleException extends FrameworkException {
    /**
     * Creates new ConsoleException.
     *
     * @param string $message Optional short message to describe what happened.
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
        Logger::log($message, Logger::ERROR);
    }
}