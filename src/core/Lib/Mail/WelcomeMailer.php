<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use App\Models\Users;

/**
 * Class for generating a welcome message.
 */
class WelcomeMailer extends AbstractMailer {
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

    /**
     * Generates and sends welcome message.
     *
     * @param Users $user The new user.
     * @return bool True if sent, otherwise false.
     */
    // public function send(): bool {
    //     $subject = 'Welcome to ' . env('SITE_TITLE');
    //     return $this->buildAndSend(
    //         $this->user->email,
    //         $subject,
    //         'welcome',
    //         ['user' => $this->user]
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