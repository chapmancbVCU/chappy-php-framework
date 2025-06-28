<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use App\Models\Users;

/**
 * Class for generating a reset password E-mail.
 */
class PasswordResetMailer extends AbstractMailer {
    protected Users $user;

    public function __construct(Users $user) {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Generates and sends reset password E-mail.
     *
     * @param Users $user The user whose password needs to be reset.
     * @return bool True if sent, otherwise false.
     */
    public function send(): bool {
        $subject = $this->user->username . ', please reset your password';

        return $this->buildAndSend(
            $this->user->email,
            $subject,
            'reset_password',
            ['user' => $this->user],
        );
    }

    public static function sendTo(Users $user): bool {
        return (new static($user))->send();
    }
}