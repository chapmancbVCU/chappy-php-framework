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
     * @param string $layout The layout if it exists.
     * @param array $attachments An array containing information about 
     * attachments.
     * @param string|null $layoutPath The path to the layout.
     * @param string $templatePath The path to the template.
     * @param string|null $styles Name of stylesheet file.
     * @param string|null $stylesPath The path to the stylesheet.
     * @return boolean
     */
    protected function buildAndSend(
        string $to,
        string $subject,
        ?string $template = null,
        array $data = [],
        ?string $layout = null,
        array $attachments = [],
        ?string $layoutPath = null,
        ?string $templatePath = null,
        ?string $styles = null,
        ?string $stylesPath = null

    ): bool {
        return $this->mailer->sendTemplate(
            $to,
            $subject,
            $template,
            $data,
            $layout ?? $this->layout,
            $attachments,
            $layoutPath ?? MailerService::FRAMEWORK_LAYOUT_PATH,
            $templatePath ?? MailerService::FRAMEWORK_TEMPLATE_PATH ,
            $styles ?? $this->style,
            $stylesPath ?? MailerService::FRAMEWORK_STYLES_PATH
        );
    }

    /**
     * Send the mail
     *
     * @return bool
     */
    abstract public function send(): bool;
}