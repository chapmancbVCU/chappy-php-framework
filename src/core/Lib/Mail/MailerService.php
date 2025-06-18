<?php
namespace Core\Lib\Mail;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Core\Lib\Utilities\Env;
use Throwable;

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

            $this->mailer->send($email);
            return true;
        } catch (Throwable $e) {
            logger($e, 'error');
            return false;
        }
    }
}