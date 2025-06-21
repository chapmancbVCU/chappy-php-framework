<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

class WelcomeMailer {
    public static function send(Users $user) {
        $mail = new MailerService();
        // dd(CHAPPY_ROOT);
        $templatePath = CHAPPY_ROOT . '/src/core/views/emails/';
$layoutPath = CHAPPY_ROOT . '/src/core/views/emails/layouts/';

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