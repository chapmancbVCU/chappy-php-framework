<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

use Core\Models\Notifications;
use Ramsey\Uuid\Uuid;

trait Notifiable {
    public function notify(Notification $notification): void {
        foreach($notification->via($this) as $channel) {
            if($channel == 'database') {
                $data = $notification->toDatabase($this);
                $record = new Notifications();
                $record->id = Uuid::uuid4()->toString();
                $record->type = get_class($notification);
                $record->notifiable_type = get_class($this);
                $record->notifiable_id = $this->id;
                $record->data = json_encode($data);
                $record->read_at = null;
                $record->save();
            }
        }
    }

    public function notifications(): array {
        return Notifications::find([
            'conditions' => 'notifiable_id = ? AND notifiable_type = ?',
            'bind' => [$this->id, get_class($this)]
        ]);
    }
}