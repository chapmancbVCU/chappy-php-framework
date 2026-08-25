<?php
declare(strict_types=1);
namespace Core\Traits;

trait IsPrincipal {
    protected string $authIdentifierName = 'id';
    protected string $authPasswordName = 'password';
    protected ?string $rememberTokenName = 'remember_token';

    public function getAuthIdentifierName(): string {
        return $this->authIdentifierName;
    }

    public function getAuthIdentifier() {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPasswordName(): string {
        return $this->authPasswordName;
    }

    public function getAuthPassword(): ?string {
        return $this->{$this->getAuthPasswordName()} ?? null;    
    }

    public function getRememberToken(): ?string {
        $name = $this->{$this->getAuthIdentifierName()};
        return !empty($name) ? ($this->{$name} ?? null) : null;
    }

    public function setRememberToken(string $value): void {
        $name = $this->getRememberTokenName();
        if(!empty($name)) $this->{$name} = $value;
    }

    public function getRememberTokenName(): ?string {
        return $this->rememberTokenName;
    }
}