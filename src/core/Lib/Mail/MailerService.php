<?php
namespace Core\Lib\Mail;

use Throwable;
use Core\Lib\Utilities\Env;
use Core\Lib\Logging\Logger;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class MailerService {
    protected Mailer $mailer;

    /**
     * Creates a new mailer.
     */
    public function __construct() {
        $dsn = Env::get('MAILER_DSN');
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function send(string $to, string $subject, string $htmlBody): bool {
        try {
            $email = (new Email())
                ->from(Env::get('MAIL_FROM_ADDRESS'))
                ->to($to)
                ->subject($subject)
                ->html($htmlBody);

            Logger::log(json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'sent',
                'to' => $to,
                'subject' => $subject,
                'template' => $template ?? null,
                'transport' => Env::get('MAILER_DSN'),
                'mailer_class' => static::class,
                'body' => $htmlBody
            ]));

            $this->mailer->send($email);
            return true;
        } catch (Throwable $e) {
            logger($e, 'error');
            return false;
        }
    }
}