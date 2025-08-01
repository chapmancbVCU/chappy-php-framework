<?php
namespace Core\Lib\Notifications;

use Core\Lib\Notifications\Notification;
use App\Models\Users;

class UserRegistered extends Notification
{
    protected $user;

    public function __construct(Users $user)
    {
        $this->user = $user;
    }

    /**
     * Specify which channels to deliver to.
     */
    public function via($notifiable): array
    {
        return ['database']; // we only use database for now
    }

    /**
     * Data stored in the notifications table.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'user_id'   => $this->user->id,
            'username'  => $this->user->username ?? $this->user->email,
            'message'   => "A new user has registered: {$this->user->username}",
            'registered_at' => date('Y-m-d H:i:s')
        ];
    }
}
