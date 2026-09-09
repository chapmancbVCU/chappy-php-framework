<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

/**
 * Contract the auth guard/provider relies on to authenticate a model
 * without knowing its underlying schema.
 */
interface Principal {
    public function getAuthIdentifierName(): string;
    public function getAuthIdentifier();
    public function getAuthPasswordName(): ?string;
    public function getAuthPassword(): ?string;
    public function getRememberToken(): ?string;
    public function setRememberToken(string $value): void;
    public function getRememberTokenName(): ?string;
}
