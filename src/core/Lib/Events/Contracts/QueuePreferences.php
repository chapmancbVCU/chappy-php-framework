<?php
namespace Core\Lib\Events\Contracts;

interface QueuePreferences {
    public function viaQueue(): ?string;      // e.g. 'mail'
    public function delay(): int;             // seconds or unix ts
    public function backoff(): int|array;     // e.g. [10,30,60]
    public function maxAttempts(): int;       // per-listener override
}