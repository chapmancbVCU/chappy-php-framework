<?php
declare(strict_types=1);
namespace core\Services;

use Core\Input;
use App\Models\Users;
use Core\Models\ProfileImages;
use Core\Lib\FileSystem\Uploads;

final class UserService {
    /**
     * Deletes user if not admin and unlinks profile images if $unlink
     * is set to true.
     *
     * @param int $id The id for user we want to delete.
     * @param bool $unlink Determines if profile images are deleted.
     * @return void
     */
    public static function deleteIfAllowed(int $id, bool $unlink = false): void {
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
     * Deletes profile image
     *
     * @param Input $request The request for deleting image.
     * @return array JSON response array.
     */
    public static function deleteProfileImage(Input $request): array {
        $user = AuthService::currentUser();
        $id = $request->get('image_id');
        $image = ProfileImages::findById((int)$id);
        if($user && $image) {
            ProfileImages::deleteById($image->id);
            return ['success' => true, 'model_id' => $image->id];
        }
        return ['success' => false];
    }

    /**
     * Uploads and sorts profile images.
     *
     * @param Users $user The user whose profile images we want to manage.
     * @param Uploads|null $uploads The Uploads object or profile image upload.
     * @param string|null $sortedImages Order of sorted images.
     * @return void
     */
    public static function handleProfileImages(Users $user, ?Uploads $uploads, ?string $sortedImages): void {
        if($uploads) {
            ProfileImages::uploadProfileImage($user->id, $uploads);
        }

        if($sortedImages) {
            ProfileImages::updateSortByUserId($user->id, json_decode($sortedImages));
        }
    }

    /**
     * Updates user's password
     *
     * @param Users $user The user whose password we want to update.
     * @param Input $request The request.
     * @return bool True if password is updated, otherwise false
     */
    public static function updatePassword(Users $user, Input $request): bool {
        if(!password_verify($request->get('current_password'), $user->password)) {
            return false;
        }

        $user->assign($request->get(), Users::blackListedFormKeys);
        $user->setChangePassword(true);
        $user->confirm = $request->get('confirm');

        if($user->save()) {
            $user->setChangePassword(false);
            return true;
        }
        return false;
    }

    /**
     * Assist in toggling inactive field.
     *
     * @param Input $request The request.
     * @return int 1 if inactive is 'on', otherwise we return 0.
     */
    public static function toggleAccountStatus(Input $request): int {
        return ($request->get('inactive') == 'on') ? 1 : 0;
    }

    /**
     * Assist in toggling reset_password field.
     *
     * @param Input $request The request.
     * @return int 1 if reset_password is 'on', otherwise we return 0.
     */
    public static function toggleResetPassword(Input $request): int {
        return ($request->get('reset_password') == 'on') ? 1 : 0;
    }
}