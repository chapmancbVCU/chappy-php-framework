<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

/**
 * Class for generating a message informing the user that their 
 * password has been updated.
 */
class UpdatePasswordMailer extends AbstractMailer {
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
        return 'The password update notification for ' . $this->user->username;
    }

    /**
     * Overrides getTemplate from parent.
     *
     * @return string The template to be used.
     */
    protected function getTemplate(): string {
        return 'update_password';
    }
}