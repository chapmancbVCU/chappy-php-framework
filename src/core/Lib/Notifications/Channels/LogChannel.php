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
     * Configures log array
     *
     * @param array $data Information associated with notification.
     * @param string $message Messages sent for logging.
     * @param array $meta Any meta data that is available.
     * @param string $notifiableClass The name of the notifiable class.
     * @param string $notificationClass The name of the notification class.
     * @return array The log array.
     */
    private static function configureLog(
        array $data, 
        string $message, 
        array $meta, 
        string $notifiableClass, 
        string $notificationClass
    ): array {
        return [
            'notification' => $notificationClass,
            'notifiable'   => $notifiableClass,
            'message'      => $message,
            'data'         => $data,
            'meta'         => $meta
        ];
    }

    /**
     * Generates message for log.
     *
     * @param array $data Information associated with notification.
     * @param object $notifiable The entity that is receiving the notification
     * (e.g., a User model instance or identifier).
     * @param string $notifiableClass The name of the notifiable class.
     * @param Notification $notification $notification The notification instance being sent. Must
     * optionally implement `toLog()` or `toArray()`.
     * @param string $notificationClass The name of the notification class.
     * @param string $payloadMessage Messages obtained from payload.
     * @return string The message for the notification.
     */
    private static function logMessage(
        array $data, 
        object $notifiable,
        string $notifiableClass, 
        Notification $notification,
        string $notificationClass,
        string $payloadMessage
    ): string {
        $message = $payloadMessage ?? $notification->toLog($notifiable);
        if($message === '' || $message == null) {
            if(isset($data['message']) && is_string($data['message'])) {
                $message = $data['message'];
            } else {
                $message = sprintf('Notification %s for %s', $notificationClass, $notifiableClass);
            }
        }
        return $message;
    }

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

        // Extract overrides/metadata from payload (if provided)
        $payloadArr = is_array($payload) ? $payload : [];
        $level = isset($payloadArr['level']) ? (string)$payloadArr['level'] : 'info';
        $meta = $payloadArr['_meta'] ?? $payloadArr['meta'] ?? [];

        $payloadMessage = null;
        if(array_key_exists('message', $payloadArr)) {
            $payloadMessage = is_string($payloadArr['message'])
            ? $payloadArr['message']
            : json_encode($payloadArr['message'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        // Build a "data" bag:
        // - Start from payload (minus control keys)
        // - Merge with notification->toArray($notifiable) (so both are visible)
        $controlKeys = ['message', 'level', 'log_channel', '_meta', 'meta'];
        $payloadData = array_diff_key($payloadArr, array_flip($controlKeys));
        $notifyArr = $notification->toArray($notifiable);
        $data = [];

        if(is_array($notifyArr) && !empty($notifyArr)) {
            $data = $notifyArr;
        }

        if(!empty($payloadData)) {
            // Payload wins on key collisions so per-send overrides show up
            $data = array_merge($data, $payloadData);
        }

        $message = self::logMessage(
            $data,
            $notifiable,
            $notifiableClass, 
            $notification,
            $notificationClass,
            $payloadMessage
        );

        $log = self::configureLog($data, $message, $meta, $notifiableClass, $notificationClass);
        Logger::log(
            json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $level
        );
    }
}