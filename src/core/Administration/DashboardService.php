<?php
declare(strict_types=1);
namespace Core\Administration;

use Core\Input;
use App\Models\Users;
use core\Auth\AuthService;
use Core\Lib\Pagination\Pagination;

final class DashboardService {
    /**
     * Returns list of paginated users.
     *
     * @param Pagination $pagination Instance of Pagination class
     * @return array An array of paginated users.
     */
    public static function paginateUsers(Pagination $pagination): array {
        return Users::find($pagination->paginationParams(
            'id != ?',
            [AuthService::currentUser()->id],
            'created_at DESC'
        ));
    }

    /**
     * Assist in toggling reset_password field.
     *
     * @param Input $request The request.
     * @return integer 1 if reset_password is 'on', otherwise we return 0.
     */
    public static function toggleResetPassword(Input $request): int {
        return ($request->get('reset_password') == 'on') ? 1 : 0;
    }

    /**
     * Returns query number of users excluding current.
     *
     * @return int The number of users except current.
     */
    public static function totalUserCountExceptCurrent(): int {
        return Users::findTotal([
            'conditions' => 'id != ?',
            'bind' => [AuthService::currentUser()->id]
        ]);
    }
}