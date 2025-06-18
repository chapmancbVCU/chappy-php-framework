<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

use Throwable;
use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class MailerService {
    protected string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected Mailer $mailer;
    protected string $templatePath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;

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
}