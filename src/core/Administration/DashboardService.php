<?php
declare(strict_types=1);
namespace Core\Administration;

use core\Auth\AuthService;
use App\Models\Users;
use Core\Lib\Pagination\Pagination;

final class DashboardService {

    public static function allUsersExceptCurrent() {
        return Users::findTotal([
            'conditions' => 'id != ?',
            'bind' => [AuthService::currentUser()->id]
        ]);
    }

    public static function paginateUsers(Pagination $pagination) {
        return Users::find($pagination->paginationParams(
            'id != ?',
            [AuthService::currentUser()->id],
            'created_at DESC'
        ));
    }
}