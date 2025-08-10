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
     * @param bool $shouldSendEmail Flag to determine if E-mail should be 
     * sent.  Set default value to false.
     */
    public function __construct(Users $user, bool $shouldSendEmail = false) {
        $this->user = $user;
        $this->shouldSendEmail = $shouldSendEmail;
    }

    public function toPayload(): array {
        return [
            'user_id'         => (int)$this->user->id,
            'shouldSendEmail' => $this->shouldSendEmail,
        ];
    }

    public static function fromPayload(array $data): self {
        $user = Users::findById((int)$data['user_id']);
        $should = (bool)($data['shouldSendEmail'] ?? false);
        return new self($user, $should);
    }
}