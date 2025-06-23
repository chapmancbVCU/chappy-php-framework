<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

/**
 * Class for generating a reset password E-mail.
 */
class PasswordResetMailer {
    /**
     * Generates and sends reset password E-mail.
     *
     * @param Users $user The user whose password needs to be reset.
     * @return bool True if sent, otherwise false.
     */
    public static function send(Users $user): bool {
        $mail = new MailerService();
        $subject = $user->username . ', please reset your password';

        return $mail->sendTemplate(
            $user->email,
            $subject,
            'reset_password',
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