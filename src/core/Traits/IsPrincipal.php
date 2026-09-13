<?php
declare(strict_types=1);
namespace Core\Traits;

/**
 * Default implementation of the IsPrincipal contract, reading values
 * off public model properties by their configured column names.  A model
 * satisfies the contract by using this trait and declaring properties
 * matching the configured column names; the identifier, password, and
 * remember-me token are then resolved dynamically from those properties.
 *
 * To point a model at differently-named columns, OVERRIDE THE GETTER
 * METHOD (not the property — see note below), e.g. getRememberTokenName().
 *
 * NOTE: The name properties below cannot be safely overridden by
 * redeclaration in a using class — PHP requires a redeclared trait
 * property to keep an identical default, so a differing value is a fatal
 * error.  Override the corresponding getter instead.  Returning null from
 * getRememberTokenName() disables remember-me for the model (the token
 * methods become no-ops), which is how a model whose remember-me state
 * lives outside the table opts out.
 */
trait IsPrincipal {
    /**
     * The name of the column holding the principal's unique identifier.
     * Override getAuthIdentifierName() to change this per model.
     *
     * @var string
     */
    protected string $authIdentifierName = 'id';

    /**
     * The name of the column holding the principal's hashed password.
     * Override getAuthPasswordName() to change this per model.
     *
     * @var string
     */
    protected string $authPasswordName = 'password';

    /**
     * The name of the column holding the remember-me token, or null to
     * disable remember-me for the model.  Override getRememberTokenName()
     * to change this per model.
     *
     * @var string|null
     */
    protected ?string $rememberTokenName = 'remember_token';

    /**
     * Gets the value of the primary identifier.
     * 
     * @return mixed The value of the primary identifier (e.g. id).
     */
    public function getAuthIdentifier() {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Gets the name of the primary identifier column.
     * 
     * @return string The name of the primary identifier column.
     */
    public function getAuthIdentifierName(): string {
        return $this->authIdentifierName;
    }

    /**
     * Gets the hashed password for this user.
     * 
     * @return string|null The hashed password for this user.
     */
    public function getAuthPassword(): ?string {
        return $this->{$this->getAuthPasswordName()} ?? null;    
    }

    /**
     * Gets the name of the password column.
     * 
     * @return string The name of the password column.
     */
    public function getAuthPasswordName(): string {
        return $this->authPasswordName;
    }

    /**
     * Gets the current remember-me token.
     * 
     * @return string|null The current remember-me token, if any.
     */
    public function getRememberToken(): ?string {
        $name = $this->getRememberTokenName();
        return !empty($name) ? ($this->{$name} ?? null) : null;
    }

    /**
     * Gets the remember-me column name or null to disable the feature for 
     * this model.
     * 
     * @return string|null The remember-me column name, or null to
     * disable the feature for this model.
     */
    public function getRememberTokenName(): ?string {
        return $this->rememberTokenName;
    }

    /**
     * Sets token value to persist.
     * 
     * @param string $value The token value to persist.
     * @return void
     */
    public function setRememberToken(string $value): void {
        $name = $this->getRememberTokenName();
        if(!empty($name)) $this->{$name} = $value;
    }
}