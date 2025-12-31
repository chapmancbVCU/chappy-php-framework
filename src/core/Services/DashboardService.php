<?php
declare(strict_types=1);
namespace Core\Services;

use App\Models\Users;
use Core\Services\AuthService;
use Core\Lib\Pagination\Pagination;
use Core\Session;

/**
 * Supports admin dashboard operations.
 */
final class DashboardService {
    /**
     * Warns if admin user is attempting to view an area they should not be.
     *
     * @param Users $user The current user.
     * @param string $redirect The destination for redirect.
     * @return void
     */
    public static function checkIfCurrentUser(Users $user, string $redirect = '') {
        if($user == AuthService::currentUser()) {
            flashMessage(Session::DANGER, 'Logged in admin user can\'t be edited or viewed through admin dashboard.');
            redirect($redirect);
        }
    }

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