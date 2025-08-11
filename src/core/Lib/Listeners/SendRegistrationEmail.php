<?php
declare(strict_types=1);
namespace Core\Lib\Listeners;

use Core\Lib\Events\UserRegistered;
use Core\Lib\Notifications\UserRegistered as UserRegisteredNotification;
use Core\Lib\Mail\WelcomeMailer;
use Core\Services\NotificationService;

/**
 * Class for sending password reset E-mail.
 */
class SendRegistrationEmail {
    /**
     * Handles event for sending password reset E-mail.
     *
     * @param UserRegistered $event The event.
     * @return void
     */
    public function handle(UserRegistered $event): void {
        $user = $event->user;
        $shouldSendEmail = $event->shouldSendEmail;
        NotificationService::notifyAdmins(new UserRegisteredNotification($user));
        if($shouldSendEmail) {
            WelcomeMailer::sendTo($user);
        }
    }
}