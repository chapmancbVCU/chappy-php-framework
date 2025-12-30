<?php
declare(strict_types=1);
namespace Core\Lib\Events;

/**
 * Simple DTO (Data Transfer Object) class for password reset event.
 */
class AccountDeactivated
{
    /**
     * User associated with event.
     * @var User
     */
    public $user;

    /**
     * Constructor
     *
     * @param User $user User associated with event.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
