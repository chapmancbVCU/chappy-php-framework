<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use App\Models\Users;

/**
 * Class for generating a message informing the user that their 
 * password has been updated.
 */
class AccountDeactivatedMailer extends AbstractMailer {
    protected Users $user;

    public function __construct(Users $user) {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Generates and sends E-mail informing the user that their 
     * password has been updated.
     *
     * @param Users $user The new user.
     * @return bool True if sent, otherwise false.
     */
    public function send(): bool {
        $subject = 'Notice: Your account has been deactivated';

        return $this->buildAndSend(
            $this->user->email,
            $subject,
            'deactivated_account',
            ['user' => $this->user],
        );
    }

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