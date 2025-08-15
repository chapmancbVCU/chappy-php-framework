<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Notifications\Contracts\Channel;

final class LogChannel implements Channel {
    public static function name(): string {
        return 'name';
    }

    public function send(mixed $notifiable, mixed $notification, mixed $payload): void {
        
    }
}