<?php
declare(strict_types=1);
namespace Core\Services;

use Core\Input;
use App\Models\Users;
use App\Jobs\SendWelcomeEmail;
use Core\Models\ProfileImages;
use Core\Lib\FileSystem\Uploads;
use Core\Lib\Queue\QueueManager;
use Core\Lib\Utilities\DateTime;
use Core\Lib\Events\EventManager;
use Core\Lib\Events\AccountDeactivated;
use Core\Lib\Events\UserPasswordResetRequested;

/**
 * Provides functions for managing users.
 */
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
        $id = $request->get('image_id');
        $image = ProfileImages::findById((int)$id);
        if($image) {
            ProfileImages::deleteById($image->id);
            return ['success' => true, 'model_id' => $image->id];
        }
        return ['success' => false];
    }

    /**
     * Acts as safeguard to ensure incorrect user is not updated.
     *
     * @param Users $user The user object to test.
     * @return void
     */
    public static function ensureAuthenticatedUser(Users $user): void {
        $currentUser = AuthService::currentUser();

        if (!$user || !$currentUser || $user->id !== $currentUser->id) {
            flashMessage('danger', 'You do not have permission to edit this user.');
            redirect('');
        }
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

    public static function queueWelcomeMailer(int $user_id, string $queueName = 'default') {
        $queue = new QueueManager();
        $job   = new SendWelcomeEmail(['user_id' => $user_id], 0); // delay=0

        $payload = $job->toPayload();
        // Fix DATETIME format
        $payload['available_at'] = DateTime::nowPlusSeconds($job->delay());

        $queue->push($payload, $queueName);
    }

    /**
     * Sends E-mail to user when account is deactivated as appropriate.
     *
     * @param Users $user The user we will send E-mail to.
     * @param bool $shouldSendEmail Sends E-mail when true.
     * @return void
     */
    public static function sendWhenSetToInactive(Users $user, bool $shouldSendEmail = false): void {
        if($shouldSendEmail) {
            EventManager::dispatcher()->dispatch(
                new AccountDeactivated($user)
            );
        }
    }

    /**
     * Sends E-mail to user when reset_password flag is set as appropriate.
     *
     * @param Users $user The user we will send E-mail to.
     * @param bool $shouldSendEmail Sends E-mail when true.
     * @return void
     */
    public static function sendWhenSetToResetPW(Users $user, bool $shouldSendEmail = false): void {
        if($shouldSendEmail) {
            EventManager::dispatcher()->dispatch(
                new UserPasswordResetRequested($user)
            );
        }
    }

    /**
     * Assist in toggling inactive field.  Returns boolean value to determine 
     * if E-mail should be sent.  To properly test if E-mail should be sent 
     * get value of $user->inactive before post.
     *
     * @param Users $user The user whose status we want to set.
     * @param Input $request The request.
     * @param int|null $currentInactive Value of $user->inactive before post.
     * @return bool true if we want to send mail and otherwise false.
     */
    public static function toggleAccountStatus(Users $user, Input $request, ?int $currentInactive = null): bool {
        $user->inactive = ($request->get('inactive') == 'on') ? 1 : 0;
        $user->login_attempts = ($user->inactive == 0) ? 0 : $user->login_attempts;

        return $currentInactive !== null && $currentInactive === 0 && $user->inactive === 1;
    }

    /**
     * Assist in toggling reset_password field.
     *
     * @param Users $user The user whose status we want to set.
     * @param Input $request The request.z
     * @param int|null $currentReset Value of $user->reset_password before post.
     * @return int 1 if reset_password is 'on', otherwise we return 0.
     */
    public static function toggleResetPassword(Users $user, Input $request, ?int $currentReset = null): bool {
        $user->reset_password = ($request->get('reset_password') == 'on') ? 1 : 0;
        return $currentReset !== null && $currentReset === 0 && $user->reset_password === 1;
    }

    /**
     * Updates user's password
     *
     * @param Users $user The user whose password we want to update.
     * @param Input $request The request.
     * @return bool True if password is updated, otherwise false
     */
    public static function updatePassword(Users $user, Input $request): Users|bool {
        if(!password_verify($request->get('current_password'), $user->password)) {
            $user->addErrorMessage('password', 'You entered wrong password');
        }

        $user->assign($request->get(), Users::blackListedFormKeys);
        $user->setChangePassword(true);
        $user->confirm = AuthService::confirm($request);

        if($user->save()) {
            $user->setChangePassword(false);
            return true;
        }
        return false;
    }
}