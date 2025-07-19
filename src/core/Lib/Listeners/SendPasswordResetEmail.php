<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use Core\Lib\Events\UserPasswordResetRequested;
use Core\Lib\Mail\PasswordResetMailer;

/**
 * Class for sending password reset E-mail.
 */
class SendPasswordResetEmail {
    /**
     * Handles event for sending password reset E-mail.
     *
     * @param UserPasswordResetRequested $event The event.
     * @return void
     */
    public function handle(UserPasswordResetRequested $event): void {
        $user = $event->user;

        flashMessage('info', "Reset Password Email sent to {$user->username} via {$user->email}");
        PasswordResetMailer::sendTo($user);
    }
}