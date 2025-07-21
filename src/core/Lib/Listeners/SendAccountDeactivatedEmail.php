<?php
declare(strict_types=1);
namespace Core\Lib\Listeners;

use Core\Lib\Events\AccountDeactivated;
use Core\Lib\Mail\AccountDeactivatedMailer;

/**
 * Class for sending account deactivated E-mail.
 */
class SendAccountDeactivatedEmail {
    /**
     * Handles event for sending account deactivated E-mail.
     *
     * @param AccountDeactivated $event The Event.
     * @return void
     */
    public function handle(AccountDeactivated $event): void {
        $user = $event->user;
        flashMessage('info', "Account Deactivated Email sent to {$user->username} via {$user->email}");
        AccountDeactivatedMailer::sendTo($user);
    }
}