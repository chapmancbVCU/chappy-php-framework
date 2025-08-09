<?php
declare(strict_types=1);
namespace Core\Lib\Listeners;

use Core\Lib\Events\UserPasswordUpdated;
use Core\Lib\Mail\UpdatePasswordMailer;

/**
 * Class for sending password updated E-mail.
 */
class SendPasswordUpdateEmail {
    /**
     * Handles event for sending password updated E-mail.
     *
     * @param UserPasswordUpdated $event The event.
     * @return void
     */
    public function handle(UserPasswordUpdated $event): void {
        $user = $event->user;
        UpdatePasswordMailer::sendTo($user);
    }
}