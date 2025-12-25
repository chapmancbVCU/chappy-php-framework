<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use App\Models\Users;

/**
 * Describes specification for all mailers.
 */
abstract class AbstractMailer {
    protected string $layout = 'default';
    protected MailerService $mailer;
    protected string $style = 'default';
    protected Users $user;

    /**
     * Constructor for AbstractMailer
     */
    public function __construct(Users $user) {
        $this->mailer = new MailerService();
        $this->user = $user;
    }

    /**
     * Common send logic shared by all mailers.
     *
     * @param string $layout The layout if it exists.
     * @param array $attachments An array containing information about 
     * attachments.
     * @param string|null $layoutPath The path to the layout.
     * @param string $templatePath The path to the template.
     * @param string|null $styles Name of stylesheet file.
     * @param string|null $stylesPath The path to the stylesheet.
     * @return bool True if successful.  Otherwise, we return false.
     */
    public function buildAndSend(
        ?string $layout = null,
        array $attachments = [],
        ?string $layoutPath = null,
        ?string $templatePath = null,
        ?string $styles = null,
        ?string $stylesPath = null

    ): bool {
        return $this->mailer->sendTemplate(
            $this->user->email,
            $this->getSubject(),
            $this->getTemplate(),
            $this->getData(),
            $layout ?? $this->layout,
            $attachments,
            $layoutPath ?? MailerService::FRAMEWORK_LAYOUT_PATH,
            $templatePath ?? MailerService::FRAMEWORK_TEMPLATE_PATH ,
            $styles ?? $this->style,
            $stylesPath ?? MailerService::FRAMEWORK_STYLES_PATH
        );
    }

    /**
     * Used to retrieve data to be used in E-mail.
     *
     * @return array Data to be used in E-mail.
     */
    abstract protected function getData(): array;

    /**
     * Returns subject for E-mail.
     *
     * @return string The subject of the E-mail.
     */
    abstract protected function getSubject(): string;

    /**
     * Returns the template to be used.
     *
     * @return string The name of the template to be used.
     */
    abstract protected function getTemplate(): string;

    /**
     * Send the mail
     *
     * @return bool
     */
    public function send(): bool {
        return $this->buildAndSend();
    }

    /**
     * Statically sends E-mail
     *
     * @param Users $user The recipient
     * @return boolean
     */
    public static function sendTo(Users $user): bool {
        return (new static($user))->send();
    }
}