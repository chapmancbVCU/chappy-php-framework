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
     * Generates and sends welcome message.
     *
     * @param Users $user The new user.
     * @return bool True if sent, otherwise false.
     */
    public function send(): bool {
        $subject = 'Welcome to ' . env('SITE_TITLE');
        return $this->buildAndSend(
            $this->user->email,
            $subject,
            'welcome',
            ['user' => $this->user]
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