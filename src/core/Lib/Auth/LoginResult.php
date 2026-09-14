<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Lib\Contracts\Principal;

final class LoginResult {
    /** Credentials were valid and the user was logged in. */
    public const SUCCESS = 'success';
    /** Credentials were valid but the account is flagged for password reset. */
    public const NEEDS_RESET = 'needs_reset';
    /** Credentials were valid but the account is inactive. */
    public const INACTIVE = 'inactive';
    /** Credentials were invalid and the account is now locked. */
    public const LOCKED = 'locked';
    /** Credentials were invalid (wrong password or unknown user). */
    public const INVALID = 'invalid';

    /**
     * @param string $status One of the status constants above.
     * @param Principal|null $user The user the attempt concerned, when known.
     */
    private function __construct(
        public readonly string $status,
        public readonly ?Principal $user = null
    ) {}

    /**
     * Returns new instance with status set to self::INACTIVE.
     *
     * @return self
     */
    public static function inactive(): self {
        return new self(self::INACTIVE);
    }

    /**
     * Returns new instance with status set to self::INVALID.
     * 
     * @return self
     */
    public static function invalid(): self {
        return new self(self::INVALID);
    }

    /**
     * Returns new instance with status set to self::LOCKED.
     * 
     * @return self
     */
    public static function locked(): self {
        return new self(self::LOCKED);
    }

    /**
     * Returns new instance with status set to self::NEEDS_RESET.
     * 
     * @param Principal $user The user whose account requires a password reset.
     * @return self
     */
    public static function needsReset(Principal $user): self {
        return new self(self::NEEDS_RESET, $user);
    }

    /**
     * Determines if attempt is successful.
     * 
     * @return bool True if the attempt succeeded.
     */
    public function succeeded(): bool {
        return $this->status === self::SUCCESS;
    }

    /**
     * Returns new instance with status set to self::SUCCESS.
     * 
     * @param Principal $user The successfully authenticated user.
     * @return self
     */
    public static function success(Principal $user): self {
        return new self(self::SUCCESS, $user);
    }
}