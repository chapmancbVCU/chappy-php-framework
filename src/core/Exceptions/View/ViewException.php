<?php
declare(strict_types=1);
namespace Core\Exceptions;

/**
 * Handles exceptions related to views/layouts.  Extends the FrameworkException 
 * class.
 */
class ViewException extends FrameworkException {
    /**
     * Create a new exception to handle faults related to rendering content.
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