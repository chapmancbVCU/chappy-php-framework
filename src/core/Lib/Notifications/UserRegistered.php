<?php
namespace Core\Lib\Notifications;

use App\Models\Users;
use Core\Lib\Utilities\DateTime;
use Core\Lib\Notifications\Notification;

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
        return ['database', 'mail']; // we only use database for now
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
            'registered_at' => DateTime::timeStamps()
        ];
    }

    public function toMail($notifiable): array {
        return [
            'to' => 'chad.chapman2010+admin@gmail.com',
            'subject' => 'New user sign up',
            'html' => "<p>New user has registered: {$this->user->username}</p>",
            'text' => "New user has registered {$this->user->username}"
        ];
    }
}
