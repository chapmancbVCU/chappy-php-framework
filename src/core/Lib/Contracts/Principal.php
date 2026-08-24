<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

interface Principal {
    public function getAuthIdentifierName(): string;
    public function getAuthIdentifier();
    public function getAuthPasswordName(): ?string;
    public function getAuthPassword(): ?string;
    public function getRememberMeToken(): ?string;
    public function setRememberMeToken(string $value): void;
    public function getRememberMeTokenName(): ?string;
}
