<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

/**
 * Defines the contract for an authentication guard.  A guard owns the
 * notion of "who is authenticated right now" for a single request,
 * resolving the current user from the underlying session and delegating
 * user retrieval and credential checks to a UserProvider.  Implementations
 * are responsible for reading and writing authentication state (session,
 * and optionally remember-me persistence) so that callers interact with a
 * Principal rather than with storage details directly.
 */
interface Guard {
    /**
     * Determines whether a user is currently authenticated.
     *
     * @return bool True if a user is resolved for the current request,
     * otherwise false.
     */
    public function check(): bool;

    /**
     * Retrieves the currently authenticated user.  Implementations
     * typically resolve the user once per request and cache the result.
     *
     * @return Principal|null The authenticated user, or null if no user
     * is authenticated.
     */
    public function user(): ?Principal;

    /**
     * Retrieves the identifier of the currently authenticated user without
     * requiring the full user object to be used by the caller.
     *
     * @return mixed The authenticated user's identifier, or null if no
     * user is authenticated.
     */
    public function id();

    /**
     * Validates a set of credentials without logging the user in or
     * modifying any authentication state.
     *
     * @param array $credentials The credentials to validate (e.g.
     * ['username' => ..., 'password' => ...]).
     * @return bool True if the credentials are valid, otherwise false.
     */
    public function validate(array $credentials): bool;

    /**
     * Logs the given user in, establishing authentication state for the
     * current request and subsequent ones.  When $remember is true,
     * implementations should also persist a remember-me token.
     *
     * @param Principal $user The user to authenticate.
     * @param bool $remember Whether to persist a remember-me token so the
     * user remains logged in across sessions.  Defaults to false.
     * @return void
     */
    public function login(Principal $user, bool $remember = false): void;

    /**
     * Resolves a user by identifier and logs them in.
     *
     * @param mixed $id The identifier of the user to authenticate.
     * @return Principal|null The authenticated user, or null if no user
     * matches the given identifier.
     */
    public function loginUsingId($id): ?Principal;

    /**
     * Logs the current user out, clearing authentication state for the
     * request and removing any persisted remember-me token.
     *
     * @return void
     */
    public function logout(): void;
}