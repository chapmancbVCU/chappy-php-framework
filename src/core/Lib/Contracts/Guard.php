<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

interface Guard {
    public function check(): bool;
    public function user(): ?Principal;
    public function id();
    public function validate(array $credentials): bool;
    public function login(Principal $user, bool $remember = false): void;
    public function loginUsingId($id): ?Principal;
    public function logout(): void;
}