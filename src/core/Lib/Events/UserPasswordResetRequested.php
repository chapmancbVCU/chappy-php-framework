<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use App\Models\Users;

/**
 * Simple DTO (Data Transfer Object) class for password reset event.
 */
class UserPasswordResetRequested {
    /**
     * User associated with event.
     * @var Users
     */
    public Users $user;

    /**
     * Constructor
     *
     * @param User $user User associated with event.
     */
    public function __construct(Users $user) {
        $this->user = $user;
    }
}