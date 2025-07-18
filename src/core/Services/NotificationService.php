<?php
declare(strict_types=1);
namespace Core\Services;

use App\Models\Users;
use Core\Lib\Notifications\UserRegistered;

class NotificationService {

    public static function sendUserRegistrationNotification(Users $newUser) {
        $admins = Users::find([
            'conditions' => 'deleted = ?',
            'bind' => [0]
        ]);

        foreach($admins as $admin) {
            if($admin->hasAcl('admin')) {
                $admin->notify(new UserRegistered($newUser));
            }
        }
    }
}