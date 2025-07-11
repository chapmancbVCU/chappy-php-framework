<?php
declare(strict_types=1);
namespace core\Services;

use App\Models\Users;
use Core\Models\ProfileImages;

final class UserService {

    public static function deleteIfAllowed(int $id, bool $unlink) {
        $user = Users::findById((int)$id);
        if($user && $user->acl != '["Admin"]') {
            ProfileImages::deleteImages($id, $unlink);
            $user->delete();
            flashMessage('success', 'User has been deleted.');
        } else {
            flashMessage('danger', 'Cannot delete Admin user!');
        }
    }

    /**
     * Assist in toggling inactive field.
     *
     * @param Input $request The request.
     * @return integer 1 if inactive is 'on', otherwise we return 0.
     */
    public static function toggleAccountStatus(Input $request) {
        return ($request->get('inactive') == 'on') ? 1 : 0;
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
}