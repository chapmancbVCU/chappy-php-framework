<?php
declare(strict_types=1);

namespace Core\Exceptions\Notifications;

/**
 * Handles exceptions related to channels.  Extends the 
 * NotificationException class.
 */
class ChannelException extends NotificationException {
    /**
     * Create a channel-scoped notification exception.
     *
     * The final exception message is prefixed with the channel in square
     * brackets, e.g. "[mail] Transport unavailable". The channel name is also
     * stored on the exception as a readonly property for structured logging.
     *
     * @param non-empty-string $channel  Short channel name (e.g. "mail", "database").
     * @param string           $message  Optional human-readable details.
     * @param \Throwable|null  $previous Optional underlying cause for exception chaining.
     *
     * @phpstan-param non-empty-string $channel
     * @psalm-param   non-empty-string $channel
     */
    public function __construct(
        public readonly string $channel,
        string $message = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct("[$channel] $message", 0, $previous);
    }
}