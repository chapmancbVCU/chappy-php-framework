<?php
declare(strict_types=1);
namespace Core\Lib\Hashing;

use Core\Lib\Contracts\Hasher;
use RuntimeException;

/**
 * Bcrypt implementation of the Hasher contract.
 */
final class BcryptHasher implements Hasher {
    private int $cost;

    /**
     * @param int $cost The bcrypt work factor (4–31). Higher is slower/safer.
     */
    public function __construct(int $cost = 12) {
        $this->cost = $cost;
    }

    /**
     * @param string $value Plain-text value to hash.
     * @return string The bcrypt hash.
     */
    public function make(string $value): string {
        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $this->cost]);
        if($hash === false) {
            throw new RuntimeException("Bcrypt hashing failed.");
        }
        return $hash;
    }

    /**
     * @param string $hashed Stored hash to test.
     * @return bool True if the hash should be re-created (e.g. cost changed).
     */
    public function needsRehash(string $hashed): bool {
        return password_needs_rehash($hashed, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    /**
     * @param string $value Plain-text value to check.
     * @param string $hashed Stored hash to check against.
     * @return bool True on match.
     */
    public function verify(string $value, string $hashed): bool {
        if($hashed === '') return false;
        return password_verify($value, $hashed);
    }
}