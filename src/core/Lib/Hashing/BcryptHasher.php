<?php
declare(strict_types=1);
namespace Core\Lib\Hashing;

use Core\Lib\Contracts\Hasher;
use RuntimeException;

final class BcryptHasher implements Hasher {
    private int $cost;

    public function __construct(int $cost = 12) {
        $this->cost = $cost;
    }

    public function make(string $value): string {
        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $this->cost]);
        if($hash === false) {
            throw new RuntimeException("Bcrypt hashing failed.");
        }
        return $hash;
    }

    public function needsRehash(string $hashed): bool {
        return password_needs_rehash($hashed, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    public function verify(string $value, string $hashed): bool {
        if($hashed === '') return false;
        return password_verify($value, $hashed);
    }
}