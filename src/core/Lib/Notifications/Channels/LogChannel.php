<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Logging\Logger;
use Core\Lib\Notifications\Contracts\Channel;


/**
 * Notification channel that writes notifications to the application log.
 *
 * This channel is useful for development or local environments where
 * sending real notifications (e.g., email, SMS, Slack) is not desired.
 * It serializes the notifiable entity and notification message into a
 * JSON structure and writes it to the configured Logger.
 */
final class LogChannel implements Channel {
    /**
     * Returns the canonical name of the channel.
     *
     * Used by the notification dispatcher to identify this channel
     * when the notification's `via()` method includes "log".
     *
     * @return string The string identifier for this channel ("log").
     */
    public static function name(): string {
        return 'log';
    }

    /**
     * Send the given notification to the log.
     *
     * If the notification implements a `toLog()` method, its return
     * value will be used as the log message. Otherwise, if the
     * notification implements `toArray()`, the array will be logged.
     * If neither method exists, the notifiable itself is cast to string.
     *
     * The output is encoded as JSON and written at INFO level.
     *
     * @param mixed $notifiable   The entity that is receiving the notification
     *                            (e.g., a User model instance or identifier).
     * @param mixed $notification The notification instance being sent. Must
     *                            optionally implement `toLog()` or `toArray()`.
     * @param mixed $payload      Additional payload or metadata provided by
     *                            the dispatcher (e.g., context or overrides).
     *
     * @return void
     */
    public function send(mixed $notifiable, mixed $notification, mixed $payload): void {
        $log = [];


        $log['message'] = method_exists($notification, 'toLog')
            ? $notification->toLog($notifiable)
            : (method_exists($notification, 'toArray') 
                ? $notification->toArray($notifiable) 
                : [(string)$notifiable]);

        $log['notifiable'] = is_object($notifiable) ? get_class($notifiable) : $notifiable;
        
        Logger::log(json_encode($log, JSON_PRETTY_PRINT), 'info');
    }
}