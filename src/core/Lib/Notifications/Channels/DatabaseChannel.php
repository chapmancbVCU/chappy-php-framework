<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Notifications\Contracts\Channel;
use Core\Lib\Notifications\Notification;
use Core\Models\Notifications;
use Ramsey\Uuid\Uuid;
use Core\Lib\Notifications\Notification as BaseNotification;
use Core\Lib\Notifications\Exceptions\{
    InvalidPayloadException,
    NotifiableRoutingException,
    ChannelSendFailedException
};

/**
 * Database-backed notification channel.
 *
 * Persists a notification record to the `notifications` table using the
 * {@see \Core\Models\Notifications} model. The payload is expected to be the
 * result of the notification's {@see \Core\Lib\Notifications\Notification::toDatabase()}
 * and is JSON-encoded for storage.
 *
 * Example usage (via Notifiable trait):
 * $user->notify(new \App\Notifications\UserRegistered($user));
 *
 * @see \Core\Lib\Notifications\Contracts\Channel
 */
final class DatabaseChannel implements Channel {
    /**
     * Short channel name used in Notification::via().
     *
     * @return string The channel identifier, always "database".
     */
    public static function name(): string { 
        return 'database'; 
    }

    /**
     * Persist the notification for the given notifiable entity.
     *
     * Accepts `mixed` per the Channel interface, but expects:
     * - $notifiable: an object (typically using the Notifiable trait) exposing a public `id` (int|string).
     * - $notification: an instance of \Core\Lib\Notifications\Notification.
     * - $payload: array data produced by toDatabase(), or null.
     *
     * @param object $notifiable The user/entity receiving the notification.
     * @param Notification $notification The notification instance.
     * @param mixed $payload Usually the result of toX() (array/DTO)
     *
     * @phpstan-param object $notifiable
     * @phpstan-param \Core\Lib\Notifications\Notification $notification
     * @phpstan-param array<string,mixed>|null $payload
     *
     * @psalm-param object $notifiable
     * @psalm-param \Core\Lib\Notifications\Notification $notification
     * @psalm-param array<string,mixed>|null $payload
     *
     * @throws InvalidPayloadException|NotifiableRoutingException|InvalidPayloadException|ChannelSendFailedException
     * @return void
     */
    #[\Override] // PHP 8.3+ (optional): ensures the signature matches the interface
    public function send(object $notifiable, Notification $notification, mixed $payload): void {
        if(!($notification instanceof BaseNotification)) {
            throw new InvalidPayloadException('DatabaseChannel expects a Notification instance');
        }
        if(!is_object($notifiable) || !isset($notifiable->id)) {
            throw new NotifiableRoutingException("Notifiable must expose a public 'id' for DatabaseChannel");
        }
        if(!is_array($payload) && $payload !== null) {
            throw new InvalidPayloadException('DatabaseChannel expects array|null payload.');
        }

        try {
            $record = new Notifications();
            $record->id = Uuid::uuid4()->toString();
            $record->type = get_class($notification);
            $record->notifiable_type = get_class($notifiable);
            $record->notifiable_id = $notifiable->id;
            $record->data = json_encode($payload);
            $record->read_at = null;
            $record->save();
        } catch(\Throwable $e) {
            throw new ChannelSendFailedException(
                channel: self::name(),
                notificationClass: get_class($notification),
                notifiableId: $notifiable->id ?? null,
                message: 'Database persist failed',
                previous: $e
            );
        }
    }
}