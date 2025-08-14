<?php
declare(strict_types=1);
namespace Core\Lib\Notifications\Channels;

use Core\Lib\Mail\AbstractMailer;
use Core\Lib\Notifications\Contracts\Channel;
use Core\Lib\Notifications\Notification;
use Core\Lib\Mail\MailerService;        // <- your service
use RuntimeException;
use InvalidArgumentException;

/**
 * Notification channel that sends email via the framework MailerService
 * and/or AbstractMailer-based custom mailers.
 *
 * Supported payload shapes (returned by Notification::toMail()):
 *
 * 1) Template mode (MailerService::sendTemplate)
 *    [
 *      // 'to' => 'user@example.com',            // optional; auto-routes from notifiable if omitted
 *      'subject'     => 'Subject',
 *      'template'    => 'welcome',
 *      'data'        => ['user' => $user],
 *      'layout'      => 'default',               // optional
 *      'attachments' => [...],                   // optional (use Attachments::content/path helpers)
 *      'layoutPath'  => null,                    // optional
 *      'templatePath'=> null,                    // optional
 *      'styles'      => 'default',               // optional
 *      'stylesPath'  => null,                    // optional
 *    ]
 *
 * 2) Raw HTML (MailerService::send / sendWithText)
 *    [
 *      // 'to' => 'user@example.com',            // optional; auto-route
 *      'subject'     => 'Subject',
 *      'html'        => '<p>Hello</p>',
 *      'text'        => 'Hello',                 // optional -> triggers sendWithText()
 *      'template'    => 'welcome',               // optional: used only for logging
 *      'attachments' => [...],                   // optional
 *    ]
 *
 * 3) Custom mailer (AbstractMailer subclass)
 *    [
 *      'mailer'      => \Core\Lib\Mail\WelcomeMailer::class,
 *      // The mailer can implement a static sendTo(\App\Models\Users $user): bool
 *      // If not, we construct it and call buildAndSend(...) with optional overrides:
 *      'layout'      => null,
 *      'attachments' => [],
 *      'layoutPath'  => null,
 *      'templatePath'=> null,
 *      'styles'      => null,
 *      'stylesPath'  => null,
 *    ]
 */
final class MailChannel implements Channel {
    public function __construct(private ?MailerService $service = null) {
        $this->service ??= new MailerService();
    }

    /**
     * Short channel name used in Notification::via().
     *
     * @return string The channel identifier, always "mail".
     */
    public static function name(): string {
        return 'mail';
    }

    private function notifyWithTemplate(array $payload, string $subject, string $to) {
        $ok = $this->service->sendTemplate(
            $to,
            $subject,
            (string)$payload['template'],
            (array)($payload['data'] ?? []),
            $payload['layout'] ?? null,
            (array)($payload['attachments'] ?? []),
            $payload['layoutPath'] ?? null,
            $payload['templatePath'] ?? null,
            $payload['styles'] ?? null,
            $payload['stylesPath'] ?? null
        );

        if(!$ok) {
            throw new RuntimeException('MailerService::sendTemplate returned false.');
        }
    }

    /**
     * Ensure the notifiable is the expected user type for AbstractMailer.
     *
     * @return \App\Models\Users
     */
    private static function requireUser(mixed $notifiable): object {
        if($notifiable instanceof \App\Models\Users) {
            return $notifiable;
        }
        throw new InvalidArgumentException(
            'Custom mailers require the notifiable to be an instance of \App\Models\Users.'
        );
    }

    /**
     * Resolve recipient email from the notifiable.
     */
    private function route(mixed $notifiable): string {
        if(method_exists($notifiable, 'routeNotificationForMail')) {
            $email = $notifiable->routeNotificationForMail();
            if(is_string($email) && $email !== '') {
                return $email;
            }
        }

        if(isset($notifiable->email) && is_string($notifiable->email) && $notifiable->email) {
            return $notifiable->email;
        }
        throw new InvalidArgumentException("No email route found for notifiable entity.");
    }

    /**
     * @param mixed $notifiable
     * @param mixed $notification
     * @param mixed $payload
     *
     * @phpstan-param object $notifiable
     * @phpstan-param Notification $notification
     * @phpstan-param array<string,mixed>|null $payload
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    #[\Override]
    public function send(mixed $notifiable, mixed $notification, mixed $payload): void {
        if(!($notification instanceof Notification)) {
            throw new InvalidArgumentException('MailChannel expects a Notification instance.');
        }

        $payload = is_array($payload) ? $payload : [];

        // Custom mailer path
        if(isset($payload['mailer'])) {
            $this->sendWithCustomMailer($notifiable, $payload);
            return;
        }

        $to = $payload['to'] ?? $this->route($notifiable);
        $subject = (string)($payload['subject'] ?? 'Notification');

        // Template mode
        if(isset($payload['template']) && !isset($payload['html'])) {
            $this->notifyWithTemplate($payload, $to, $subject);
            return;
        }

        // Raw HTML (with optional text)
        if(isset($payload['html'])) {
            $html = (string)$payload['html'];
            $text = array_key_exists('text', $payload) ? (string)$payload['text'] : null;
            $templateForLog = $payload['template'] ?? null;
            $attachments= (array)($payload['attachments'] ?? []);

            $ok = $text !== null
                ? $this->service->sendWithText($to, $subject, $html, $text, $templateForLog, $attachments)
                : $this->service->send($to, $subject, $html, $templateForLog, $attachments);

            if(!$ok) {
                throw new RuntimeException('MailerService::send()/sendWithText() returned');
            }
            return;
        }

        throw new InvalidArgumentException(
            'Mail payload must include one of: "template", "html", or "mailer".'
        );
    }

    /**
     * Handle AbstractMailer subclasses.
     *
     * @param object $notifiable Must be compatible with the mailer constructor (expects \App\Models\Users)
     * @param array<string,mixed> $payload
     */
    private function sendWithCustomMailer(object $notifiable, array $payload): void {
        $mailerClass = (string)$payload['mailer'];
        if(!class_exists($mailerClass)) {
            throw new InvalidArgumentException("Mailer class not found: {$mailerClass}");
        }
        if(!is_subclass_of($mailerClass, AbstractMailer::class)) {
            throw new InvalidArgumentException("Mailer must extend " . AbstractMailer::class);
        }

        // Prefer conventional static sendTo($user)
        if(method_exists($mailerClass, 'sendTo')) {
            $ok = $mailerClass::sendTo($this->requireUser($notifiable));
            if(!$ok) {
                throw new RuntimeException("{$mailerClass}::sendTo() returned false.");
            }
            return;
        }

        // Fallback: construct and call buildAndSend(...) with optional overrides.
        $mailer = new $mailerClass($this->requireUser($notifiable));
        $ok = $mailer->buildAndSend(
            $payload['layout'] ?? null,
            (array)($payload['attachments']) ?? null,
            $payload['layoutPath'] ?? null,
            $payload['templatePath'] ?? null,
            $payload['styles'] ?? null,
            $payload['stylesPath'] ?? null
        );

        if(!$ok) {
            throw new RuntimeException(("{$mailerClass}::buildAndSend() returned false."));
        }
    }
}