<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Core\Lib\Mail\MailerService;
use App\Models\Users;

/**
 * Class for generating a message informing the user that their 
 * password has been updated.
 */
class UpdatePasswordMailer {
    /**
     * Generates and sends E-mail informing the user that their 
     * password has been updated.
     *
     * @param Users $user The new user.
     * @return bool True if sent, otherwise false.
     */
    public static function send(Users $user): bool {
        $mail = new MailerService();
        $subject = 'The password update notification for ' . $user->username;

        return $mail->sendTemplate(
            $user->email,
            $subject,
            'update_password',
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