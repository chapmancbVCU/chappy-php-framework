<?php
declare(strict_types=1);
namespace Core\Exceptions\Logger;
use Core\Exceptions\FrameworkRuntimeException;
use Core\Lib\Logging\Logger;
/**
 * Handles exceptions related to Logger severity levels.
 */
final class LoggerLevelException extends FrameworkRuntimeException {
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
        Logger::log($message, Logger::ERROR);
    }
}