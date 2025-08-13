<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Notifications\Contracts\Channel;
use Core\Models\Notifications;
use Ramsey\Uuid\Uuid;

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
     * @param object $notifiable   The entity receiving the notification (uses Notifiable, must expose an `id`).
     * @param object $notification The notification instance being delivered.
     * @param array<string,mixed>|null $payload
     *        Structured data to store in the `data` column (will be JSON-encoded).
     *
     * @return void
     */
    public function send($notifiable, $notification, $payload): void {
        $record = new Notifications();
        $record->id = Uuid::uuid4()->toString();
        $record->type = get_class($notification);
        $record->notifiable_type = get_class($notifiable);
        $record->notifiable_id = $notifiable->id;
        $record->data = json_encode($payload);
        $record->read_at = null;
        $record->save();
    }
}