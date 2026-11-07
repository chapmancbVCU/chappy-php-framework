<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Hasher;
use Core\Lib\Contracts\Principal;
use Core\Lib\Contracts\UserProvider;
use Core\Models\User;

/**
 * Retrieves and validates Principals backed by the Users model.  Isolates
 * all knowledge of how users are stored and looked up, so the guard can
 * authenticate against the Users model through the UserProvider contract
 * without depending on it directly.
 */
final class ModelUserProvider implements UserProvider {
    /**
     * The hasher used to verify supplied passwords against stored hashes.
     *
     * @var Hasher
     */
    private Hasher $hasher;

    /**
     * The user model class used for lookups.
     *
     * @var class-string<User>
     */
    private string $model;

    /**
     * @param Hasher $hasher The hasher used to verify credentials.
     * @param class-string<User> $model The user model class, usually from 
     * Auth::userModel().
     */
    public function __construct(Hasher $hasher, string $model) {
        $this->hasher = $hasher;
        $this->model = $model;
    }

    /**
     * Retrieves a principal by its unique identifier.
     *
     * @param mixed $id The identifier of the principal to retrieve.
     * @return Principal|null The matching user, or null if none exists.
     */
    public function retrieveById($id): ?Principal {
        return $this->model::findById((int)$id) ?: null;
    }

    /**
     * Retrieves a principal matching the given credentials, without
     * verifying them.  Looks the user up by username only; password
     * verification is performed separately by validateCredentials().
     *
     * @param array $credentials The credentials used to locate the user
     * (expects a 'username' key).
     * @return Principal|null The matching user, or null if the username is
     * absent or no user is found.
     */
    public function retrieveByCredentials(array $credentials): ?Principal {
        if(empty($credentials['username'])) return null;
        return $this->model::findByUserName($credentials['username']) ?: null;
    }

    /**
     * Verifies that the supplied password matches the principal's stored
     * hash.
     *
     * @param Principal $user The user whose credentials are being verified.
     * @param array $credentials The credentials to verify (expects a
     * 'password' key).
     * @return bool True if the password is valid for the user, otherwise
     * false.  Returns false if no password is supplied.
     */
    public function validateCredentials(Principal $user, array $credentials): bool {
        $plain = $credentials['password'] ?? '';
        if($plain === '') return false;
        return $this->hasher->verify($plain, (string)$user->getAuthPassword());
    }
}