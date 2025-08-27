<?php
declare(strict_types=1);
namespace Core\Services;

use App\Models\Users;
use Core\Models\Notifications;
use Core\Lib\Notifications\UserRegistered;

class NotificationService {

    /**
     * Adds notification information to Session Messages.
     *
     * @return void
     */
    public static function flashUnreadNotifications(): void {
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

    /**
     * Sends notifications to users with a particular group.
     *
     * @param object $notification The notification to be sent to users.
     * @param string $userGroup The ACL group that will receive notifications.  
     * The default value is 'Admin'.
     * @return void
     */
    public static function notifyUsers(object $notification, string $userGroup = 'Admin'): void {
        $users = Users::find([
            'conditions' => 'deleted = ?',
            'bind' => [0]
        ]);

        foreach($users as $user) {
            if($user->hasAcl($userGroup)) {
                $user->notify($notification);
            }
        }
    }
}