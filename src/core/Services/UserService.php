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
}