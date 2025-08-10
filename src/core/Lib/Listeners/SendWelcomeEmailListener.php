<?php
namespace Core\Lib\Listeners;

use Console\Helpers\Tools;
use Core\Services\UserService;
use Core\Lib\Events\UserRegistered;
use Core\Services\NotificationService;
use Core\Lib\Events\Contracts\ShouldQueue;
use Core\Lib\Events\Contracts\QueuePreferences;

class SendWelcomeEmailListener implements ShouldQueue, QueuePreferences {
    public function handle(UserRegistered $event) : void {
        NotificationService::sendUserRegistrationNotification($event->user);
        $shouldSendEmail = $event->shouldSendEmail;
        if($shouldSendEmail) {
            Tools::info("Test: " . $shouldSendEmail);
        }
        UserService::queueWelcomeMailer((int)$event->user->id, $this->viaQueue());
    }

    public function viaQueue(): ?string { return 'mail'; }
    public function delay(): int { return 60; }               // 1 min
    public function backoff(): int|array { return [10, 30, 60]; }
    public function maxAttempts(): int { return 5; }
}