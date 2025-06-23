<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

/**
 * Class for generating a message informing the user that their 
 * password has been updated.
 */
class AccountDeactivatedMailer {
    /**
     * Generates and sends E-mail informing the user that their 
     * password has been updated.
     *
     * @param Users $user The new user.
     * @return bool True if sent, otherwise false.
     */
    public static function send(Users $user): bool {
        $mail = new MailerService();
        $subject = 'Notice: Your account has been deactivated';

        return $mail->sendTemplate(
            'chad.chapman2010+welcome_test@gmail.com',
            $subject,
            'deactivated_account',
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