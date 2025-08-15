<?php
declare(strict_types=1);

namespace Core\Lib\Notifications\Exceptions;

/**
 * Handles exceptions related to sending through a channel.  Extends the 
 * ChannelException class
 */
final class ChannelSendFailedException extends ChannelException {
    public function __construct(
        string $channel,
        public readonly string $notificationClass,
        public readonly string|int|null $notifiableId,
        string $message = 'Send failed',
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $channel,
            "$message (notification=$notificationClass, notifiable=$notifiableId)",
            $previous
        );
    }
}