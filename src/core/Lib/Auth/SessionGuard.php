<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Guard;
use Core\Lib\Contracts\Principal;
use Core\Lib\Contracts\UserProvider;
use Core\Session;

/**
 * Session-backed guard. Owns "who is logged in right now" and delegates
 * all storage to a UserProvider.  Resolves the current user from the
 * session once per request and caches the result, writes the session on
 * login, and clears it on logout.  Remember-me persistence is delegated to
 * a RememberMeStore.
 */
final class SessionGuard implements Guard {
    /**
     * Retrieves and validates users on the guard's behalf.
     *
     * @var UserProvider
     */
    private UserProvider $provider;

    /**
     * The session key under which the authenticated user's identifier is
     * stored.
     *
     * @var string
     */
    private string $sessionKey;

    /**
     * The resolved user for the current request, or null if none is
     * authenticated or resolution has not yet occurred.
     *
     * @var Principal|null
     */
    private ?Principal $user = null;

    /**
     * Whether the current user has been resolved from the session yet.
     * Guards against repeated lookups (and re-resolving a null result).
     *
     * @var bool
     */
    private bool $resolved = false;

    /**
     * Handles persistence and teardown of remember-me tokens.
     *
     * @var RememberMeStore|null
     */
    private ?RememberMeStore $remember;

    /**
     * @param UserProvider $provider The provider used to retrieve and
     * validate users.
     * @param string|null $sessionKey The session key holding the user's
     * identifier.  Defaults to the CURRENT_USER_SESSION_NAME environment
     * value when null.
     * @param RememberMeStore|null $remember The remember-me store.  A
     * default store is constructed when null.
     */
    public function __construct(
        UserProvider $provider, 
        ?string $sessionKey = null,
        ?RememberMeStore $remember = null
    ) {
        $this->provider = $provider;
        $this->sessionKey = $sessionKey ?? env('CURRENT_USER_SESSION_NAME');
        $this->remember = $remember ?? new RememberMeStore();
    }

    /**
     * Determines whether a user is currently authenticated.
     *
     * @return bool True if a user is resolved for the current request,
     * otherwise false.
     */
    public function check(): bool {
        return $this->user() !== null;
    }

    /**
     * Gets the identifier of the currently authenticated user.
     *
     * @return mixed The authenticated user's identifier, or null if no
     * user is authenticated.
     */
    public function id() {
        $user = $this->user();
        return $user ? $user->getAuthIdentifier() : null;
    }

    /**
     * Logs the given user in, storing their identifier in the session and
     * caching them for the current request.  When $remember is true, a
     * remember-me token is also persisted.
     *
     * @param Principal $user The user to authenticate.
     * @param bool $remember Whether to persist a remember-me token.
     * Defaults to false.
     * @return void
     */
    public function login(Principal $user, bool $remember = false): void {
        Session::set($this->sessionKey, $user->getAuthIdentifier());
        $this->user = $user;
        $this->resolved = true;
        if($remember) $this->remember->persist($user);
    }

    /**
     * Resolves a user by identifier and logs them in.
     *
     * @param mixed $id The identifier of the user to authenticate.
     * @return Principal|null The authenticated user, or null if no user
     * matches the given identifier.
     */
    public function loginUsingId($id): ?Principal{
        $user = $this->provider->retrieveById($id);
        if($user !== null) $this->login($user);
        return $user;    
    }

    /**
     * Logs the current user out, clearing the session, the cached user,
     * and any persisted remember-me token.
     *
     * @return void
     */
    public function logout(): void {
        $this->remember->forget();
        Session::delete($this->sessionKey);
        $this->user = null;
        $this->resolved = true;
    }

    /**
     * Resolves the current user from the session once, then caches it.
     *
     * @return Principal|null The authenticated user, or null if none is
     * authenticated.
     */
    public function user(): ?Principal {
        if($this->resolved) return $this->user;
        $this->resolved = true;
        
        if(Session::exists($this->sessionKey)) {
            $this->user = $this->provider->retrieveById(Session::get($this->sessionKey));
        }
        return $this->user;
    }

    /**
     * Checks credentials WITHOUT logging anyone in.
     *
     * @param array $credentials The credentials to validate.
     * @return bool True if the credentials are valid, otherwise false.
     */
    public function validate(array $credentials): bool {
        $user = $this->provider->retrieveByCredentials($credentials);
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }
}