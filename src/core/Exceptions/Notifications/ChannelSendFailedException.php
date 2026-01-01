<?php
declare(strict_types=1);

namespace Core\Exceptions\Notifications;

/**
 * Handles exceptions related to sending through a channel.  Extends the 
 * ChannelException class
 */
final class ChannelSendFailedException extends ChannelException {
    /**
     * Create an exception indicating that delivery over a specific notification
     * channel failed. Includes contextual metadata (channel name, notification
     * class, and notifiable id) to aid logging and retries.
     *
     * @param non-empty-string $channel          Short channel name (e.g. "mail", "database").
     * @param string           $notificationClass Fully-qualified class name of the notification (or mailer) involved.
     * @param int|string|null  $notifiableId     Identifier of the target entity, if known.
     * @param string           $message          Human-readable summary; will be combined with context.
     * @param \Throwable|null  $previous         Underlying cause to chain.
     *
     * @phpstan-param class-string $notificationClass
     * @psalm-param   class-string $notificationClass
     */
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