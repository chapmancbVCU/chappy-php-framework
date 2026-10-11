<?php
declare(strict_types=1);
namespace Core\Services;

use App\Models\Users;
use Core\Services\AuthService;
use Core\Lib\Pagination\Pagination;
use Core\Session;
use Core\Lib\Pagination\SearchFilter;
use Core\Models\ACL;

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
    public static function checkIfCurrentUser(Users $user, string $redirect = ''): void {
        if($user == AuthService::currentUser()) {
            flashMessage(Session::DANGER, 'Logged in admin user can\'t be edited or viewed through admin dashboard.');
            redirect($redirect);
        }
    }

    /**
     * Status filter options: value as stored in users.inactive => label.
     * Same value => label shape as ACL::getOptionsForForm().
     */
    public const STATUS_OPTIONS = [
        '0' => 'Active',
        '1' => 'Inactive',
    ];
    
    /**
     * Builds the search/filter definition for the admin user list. Always
     * excludes the current user.
     *
     * @param object $request The request object.
     * @return SearchFilter
     */
    public static function userFilter(object $request): SearchFilter {
        return SearchFilter::fromRequest($request)
            ->where('id != ?', [AuthService::currentUser()->id])
            ->searchable(['username', 'email', 'fname', 'lname'])
            ->filterable(['inactive' => array_keys(self::STATUS_OPTIONS)])
            // users.acl holds a JSON array like ["Admin","test"]; match any user
            // whose array contains the selected access level.
            ->filterJsonContains('acl', array_keys(ACL::getOptionsForForm()))
            ->sortable(['username', 'acl', 'inactive', 'created_at', 'updated_at'], 'created_at', 'desc');
    }
    
    /**
     * Returns list of paginated users matching the filter.
     *
     * @param Pagination $pagination Instance of Pagination class.
     * @param SearchFilter $filter The active search/filter.
     * @return array An array of paginated users.
     */
    public static function paginateUsers(Pagination $pagination, SearchFilter $filter): array {
        return Users::find($pagination->queryParams($filter));
    }
    
    /**
     * Returns the number of users matching the filter.
     *
     * @param SearchFilter $filter The active search/filter.
     * @return int The number of matching users.
     */
    public static function totalUsers(SearchFilter $filter): int {
        return Users::findTotal($filter->findParams());
    }
}