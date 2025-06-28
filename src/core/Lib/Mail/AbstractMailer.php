<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

abstract class AbstractMailer {
    protected string $layout = 'default';
    protected MailerService $mailer;
    protected string $style = 'default';

    /**
     * Constructor for AbstractMailer
     */
    public function __construct() {
        $this->mailer = new MailerService();
    }

    /**
     * Common send logic shared by all mailers.
     *
     * @param string $to The recipient.
     * @param string $subject The E-mail's subject.
     * @param string $template The content if it exists.
     * @param array $data Any data that the template uses.
     * @return boolean
     */
    protected function buildAndSend(
        string $to,
        string $subject,
        string $template,
        array $data = []
    ): bool {
        return $this->mailer->sendTemplate(
            $to,
            $subject,
            $template,
            $data,
            $this->layout,
            [],
            MailerService::FRAMEWORK_LAYOUT_PATH,
            MailerService::FRAMEWORK_TEMPLATE_PATH,
            $this->style,
            MailerService::FRAMEWORK_STYLES_PATH
        );
    }

    /**
     * Send the mail
     *
     * @return bool
     */
    abstract public function send(): bool;
}