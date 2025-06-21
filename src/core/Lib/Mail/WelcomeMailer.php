<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

class WelcomeMailer {
    public static function send(User $user) {
        $mail = new MailerService();
        $layoutPath = CHAPPY_ROOT.DS.'views'.DS.'emails'.DS.'welcome.php';
        $templatePath = CHAPPY_ROOT.DS .'views'.DS.'emails'.DS.'layouts'.DS.'default.php';
        $subject = 'Welcome to ' . env('APP_NAME');
        $mail->sendTemplate(
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