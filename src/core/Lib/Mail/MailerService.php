<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Exception;
use Throwable;
use Core\Lib\Utilities\Arr;
use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Supports E-mail infrastructure for this framework.
 */
class MailerService {
    public const FRAMEWORK_LAYOUT_PATH = CHAPPY_ROOT.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    public const FRAMEWORK_TEMPLATE_PATH = CHAPPY_ROOT.DS.'views'.DS.'emails'.DS;
    public const FRAMEWORK_STYLES_PATH = CHAPPY_ROOT.DS.'views'.DS.'emails'.DS.'styles'.DS;
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected Mailer $mailer;
    protected static string $stylesPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'css'.DS;
    protected static string $templatePath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;
    
    /**
     * Creates a new mailer.
     */
    public function __construct() {
        $dsn = Env::get('MAILER_DSN');
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    /**
     * Supports ability to use alternative layout paths.
     *
     * @param string|null $layoutPath The path to the layout.
     * @return string If parameter is null then constant is used.  Otherwise, 
     * the value of the parameter is used.
     */
    protected static function layoutPath(?string $layoutPath = null): string {
        return $layoutPath ?? self::$layoutPath;
    }

    /**
     * Logs each attempt at sending an E-mail.
     *
     * @param string $status The status of the attempt to send E-mail.
     * @param string $to he recipient.
     * @param string $subject The E-mail's subject.
     * @param string $htmlBody The E-mail's content
     * @param string|null $textBody The E-mail's text content.
     * @param string|null $template The name of the template.
     * @param string|null $error Reported errors for a send attempt.
     * @return void
     */
    protected function mailLogger(
        string $status,
        string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        ?string $template = null,
        ?string $error = null,
        ?array $attachments = null
    ): void {
        // We want to maintain key order explicitly.
        $log = [];

        $log['MailerService_status'] = $status;
        $log['timestamp'] = date('Y-m-d H:i:s');
        $log['to'] = $to;
        $log['subject'] = $subject;
        $log['html_body'] = $htmlBody ?: '(empty)';

        if($textBody !== null) {
            $log['text_body'] = $textBody;
        }

        $log['template'] = $template;
        $log['transport'] = Env::get('MAILER_DSN');
        $log['mailer_class'] = static::class;

        if($error !== null) {
            $log['error'] = $error;
        }

        if(Arr::isNotEmpty($attachments)) {
            $log['attachments'] = array_map(function ($attachment) {
            return [
                'name' => $attachment['name'] ?? '(no name)',
                'mime' => $attachment['mime'] ?? '(unknown)',
                'source' => isset($attachment['path']) ? 'path: ' . $attachment['path']
                            : (isset($attachment['content']) ? 'inline content (' . strlen($attachment['content']) . ' bytes)' 
                            : '(missing)'),
            ];
            }, Arr::isAssoc($attachments) ? [$attachments] : $attachments);
        }

        Logger::log(json_encode($log, JSON_PRETTY_PRINT), $status === 'failed' ? 'error' : 'info');
    }

    /**
     * Renders template file.
     *
     * @param string $path Path to the template.
     * @param array $data Any data that needs to be passed to the view.
     * @return string The template's content.
     */
    protected function renderTemplateFile(string $path, array $data = []): string {
        extract($data);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    /**
     * Sends a HTML E-mail.
     *
     * @param string $to The recipient.
     * @param string $subject The E-mail's subject.
     * @param string $htmlBody The E-mail's content.
     * @param string|null $template The template if it exists.
     * @return bool True if sent, otherwise we return false.
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $template = null, array $attachments = []): bool {
        try {
            $email = (new Email())
                ->from(Env::get('MAIL_FROM_ADDRESS'))
                ->to($to)
                ->subject($subject)
                ->html($htmlBody);

            if(!Arr::isEmpty($attachments)) {
                $email = Attachments::processAttachments($attachments, $email);
            }

            $this->mailer->send($email);

            $this->mailLogger(
                'success',
                $to,
                $subject,
                $htmlBody,
                null,
                $template,
                null,
                $attachments
            );

            return true;
        } catch (Throwable $e) {
            $this->mailLogger(
                'failed',
                $to,
                $subject,
                $htmlBody,
                null,
                $template,
                $e->getMessage(),
                $attachments
            );

            return false;
        }
    }

    /**
     * Generates E-mail using a specified template, layout,
     * and/or css stylesheet.
     *
     * @param string $to The recipient.
     * @param string $subject The E-mail's subject.
     * @param string $template The template if it exists.
     * @param array $data Any data that the template uses.
     * @param string|null $layout The layout if it exists.
     * @param array $attachments An array containing information about 
     * attachments.
     * @param string|null $layoutPath The path to the layout.
     * @param string|null $templatePath The path to the template.
     * @param string|null $styles Name of stylesheet file.
     * @param string|null $stylesPath The path to the stylesheet.
     * @return bool True if sent, otherwise we return false.
     */
    public function sendTemplate(
        string $to, 
        string $subject, 
        string $template, 
        array $data, 
        ?string $layout = null, 
        array $attachments = [], 
        ?string $layoutPath = null,
        ?string $templatePath = null,
        ?string $styles = null,
        ?string $stylesPath = null
    ): bool {
        $templatePath = self::templatePath($templatePath);
        $stylesPath = $stylesPath ?? self::$stylesPath;

        $html = $this->template($template, $data, $layout, self::layoutPath($layoutPath), $templatePath, $styles, $stylesPath);

        $textPath = $templatePath . $template . '.txt';
        if(file_exists($textPath)) {
            $text = $this->renderTemplateFile($textPath, $data);
            return $this->sendWithText($to, $subject, $html, $text, $template, $attachments);
        }
        return $this->send($to, $subject, $html, $template, $attachments);
    }

    /**
     * Sends a text E-mail.
     *
     * @param string $to The recipient.
     * @param string $subject The E-mail's subject.
     * @param string $htmlBody The E-mail's HTML content.
     * @param string $textBody The E-mail's text content.
     * @param string|null $template The template if it exists.
     * @return bool True if sent, otherwise we return false.
     */
    public function sendWithText(string $to, string $subject, string $htmlBody, string $textBody, ?string $template = null, array $attachments = []): bool {
        try {
            $email = (new Email())
                ->from(Env::get('MAIL_FROM_ADDRESS'))
                ->to($to)
                ->subject($subject)
                ->text($textBody)
                ->html($htmlBody);

            if(!Arr::isEmpty($attachments)) {
                $email = Attachments::processAttachments($attachments, $email);
            }

            $this->mailer->send($email);

            $this->mailLogger(
                'success',
                $to,
                $subject,
                $htmlBody,
                $textBody,
                $template,
                null,
                $attachments
            );

            return true;
        } catch (Throwable $e) {
            $this->mailLogger(
                'failed',
                $to,
                $subject,
                $htmlBody,
                $textBody,
                $template,
                $e->getMessage(),
                $attachments
            );

            return false;
        }
    }
    
    /**
     * Prepares E-mail content based on template to be sent.
     *
     * @param string $view The name of the template.
     * @param array $data Any data that the template uses.
     * @param string|null $layout The layout if it exists.
     * @param string|null $layoutPath The path to the layout.
     * @param string $templatePath The path to the template.
     * @return string The E-mail's contents.
     */
    protected function template(
        string $view, 
        array $data = [], 
        ?string $layout = null, 
        ?string $layoutPath = null,
        string $templatePath,
        ?string $styles = null,
        ?string $stylesPath = null
    ): string {
        $viewPath = $templatePath . $view . '.php';
        if(!file_exists($viewPath)) {
            throw new Exception("Email view $view not found");
        }

        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if($layout) {
            $layoutPath = $layoutPath . $layout . '.php';
            if(file_exists($layoutPath)) {
                ob_start();
                include $layoutPath;
                $content = ob_get_clean();
            }
        }

        $stylePath = $stylesPath . $styles . '.css';
        $style = file_exists($stylePath) ? file_get_contents($stylePath) : '';
        $inliner = new CssToInlineStyles();

        return $inliner->convert($content, $style);
    }

    /**
     * Supports ability to use alternative template paths.
     *
     * @param string|null $templatePath The path to the template.
     * @return string If parameter is null then constant is used.  Otherwise, 
     * the value of the parameter is used.
     */
    protected static function templatePath(?string $templatePath = null): string {
        return $templatePath ?? self::$templatePath;
    }
}