<?php
declare(strict_types=1);
namespace Core\Administration;

use core\Auth\AuthService;
use App\Models\Users;

final class DashboardService {

    public static function allUsersExceptCurrent() {
        return Users::findTotal([
            'conditions' => 'id != ?',
            'bind' => [AuthService::currentUser()->id]
        ]);
    }
}