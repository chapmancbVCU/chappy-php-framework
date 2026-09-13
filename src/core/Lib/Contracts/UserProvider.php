<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

/**
 * Defines the contract for retrieving and validating principals from an
 * underlying data store.  A UserProvider isolates all knowledge of how
 * users are stored and looked up, so a guard can authenticate against any
 * backing store (a model, an external service, etc.) through a single
 * interface.  Retrieval and credential validation are kept separate: a
 * provider can locate a principal by its credentials without verifying
 * them, and verify a principal's credentials without re-fetching it.
 */
interface UserProvider {
    /**
     * Retrieves a principal by its unique identifier.
     *
     * @param mixed $id The identifier of the principal to retrieve.
     * @return Principal|null The matching principal, or null if none
     * exists for the given identifier.
     */
    public function retrieveById($id): ?Principal;

    /**
     * Retrieves a principal matching the given credentials, without
     * verifying them.  Implementations should look the principal up by
     * its identifying credential(s) only (e.g. username) and must not
     * treat the password as part of the lookup; verification is the
     * responsibility of validateCredentials().
     *
     * @param array $credentials The credentials used to locate the
     * principal (e.g. ['username' => ...]).
     * @return Principal|null The matching principal, or null if none is
     * found.
     */
    public function retrieveByCredentials(array $credentials): ?Principal;

    /**
     * Verifies that the given credentials are valid for the given
     * principal, typically by checking the supplied password against the
     * principal's stored hash.
     *
     * @param Principal $user The principal whose credentials are being
     * verified.
     * @param array $credentials The credentials to verify (e.g.
     * ['password' => ...]).
     * @return bool True if the credentials are valid for the principal,
     * otherwise false.
     */
    public function validateCredentials(Principal $user, array $credentials): bool;
}