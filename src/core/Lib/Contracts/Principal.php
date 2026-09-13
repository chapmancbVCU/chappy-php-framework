<?php
declare(strict_types=1);
namespace Core\Lib\Contracts;

/**
 * Contract the auth guard/provider relies on to authenticate a model
 * without knowing its underlying schema.  A Principal exposes its
 * identifier, its stored password, and its remember-me token through a
 * fixed set of accessors, along with the column names backing each, so
 * the authentication system can operate against any model that maps
 * these concepts to storage however it chooses.
 */
interface Principal {
    /**
     * Gets the name of the column that uniquely identifies the principal.
     *
     * @return string The identifier column name (e.g. 'id').
     */
    public function getAuthIdentifierName(): string;

    /**
     * Gets the principal's unique identifier value.
     *
     * @return mixed The value of the identifier column (e.g. the id).
     */
    public function getAuthIdentifier();

    /**
     * Gets the name of the column that stores the principal's password,
     * or null if the model has no password column.
     *
     * @return string|null The password column name, or null if none.
     */
    public function getAuthPasswordName(): ?string;

    /**
     * Gets the principal's stored (hashed) password.
     *
     * @return string|null The hashed password, or null if none is set.
     */
    public function getAuthPassword(): ?string;

    /**
     * Gets the principal's current remember-me token.
     *
     * @return string|null The remember-me token, or null if the model
     * does not support remember-me or no token is set.
     */
    public function getRememberToken(): ?string;

    /**
     * Sets the principal's remember-me token.  Implementations for models
     * that do not support remember-me may treat this as a no-op.
     *
     * @param string $value The token value to assign.
     * @return void
     */
    public function setRememberToken(string $value): void;

    /**
     * Gets the name of the column that stores the remember-me token, or
     * null if the model does not support remember-me.
     *
     * @return string|null The remember-me token column name, or null if
     * the model has no such column.
     */
    public function getRememberTokenName(): ?string;
}
