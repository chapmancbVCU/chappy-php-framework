<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

/**
 * Defines the contract for hashing and verifying values, typically user
 * passwords.  Implementations wrap a specific hashing algorithm (e.g.
 * bcrypt or Argon2) behind a common interface so that callers hash and
 * verify without depending on the underlying algorithm.  This keeps the
 * choice of algorithm and its parameters in a single place and allows it
 * to be changed or upgraded without altering calling code.
 */
interface Hasher {
    /**
     * Hashes the given plain-text value.
     *
     * @param string $value The plain-text value to hash.
     * @return string The resulting hash, suitable for storage.
     */
    public function make(string $value): string;

    /**
     * Determines whether a previously computed hash should be re-created,
     * for example because the algorithm's configured cost or parameters
     * have since changed.  Typically checked after a successful verify()
     * so an out-of-date hash can be transparently upgraded.
     *
     * @param string $hashed The existing hash to examine.
     * @return bool True if the value should be re-hashed with the current
     * settings, otherwise false.
     */
    public function needsRehash(string $hashed): bool;

    /**
     * Verifies that a plain-text value matches a previously computed hash.
     *
     * @param string $value The plain-text value to check.
     * @param string $hashed The hash to check against.
     * @return bool True if the value matches the hash, otherwise false.
     */
    public function verify(string $value, string $hashed): bool;
}