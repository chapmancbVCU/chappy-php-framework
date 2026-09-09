<?php
declare(strict_types=1);
namespace Core\Traits;

/**
 * Default implementation of the IsPrincipal contract, reading values
 * off public model properties by their configured column names.
 *
 * To point a model at differently-named columns, OVERRIDE THE GETTER
 * METHOD (not the property — see note below), e.g. getRememberTokenName().
 */
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
        $name = $this->getRememberTokenName();
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