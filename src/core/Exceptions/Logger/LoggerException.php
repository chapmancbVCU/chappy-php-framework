<?php
declare(strict_types=1);
namespace Core\Exceptions\Logger;
use Core\Exceptions\FrameworkRuntimeException;

/**
 * Handles exceptions related to file logging.
 */
class LoggerException extends FrameworkRuntimeException {
    /**
     * Create a new exception to handle faults related to log file write 
     * operations.
     *
     * @param string $message Optional short message to describe what happened.
     * @param \Throwable|null $previous Optional underlying cause for exception chaining.
     */
    public function __construct(
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}