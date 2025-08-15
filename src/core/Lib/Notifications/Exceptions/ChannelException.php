<?php
declare(strict_types=1);

namespace Core\Lib\Notifications\Exceptions;

/**
 * Handles exceptions related to channels.  Extends the 
 * NotificationException class.
 */
class ChannelException extends NotificationException {
    public function __construct(
        public readonly string $channel,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct("[$channel] $message", 0, $previous);
    }
}