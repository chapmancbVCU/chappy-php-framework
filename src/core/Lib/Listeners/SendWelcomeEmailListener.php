<?php
namespace Core\Lib\Listeners;

use Core\Lib\Events\UserRegistered;
use Core\Lib\Events\Contracts\ShouldQueue;
use Core\Lib\Events\Contracts\QueuePreferences;
use Core\Services\UserService;

class SendWelcomeEmailListener implements ShouldQueue, QueuePreferences {
    public function handle(UserRegistered $event) : void {
        UserService::queueWelcomeMailer((int)$event->user->id);
    }

    public function viaQueue(): ?string { return 'mail'; }
    public function delay(): int { return 60; }               // 1 min
    public function backoff(): int|array { return [10, 30, 60]; }
    public function maxAttempts(): int { return 5; }
}