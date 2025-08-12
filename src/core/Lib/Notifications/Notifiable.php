<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

use Core\Models\Notifications;
use Core\Lib\Notifications\ChannelRegistry;

trait Notifiable {
    public function notify(Notification $notification): void {
        $channels = $notification->via($this);

        foreach($channels as $channel) {
            $toMethod = 'to' . ucfirst($channel);
            $payload = method_exists($notification, $toMethod)
                ? $notification->{$toMethod}($this)
                : null;
            
            $driver = ChannelRegistry::resolve($channel);
            $driver->send($this, $notification, $payload);
        }
    }

    public function notifications(): array {
        $results = Notifications::find([
            'conditions' => 'notifiable_id = ? AND read_at IS NULL',
            'bind' => [$this->id],
            'order' => 'created_at DESC'
        ]);
        return is_array($results) ? $results : [];
    }
}