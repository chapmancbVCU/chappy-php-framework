<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Throwable;
use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;
use Exception;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class MailerService {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected Mailer $mailer;
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
     * @param string $htmlBody The E-mail's content
     * @param string|null $template The content if it exists.
     * @return bool True if sent, otherwise we return false.
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $template = null): bool {
        try {
            $email = (new Email())
                ->from(Env::get('MAIL_FROM_ADDRESS'))
                ->to($to)
                ->subject($subject)
                ->html($htmlBody);

            $this->mailer->send($email);

            Logger::log(json_encode([
                'MailerService_status' => 'sent',
                'timestamp' => date('Y-m-d H:i:s'),
                'to' => $to,
                'subject' => $subject,
                'body' => $htmlBody,
                'template' => $template ?? null,
                'transport' => Env::get('MAILER_DSN'),
                'mailer_class' => static::class
            ]));

            return true;
        } catch (Throwable $e) {
            Logger::log(json_encode([
                'MailerService_status' => 'failed',
                'timestamp' => date('Y-m-d H:i:s'),
                'to' => $to,
                'subject' => $subject,
                'body' => $htmlBody,
                'template' => $template ?? null,
                'transport' => Env::get('MAILER_DSN'),
                'mailer_class' => static::class,
                'error' => $e->getMessage()
            ]), 'error');

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
    public function sendWithText(string $to, string $subject, string $htmlBody, string $textBody, ?string $template = null): bool {
        try {
            $email = (new Email())
                ->from(Env::get('MAIL_FROM_ADDRESS'))
                ->to($to)
                ->subject($subject)
                ->text($textBody)
                ->html($htmlBody);

            $this->mailer->send($email);

            Logger::log(json_encode([
                'MailerService_status' => 'sent',
                'timestamp' => date('Y-m-d H:i:s'),
                'to' => $to,
                'subject' => $subject,
                'body' => $htmlBody,
                'template' => $template ?? null,
                'transport' => Env::get('MAILER_DSN'),
                'mailer_class' => static::class
            ]));

            return true;
        } catch (Throwable $e) {
            Logger::log(json_encode([
                'MailerService_status' => 'failed',
                'timestamp' => date('Y-m-d H:i:s'),
                'to' => $to,
                'subject' => $subject,
                'body' => $htmlBody,
                'template' => $template ?? null,
                'transport' => Env::get('MAILER_DSN'),
                'mailer_class' => static::class,
                'error' => $e->getMessage()
            ]), 'error');

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
    public function sendTemplate(string $to, string $subject, string $template, array $data, ?string $layout = null): bool {
        
        $html = $this->template($template, $data, $layout);

        $textPath = self::$templatePath . $template . '.txt';
        if(file_exists($textPath)) {
            $text = $this->renderTemplateFile($textPath, $data);
            return $this->sendWithText($to, $subject, $html, $text, $template);
        }
        return $this->send($to, $subject, $html, $template);
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