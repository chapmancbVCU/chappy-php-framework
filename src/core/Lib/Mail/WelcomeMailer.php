<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

class WelcomeMailer {
    public static function send(Users $user) {
        $mail = new MailerService();
        $subject = 'Welcome to ' . env('SITE_TITLE');

        return $mail->sendTemplate(
            'chad.chapman2010+welcome_test@gmail.com',
            $subject,
            'welcome',
            ['user' => $user],
            'account_status',
            [],
            MailerService::FRAMEWORK_LAYOUT_PATH,
            MailerService::FRAMEWORK_TEMPLATE_PATH,
            'account_status',
            MailerService::FRAMEWORK_STYLES_PATH
        );
    }
}