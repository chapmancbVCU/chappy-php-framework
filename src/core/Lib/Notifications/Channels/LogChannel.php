<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Logging\Logger;
use Core\Lib\Notifications\Notification;
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
     * @param object $notifiable   The entity that is receiving the notification
     *                            (e.g., a User model instance or identifier).
     * @param Notification $notification The notification instance being sent. Must
     *                            optionally implement `toLog()` or `toArray()`.
     * @param mixed $payload      Additional payload or metadata provided by
     *                            the dispatcher (e.g., context or overrides).
     *
     * @return void
     */
    public function send(object $notifiable, Notification $notification, mixed $payload): void
    {
        // Keep the objects; just capture class names for logging
        $notificationClass = get_class($notification);
        $notifiableClass   = get_class($notifiable);

        // Structured data: prefer the notification payload; fallback to provided payload if empty
        $data = $notification->toArray($notifiable);
        if (empty($data) && is_array($payload)) {
            $data = $payload;
        }

        // Human-friendly message: use toLog(); if empty, fall back to a reasonable default
        $message = $notification->toLog($notifiable);
        if ($message === '') {
            $message = isset($data['message']) && is_string($data['message'])
                ? $data['message']
                : sprintf('Notification %s for %s', $notificationClass, $notifiableClass);
        }

        $log = [
            'notification' => $notificationClass,
            'notifiable'   => $notifiableClass,
            'message'      => $message,
            'data'         => $data,
        ];

        Logger::log(
            json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'info'
        );
    }
}