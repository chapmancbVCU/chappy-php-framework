<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

/**
 * Base Notification class similar to Laravel's.
 * Extend this for each specific notification type.
 */
abstract class Notification {
    /**
     * The delivery channels (e.g. ['database', 'mail']).
     * @param mixed $notifiable The notifiable entity (e.g. a User instance)
     * @return array
     */
    public function via($notifiable): array {
        return ['database'];
    }

    /**
     * Representation of the notification in the database.
     *
     * @param mixed $notifiable The notifiable entity.
     * @return array
     */
    public function toDatabase($notifiable): array {
        return [];
    }

    /**
     * Representation of the notification in mail.
     *
     * @param [type] $notifiable
     * @return void
     */
    public function toMail($notifiable) {
        return [];
    }
}