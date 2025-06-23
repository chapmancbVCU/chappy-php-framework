<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

/**
 * Class for generating a welcome message.
 */
class WelcomeMailer {
    /**
     * Generates and sends welcome message.
     *
     * @param Users $user The new user.
     * @return void
     */
    public static function send(Users $user) {
        $mail = new MailerService();
        $subject = 'Welcome to ' . env('SITE_TITLE');

        return $mail->sendTemplate(
            'chad.chapman2010+welcome_test@gmail.com',
            $subject,
            'welcome',
            ['user' => $user],
            'default',
            [],
            MailerService::FRAMEWORK_LAYOUT_PATH,
            MailerService::FRAMEWORK_TEMPLATE_PATH,
            'default',
            MailerService::FRAMEWORK_STYLES_PATH
        );
    }
}