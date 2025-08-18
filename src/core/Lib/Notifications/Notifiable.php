<?php
declare(strict_types=1);
namespace Core\Lib\Notifications;

use Core\Models\Notifications;
use Core\Lib\Notifications\ChannelRegistry;

trait Notifiable {
    public function notify(
        Notification $notification,
        ?array $channels = null,
        array $payload = []
    ): void {
        $resolved = $channels ?? $notification->via($this);

        foreach($resolved as $channel) {
            $name = $channel instanceof \Core\Lib\Notifications\Channel ? $channel->value : (string)$channel;
            $toMethod = 'to' . ucfirst($name);

            $messagePayload = method_exists($notification, $toMethod)
                ? $notification->{$toMethod}($this)
                : null;
            
            $combinedPayload = [
                'message' => $messagePayload,
                'meta' => $payload
            ];

            $driver = ChannelRegistry::resolve($name);
            $driver->send($this, $notification, $combinedPayload);
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