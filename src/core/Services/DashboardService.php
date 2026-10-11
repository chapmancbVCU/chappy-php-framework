<?php
declare(strict_types=1);
namespace Core\Services;

use App\Models\Users;
use Core\Services\AuthService;
use Core\Lib\Pagination\Pagination;
use Core\Session;
use Core\Lib\Pagination\SearchFilter;

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
     * Access level filter options: label => value as stored in users.acl.
     * Adjust to match how your acl column is stored.
     */
    public const ACL_OPTIONS = [
        'Standard' => '[""]',
        'Admin'    => '["Admin"]',
    ];

    /**
     * Status filter options: label => value as stored in users.inactive.
     */
    public const STATUS_OPTIONS = [
        'Active'   => '0',
        'Inactive' => '1',
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
            ->filterable([
                'acl'      => array_values(self::ACL_OPTIONS),
                'inactive' => array_values(self::STATUS_OPTIONS),
            ])
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