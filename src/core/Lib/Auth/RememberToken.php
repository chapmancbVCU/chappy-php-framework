<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

final class RememberToken {
    public static function generate(): string {
        return bin2hex(random_bytes(32));
    }

    public static function hash(string $token): string {
        return hash('sha256', $token);
    }
}