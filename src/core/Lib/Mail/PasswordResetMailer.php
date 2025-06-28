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
     * Overrides getData from parent.
     *
     * @return array Data to be used by E-mail.
     */
    protected function getData(): array {
        return ['user' => $this->user];
    }

    /**
     * Overrides getRecipient from parent.
     *
     * @return string The E-mail's recipient.
     */
    protected function getRecipient(): string {
        return $this->user->email;
    }

    /**
     * Overrides getSubject from parent.
     *
     * @return string The E-mail's subject.
     */
    protected function getSubject(): string {
        return $this->user->username . ', please reset your password';
    }

    /**
     * Overrides getTemplate from parent.
     *
     * @return string The template to be used.
     */
    protected function getTemplate(): string {
        return 'reset_password';
    }

    /**
     * Generates and sends reset password E-mail.
     *
     * @param Users $user The user whose password needs to be reset.
     * @return bool True if sent, otherwise false.
     */
    // public function send(): bool {
    //     $subject = $this->user->username . ', please reset your password';

    //     return $this->buildAndSend(
    //         $this->user->email,
    //         $subject,
    //         'reset_password',
    //         ['user' => $this->user],
    //     );
    // }

    /**
     * Statically sends E-mail
     *
     * @param Users $user The recipient
     * @return boolean
     */
    public static function sendTo(Users $user): bool {
        return (new static($user))->send();
    }
}