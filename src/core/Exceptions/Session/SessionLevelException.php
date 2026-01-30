<?php
declare(strict_types=1);
namespace Core\Exceptions\Session;
use Core\Exceptions\FrameworkRuntimeException;

/**
 * Handles exceptions related to Logger severity levels.
 */
final class SessionLevelException extends FrameworkRuntimeException {
    /**
     * Create a new SessionLevelException.
     *
     * @param string $level The session level.
     * @param string $message Optional human-readable details.
     * @param \Throwable|null $previous Optional underlying cause for exception chaining.
     */
    public function __construct(
        public readonly string $level,
        string $message = "",
        ?\Throwable $previous = null
    ) {
        $message = $message . "[$level]";
        parent::__construct(
            $message,
            0,
            $previous
        );
        error($message);
    }
}