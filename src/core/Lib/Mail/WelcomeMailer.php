<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

class WelcomeMailer {
    public static function send(Users $user) {
        $mail = new MailerService();
        $templatePath = CHAPPY_BASE_PATH.DS.'vendor'.DS.'chappy-php'.DS.'chappy-php-framework'.DS.'src'.DS.'views'.DS.'emails'.DS;
        $layoutPath = CHAPPY_BASE_PATH.DS.'vendor'.DS.'chappy-php'.DS.'chappy-php-framework'.DS.'src'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
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