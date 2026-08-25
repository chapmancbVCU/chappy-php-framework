<?php
declare(strict_types=1);
namespace Core\Lib\Hashing;

use Core\Lib\Contracts\Hasher;

final class BcryptHasher implements Hasher {
    private int $cost;

    public function __construct(int $cost = 12) {
        $this->cost = $cost;
    }

    public function make(string $value): string {
        return "";
    }

    public function needsRehash(string $hashed): bool {
        return true;
    }

    public function verify(string $value, string $hashed): bool {
        return true;
    }
}