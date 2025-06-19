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

    public function sendTemplate(string $to, string $subject, string $template, array $data, ?string $layout = null): bool {
        $html = $this->template($template, $data, $layout);
        return $this->send($to, $subject, $html, $template);
    }

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