<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Guard;
use Core\Lib\Contracts\Principal;
use Core\Lib\Contracts\UserProvider;
use Core\Session;
use Override;

final class SessionGuard implements Guard {
    private UserProvider $provider;
    private string $sessionKey;
    private ?Principal $user = null;
    private bool $resolved = false;
    private ?RememberMeStore $remember;

    public function __construct(
        UserProvider $provider, 
        ?string $sessionKey = null,
        ?RememberMeStore $remember = null
    ) {
        $this->provider = $provider;
        $this->sessionKey = $sessionKey ?? env('CURRENT_USER_SESSION_NAME');
        $this->remember = $remember ?? new RememberMeStore();
    }

    public function check(): bool {
        return $this->user() !== null;
    }

    public function id() {
        $user = $this->user();
        return $user ? $user->getAuthIdentifier() : null;
    }

    public function login(Principal $user, bool $remember = false): void {
        Session::set($this->sessionKey, $user->getAuthIdentifier());
        $this->user = $user;
        $this->resolved = true;
        if($remember) $this->remember->persist($user);
    }

    public function loginUsingId($id): ?Principal{
        $user = $this->provider->retrieveById($id);
        if($user !== null) $this->login($user);
        return $user;    
    }

    public function logout(): void {
        $this->remember->forget();
        Session::delete($this->sessionKey);
        $this->user = null;
        $this->resolved = true;
    }

    public function user(): ?Principal {
        if($this->resolved) return $this->user;
        $this->resolved = true;
        
        if(Session::exists($this->sessionKey)) {
            $this->user = $this->provider->retrieveById(Session::get($this->sessionKey));
        }
        return $this->user;
    }

    public function validate(array $credentials): bool {
        $user = $this->provider->retrieveByCredentials($credentials);
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }
}