<?php
declare(strict_types=1);
namespace Core\Lib\Events;

use Core\Models\User;
use Core\Lib\Auth\Auth;

/**
 * Simple DTO (Data Transfer Object) class for queueing new account 
 * E-mail event.
 */
class UserRegistered {
    /**
     * User associated with event.
     * @var User
     */
    public User $user;

    /**
     * Flag to control if an email should be sent.
     * @var bool
     */
    public bool $shouldSendEmail;
    
    /**
     * Constructor
     *
     * @param User $user User associated with event.
     * @param bool $shouldSendEmail Flag to determine if E-mail should be 
     * sent.  Set default value to false.
     */
    public function __construct(User $user, bool $shouldSendEmail = false) {
        $this->user = $user;
        $this->shouldSendEmail = $shouldSendEmail;
    }

    /**
     * Adds instance variables to payload.
     *
     * @return array An associative array containing values of instance 
     * variables.
     */
    public function toPayload(): array {
        return [
            'user_id'         => (int)$this->user->id,
            'shouldSendEmail' => $this->shouldSendEmail,
        ];
    }

    /**
     * Retrieves information from payload array and returns new instance of 
     * this class.
     *
     * @param array $data The payload array.
     * @return self New instance of this class.
     */
    public static function fromPayload(array $data): self {
        $user = Auth::userModel()::findById((int)$data['user_id']);
        $should = (bool)($data['shouldSendEmail'] ?? false);
        return new self($user, $should);
    }
}