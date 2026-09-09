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

    /**
     * @return string The name of the primary identifier column.
     */
    public function getAuthIdentifierName(): string {
        return $this->authIdentifierName;
    }

    /**
     * @return mixed The value of the primary identifier (e.g. id).
     */
    public function getAuthIdentifier() {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * @return string The name of the password column.
     */
    public function getAuthPasswordName(): string {
        return $this->authPasswordName;
    }

    /**
     * @return string|null The hashed password for this user.
     */
    public function getAuthPassword(): ?string {
        return $this->{$this->getAuthPasswordName()} ?? null;    
    }

    /**
     * @return string|null The current remember-me token, if any.
     */
    public function getRememberToken(): ?string {
        $name = $this->getRememberTokenName();
        return !empty($name) ? ($this->{$name} ?? null) : null;
    }

    /**
     * @param string $value The token value to persist.
     * @return void
     */
    public function setRememberToken(string $value): void {
        $name = $this->getRememberTokenName();
        if(!empty($name)) $this->{$name} = $value;
    }

    /**
     * @return string|null The remember-me column name, or null to
     * disable the feature for this model.
     */
    public function getRememberTokenName(): ?string {
        return $this->rememberTokenName;
    }
}