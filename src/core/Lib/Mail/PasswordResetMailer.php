<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

/**
 * Class for generating a reset password E-mail.
 */
class PasswordResetMailer extends AbstractMailer {
    /**
     * Overrides getData from parent.
     *
     * @return array Data to be used by E-mail.
     */
    protected function getData(): array {
        return ['user' => $this->user];
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
}