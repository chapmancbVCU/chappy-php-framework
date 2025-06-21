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

class MailerService {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected Mailer $mailer;
    const MIME_7ZIP = 'application/x-7z-compressed';
    const MIME_BMP = 'image/bmp';
    const MIME_CSS = 'text/css';
    const MIME_CSV = 'text/csv';
    const MIME_DOC = 'application/msword';
    const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const MIME_GIF = 'image/gif';
    const MIME_GZIP = 'application/gzip';
    const MIME_JAVASCRIPT = 'application/javascript';
    const MIME_JPG = 'image/jpeg';
    const MIME_JSON = 'application/json';
    const MIME_MARKDOWN = 'text/markdown';
    const MIME_HTML = 'text/html';
    const MIME_PDF = 'application/pdf';
    const MIME_PHP = 'application/x-httpd-php';
    const MIME_PNG = 'image/png';
    const MIME_SVG = 'image/svg+xml';
    const MIME_TAR = 'application/x-tar';
    const MIME_TEXT = 'text/plain';
    const MIME_WEBP = 'image/webp';
    const MIME_XLS = 'application/vnd.ms-excel';
    const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const MIME_XML = 'application/xml';
    const MIME_ZIP = 'application/zip';
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
     * Processes attachment if key labeled content is found.
     *
     * @param array $attachment The attachment.
     * @param Email $email The Email to be sent.
     * @return Email $email The Email to be sent after attachments have been 
     * processed.
     */
    protected static function attach(array $attachment, Email $email): Email {
        return $email->attach(
            $attachment['content'],
            $attachment['name'] ?? null,
            $attachment['mime'] ?? null
        );
    }

    /**
     * Processes attachment if key labeled path is found.
     *
     * @param array $attachment The attachment.
     * @param Email $email The Email to be sent.
     * @return Email $email The Email to be sent after attachments have been 
     * processed.
     */
    protected static function attachFromPath(array $attachment, Email $email): Email {
        return $email->attachFromPath(
            $attachment['path'],
            $attachment['name'] ?? null,
            $attachment['mime'] ?? null
        );
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
        ?string $error = null
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

        Logger::log(json_encode($log), $status === 'failed' ? 'error' : 'info');
    }

    /**
     * Process attachments to be sent.
     *
     * @param array $attachments The array of attachments.
     * @param Email $email The Email to be sent.
     * @return Email $email The Email to be sent after attachments have been 
     * processed.
     */
    protected function processAttachments(array $attachments, Email $email): Email {
        if(Arr::isAssoc($attachments)) {
            if(isset($attachments['path'])) {
                $email = self::attachFromPath($attachments, $email);
            } else if(isset($attachments['content'])) {
                $email = self::attach($attachments, $email);
            }
            return $email;
        }

        foreach($attachments as $attachment) {
            if(isset($attachment['path'])) {
                $email = self::attachFromPath($attachment, $email);
            } else if(isset($attachment['content'])) {
                $email = self::attach($attachment, $email);
            }
        }
        return $email;
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
     * @param string|null $template The content if it exists.
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
                $email = $this->processAttachments($attachments, $email);
            }

            $this->mailer->send($email);

            $this->mailLogger(
                'failed',
                $to,
                $subject,
                $htmlBody,
                null,
                $template
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
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Sends a text E-mail.
     *
     * @param string $to The recipient.
     * @param string $subject The E-mail's subject.
     * @param string $htmlBody The E-mail's HTML content.
     * @param string $textBody The E-mail's text content.
     * @param string|null $template The content if it exists.
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
                $email = $this->processAttachments($attachments, $email);
            }

            $this->mailer->send($email);

            $this->mailLogger(
                'failed',
                $to,
                $subject,
                $htmlBody,
                $textBody,
                $template
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
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * 
     * 
     *
     * @param string $to The recipient.
     * @param string $subject The E-mail's subject.
     * @param string $template The name of the template.
     * @param array $data Any data that the template uses.
     * @param string|null $layout The layout if it exists.
     * @return bool True if sent, otherwise we return false.
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data, ?string $layout = null, array $attachments = []): bool {
        
        $html = $this->template($template, $data, $layout);

        $textPath = self::$templatePath . $template . '.txt';
        if(file_exists($textPath)) {
            $text = $this->renderTemplateFile($textPath, $data);
            return $this->sendWithText($to, $subject, $html, $text, $template, $attachments);
        }
        return $this->send($to, $subject, $html, $template, $attachments);
    }

    /**
     * Prepares E-mail content based on template to be sent.
     *
     * @param string $view The name of the template.
     * @param array $data Any data that the template uses.
     * @param string|null $layout The layout if it exists.
     * @return string The E-mail's contents.
     */
    protected function template(string $view, array $data = [], ?string $layout = null): string {
        $viewPath = self::$templatePath . $view . '.php';
        if(!file_exists($viewPath)) {
            throw new Exception("Email view $view not found");
        }

        extract($data);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if($layout) {
            $layoutPath = self::$layoutPath . $layout . '.php';
            if(file_exists($layoutPath)) {
                ob_start();
                include $layoutPath;
                return ob_get_clean();
            }
        }

        return $content;
    }
}