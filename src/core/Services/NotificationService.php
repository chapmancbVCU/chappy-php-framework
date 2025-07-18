<?php
declare(strict_types=1);
namespace Core\Services;

use App\Models\Users;
use Core\Models\Notifications;
use Core\Lib\Notifications\UserRegistered;

class NotificationService {

    public static function flashUnreadNotifications() {
        $admin = AuthService::currentUser();
        $messages = [];
        foreach ($admin->notifications() as $notification) {
            $data = json_decode($notification->data, true);
            if (isset($data['message'])) {
                $messages[] = $data['message'];
            }
            Notifications::markAsReadById($notification->id);
        }
        
        if (!empty($messages)) {
            $finalMessage = implode('<br>', $messages);
            flashMessage('info', $finalMessage);
        }
    }

    public static function sendUserRegistrationNotification(Users $newUser): void {
        $admins = Users::find([
            'conditions' => 'deleted = ?',
            'bind' => [0]
        ]);

        foreach($admins as $admin) {
            if($admin->hasAcl('Admin')) {
                $admin->notify(new UserRegistered($newUser));
            }
        }
    }
}