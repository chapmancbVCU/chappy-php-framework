<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Logging\Logger;
use Core\Lib\Notifications\Contracts\Channel;

final class LogChannel implements Channel {
    public static function name(): string {
        return 'log';
    }

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