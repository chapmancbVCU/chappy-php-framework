<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Contracts;

use Core\Lib\Notifications\Notification;

/**
 * Contract for delivering notifications over a specific transport (database, mail, SMS, etc).
 *
 * Implementations should:
 * - Accept a notifiable entity and a notification instance.
 * - Consume the payload produced by the corresponding toX() method (e.g., toDatabase(), toMail()).
 * - Perform the side-effecting delivery and throw on failure (so callers can log/retry).
 *
 * @template TNotifiable of object
 * @template TNotification of Notification
 *
 * @see \Core\Lib\Notifications\Notification::via()
 * @see \Core\Lib\Notifications\Notifiable::notify()
 */
interface Channel {
    /**
     * Deliver the notification for this channel.
     *
     * Implementations SHOULD be idempotent when possible, or document non-idempotent behavior.
     * If delivery fails, throw an exception to allow the caller (or a queue worker) to handle retries.
     *
     * @param object $notifiable The user/entity receiving the notification.
     * @param Notification $notification The notification instance.
     * @param mixed $payload Usually the result of toX() (array/DTO)
     * @return void
     */
    public function send(object $notifiable, Notification $notification, mixed $payload): void;

    /**
     * The short channel name used in via(): e.g. 'database', 'mail', 'sms'.
     *
     * @return string The name of the channel.
     */
    public static function name(): string;
}