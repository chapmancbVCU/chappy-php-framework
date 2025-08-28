<?php
declare(strict_types=1);
namespace Core\Lib\Listeners;

use Core\Lib\Events\UserRegistered;
use Core\Lib\Mail\WelcomeMailer;

/**
 * Class for sending user registered E-mail.
 */
class SendRegistrationEmail {
    /**
     * Handles event for sending user registered E-mail.
     *
     * @param UserRegistered $event The event.
     * @return void
     */
    public function handle(UserRegistered $event): void {
        $user = $event->user;
        $shouldSendEmail = $event->shouldSendEmail;
        if($shouldSendEmail) {
            WelcomeMailer::sendTo($user);
        }
    }
}