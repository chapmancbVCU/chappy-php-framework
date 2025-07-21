<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use App\Models\Users;

/**
 * Simple DTO (Data Transfer Object) class for password reset event.
 */
class UserRegistered {
    public Users $user;
    public bool $shouldSendEmail;
    /**
     * Constructor
     *
     * @param User $user User associated with event.
     * @param bool $shouldSendEmail Flag to determine if E-mail should be sent.
     */
    public function __construct(Users $user, bool $shouldSendEmail) {
        $this->user = $user;
        $this->shouldSendEmail = $shouldSendEmail;
    }
}