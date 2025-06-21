<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

class WelcomeMailer {
    public static function send(Users $user) {
        $mail = new MailerService();
        $layoutPath = CHAPPY_BASE_PATH.DS.'vendor'.DS.'chappy-php'.DS.'chappy-php-framework'.DS.'src'.DS.'views'.DS.'emails'.DS.'welcome.php';
        // dd($layoutPath);
        $templatePath = CHAPPY_BASE_PATH.DS.'vendor'.DS.'chappy-php'.DS.'chappy-php-framework'.DS.'src'.DS.'views'.DS.'emails'.DS.'layouts'.DS.'default.php';
        $subject = 'Welcome to ' . env('APP_NAME');

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