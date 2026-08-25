<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

interface UserProvider {
    public function retrieveById($id): ?Principal;
    public function retrieveByCredentials(array $credentials): ?Principal;
    public function validateCredentials(Principal $user, array $credentials): bool;
}