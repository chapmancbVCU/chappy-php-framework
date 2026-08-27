<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Hasher;
use Core\Lib\Contracts\Principal;
use Core\Lib\Contracts\UserProvider;
use App\Models\Users;

final class ModelUserProvider implements UserProvider {
    private Hasher $hasher;

    public function construct__(Hasher $hasher) {
        $this->hasher = $hasher;
    }


    public function retrieveById($id): ?Principal {
        return Users::findById((int)$id) ?: null;
    }

    public function retrieveByCredentials(array $credentials): ?Principal {
        if(empty($credentials['username'])) return null;
        return Users::findByUsername($credentials['username']) ?: null;
    }

    public function validateCredentials(Principal $user, array $credentials): bool {
        $plain = $credentials['password'] ?? '';
        if($plain === '') return false;
        return $this->hasher->verify($plain, (string)$user->getAuthPassword());
    }
}