<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

/**
 * Class for generating a welcome message.
 */
class WelcomeMailer extends AbstractMailer {
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
        return 'Welcome to ' . env('SITE_TITLE');
    }

    /**
     * Overrides getTemplate from parent.
     *
     * @return string The template to be used.
     */
    protected function getTemplate(): string {
        return 'welcome';
    }
}