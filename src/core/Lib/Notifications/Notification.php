<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

/**
 * Base Notification class similar to Laravel's.
 * Extend this for each specific notification type.
 */
abstract class Notification {
    public const DATABASE = 'database';
    public const LOG = 'log';
    public const MAIL = 'mail';

    /**
     * Generic array representation (fallback for logging, webhooks, etc.).
     * By default, defer to the database payload.
     *
     * @param object $notifiable
     * @return array<string,mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Representation of the notification in the database.
     *
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return array<string,mixed>
     */
    public function toDatabase(object $notifiable): array {
        return [];
    }

    /**
     * Representation of the notification in logging.
     *
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return string Contents for the log.
     */
    public function toLog(object $notifiable): string {
        return '';
    }

    /**
     * Representation of the notification in mail.
     *
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return array array<string,mixed>
     */
    public function toMail(object $notifiable): array {
        return [];
    }

    /**
     * The delivery channels (e.g. ['database', 'mail']).
     *@param object $notifiable Any model/object that uses the Notifiable trait.
     * @return list<'database'|'mail'|'log'>
     */
    public function via(object $notifiable): array {
        return ['database'];
    }
}