<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Contracts;

interface Channel {
    /**
     * 
     *
     * @param mixed $notifiable The user/entity receiving the notification.
     * @param mixed $notification The notification instance.
     * @param mixed $payload Usually the result of toX() (array/DTO)
     * @return void
     */
    public function send(mixed $notifiable, mixed $notification, mixed $payload): void;

    /**
     * The short channel name used in via(): e.g. 'database', 'mail', 'sms'.
     *
     * @return string The name of the channel.
     */
    public static function name(): string;
}