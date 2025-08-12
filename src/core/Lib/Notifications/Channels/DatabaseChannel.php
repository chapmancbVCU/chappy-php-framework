<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Notifications\Contracts\Channel;
use Core\Models\Notifications;
use Ramsey\Uuid\Uuid;

final class DatabaseChannel implements Channel {
    public static function name(): string { 
        return 'database'; 
    }

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