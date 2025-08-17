<?php
namespace Core\Lib\Notifications;

use App\Models\Users;
use Core\Lib\Mail\Attachments;
use Core\Services\AuthService;
use Core\Lib\Mail\MailerService;
use Core\Lib\Mail\WelcomeMailer;
use Core\Lib\Utilities\DateTime;
use Core\Models\EmailAttachments;
use Core\Lib\Notifications\Notification;

class UserRegistered extends Notification
{
    protected $user;

    public function __construct(Users $user)
    {
        $this->user = $user;
    }

    /**
     * Data stored in the notifications table.
     * 
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return array array<string,mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'user_id'   => $this->user->id,
            'username'  => $this->user->username ?? $this->user->email,
            'message'   => "A new user has registered: {$this->user->username}",
            'registered_at' => DateTime::timeStamps()
        ];
    }

    /**
     * Logs notification to log file.
     *
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return string Contents for the log.
     */
    public function toLog(object $notifiable): string {
        return "A new user has registered: {$this->user->username}";
    }

    /**
     * Handles notification via E-mail.
     *
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return array array<string,mixed>
     */
    public function toMail(object $notifiable): array {
        // Raw 
        // return [
        //     'subject' => 'New user sign up',
        //     'html'    => "<p>New user has registered: {$this->user->username}</p>",
        // ];

        // Raw + text
        return [
            'subject' => 'New user sign up',
            'html' => "<p>New user has registered: {$this->user->username}</p>",
            'text' => "New user has registered {$this->user->username}"
        ];

        // Mailer class
        // return [ 'mailer' => NewUserRegisteredMailer::class];

        // Attachment with template
        // $attachment = EmailAttachments::findById(2);
        // return [
        //     'subject' => 'New user registered',
        //     'template' => 'new_user_registered',
        //     'data' => ['user' => $this->user],
        //     'layout' => 'default',
        //     'styles' => 'default',
        //     'attachments' => Attachments::content($attachment),
        //     'layoutPath' => MailerService::FRAMEWORK_LAYOUT_PATH,
        //     'templatePath' => MailerService::FRAMEWORK_TEMPLATE_PATH,
        //     'stylesPath' => MailerService::FRAMEWORK_STYLES_PATH
        // ];
    }

    /**
     * Specify which channels to deliver to.
     * 
     * @param object $notifiable Any model/object that uses the Notifiable trait.
     * @return array array<string,mixed>
     */
    public function via(object $notifiable): array
    {
        return Notification::channelValues();
    }
}
