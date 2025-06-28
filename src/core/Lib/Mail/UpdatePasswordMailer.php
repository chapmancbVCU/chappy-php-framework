<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use App\Models\Users;

/**
 * Class for generating a message informing the user that their 
 * password has been updated.
 */
class UpdatePasswordMailer extends AbstractMailer {
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

    /**
     * Generates and sends E-mail informing the user that their 
     * password has been updated.
     *
     * @param Users $user The new user.
     * @return bool True if sent, otherwise false.
     */
    // public function send(): bool {
    //     $subject = 'The password update notification for ' . $this->user->username;

    //     return $this->buildAndSend(
    //         $this->user->email,
    //         $subject,
    //         'update_password',
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