<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

class WelcomeMailer {
    public static function send(Users $user) {
        $mail = new MailerService();
        // dd(CHAPPY_ROOT);
        $templatePath = CHAPPY_ROOT.DS.'views'.DS.'emails'.DS;
        $layoutPath = CHAPPY_ROOT.DS.'views'.DS.'emails'.DS.'layouts'.DS;

        $subject = 'Welcome to ' . env('SITE_TITLE');

        return $mail->sendTemplate(
            $user->email,
            $subject,
            'welcome',
            ['user' => $user],
            'default',
            [],
            $layoutPath,
            $templatePath
        );
    }
}