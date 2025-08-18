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
    private const CONTROL_KEYS = ['message','level','log_channel','_meta','meta'];

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
     * Return the list of control keys that will be stripped from the data bag.
     *
     * @return list<string>
     *
     * @internal Exposed for testing and reuse; not part of the public channel API.
     */
    private static function controlKeys(): array {
        return self::CONTROL_KEYS;
    }

    /**
     * Build the consolidated "data" bag for logging.
     *
     * Strategy:
     *  1) Start with the notification’s {@see Notification::toArray()} result.
     *  2) Merge in the payload array *minus* control keys, allowing payload
     *     to override keys from step 1 so per‑send overrides are visible.
     *
     * @param object       $notifiable   The entity receiving the notification.
     * @param Notification $notification The notification instance.
     * @param array<string,mixed> $payloadArr Payload provided to the channel.
     *
     * @return array<string,mixed> Final data bag to include under "data".
     */
    private static function logData(object $notifiable, Notification $notification, array $payloadArr): array {
        $controlKeys = self::controlKeys();
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

        return $data;
    }

    /**
     * Determine the human‑readable message to log.
     *
     * Priority:
     *  1) Non‑null payload‑provided message (already normalized to string)
     *  2) Notification‑provided {@see Notification::toLog()} string
     *  3) "data['message']" if present and string
     *  4) A synthesized "Notification X for Y" fallback
     *
     * @param array<string,mixed> $data               Final data bag (may contain 'message').
     * @param object              $notifiable         The notifiable entity.
     * @param class-string        $notifiableClass    Class name of the notifiable.
     * @param Notification        $notification       The notification instance.
     * @param class-string        $notificationClass  Class name of the notification.
     * @param string|null         $payloadMessage     Message extracted from payload, if any.
     *
     * @return string Human‑readable message to include in the log entry.
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
     * Extract a string message from the payload, if present.
     *
     * Accepts either a string or any JSON‑serializable value under the "message"
     * key. Non‑string values are JSON‑encoded with unicode and slashes preserved.
     *
     * @param array<string,mixed> $payloadArr The payload provided to the channel.
     *
     * @return string|null A normalized message string or null if no message key exists.
     */
    private static function payloadMessage(array $payloadArr): string {
        if(array_key_exists('message', $payloadArr)) {
            $payloadMessage = is_string($payloadArr['message'])
            ? $payloadArr['message']
            : json_encode($payloadArr['message'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return $payloadMessage;
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

        $payloadMessage = self::payloadMessage($payloadArr);
        $data = self::logData($notifiable, $notification, $payloadArr);

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