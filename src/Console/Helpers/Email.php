<?php
namespace Console\Helpers;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;


/**
 * Supports commands related to the MailerService.
 */
class Email {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected static string $mailerPath = CHAPPY_BASE_PATH.DS.'app'.DS.'CustomMailers'.DS;
    protected static string $templatePath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;

    /**
     * Creates directory for layout if it does not exist.
     *
     * @return void
     */
    public static function layoutPath(): void {
        if(!is_dir(self::$layoutPath)) {
            mkdir(self::$layoutPath, 0755, true);
        }
    }

    /**
     * Template for E-mail layout.
     *
     * @return string The layout template.
     */
    public static function layoutTemplate(): string {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>

</body>
</html>';
    }

    /**
     * Template for custom mailer class.
     *
     * @param string $mailerName The name of the custom mailer class.
     * @return string The class' contents.
     */
    public function mailerTemplate(string $mailerName): string {
        $mailerName = Str::ucfirst($mailerName);
        return '<?php
declare(strict_types=1);
namespace Core\Lib\Mail;

/**
 * Class for generating a reset password E-mail.
 */
class '.$mailerName.'Mailer extends AbstractMailer {
    /**
     * Overrides getData from parent.
     *
     * @return array Data to be used by E-mail.
     */
    protected function getData(): array {
        // Implement function
    }

    /**
     * Overrides getSubject from parent.
     *
     * @return string The E-mail\'s subject.
     */
    protected function getSubject(): string {
        // Implement function
    }

    /**
     * Overrides getTemplate from parent.
     *
     * @return string The template to be used.
     */
    protected function getTemplate(): string {
        // Implement function
    }
}      
';
    }

    /**
     * Generates a new E-mail.
     *
     * @param InputInterface $input The input.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEmail(InputInterface $input): int {
        Tools::pathExists(self::$templatePath);
        $emailName = self::$templatePath . $input->getArgument('email-name') . '.php';
        return Tools::writeFile($emailName, '', 'E-mail file');
    }

    /**
     * Generates a new E-mail layout.
     *
     * @param InputInterface $input The input.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeLayout(InputInterface $input): int {
        Tools::pathExists(self::$layoutPath);
        $layoutName = self::$layoutPath . $input->getArgument('email-layout') . '.php';
        return Tools::writeFile($layoutName, self::layoutTemplate(), 'E-mail layout');
    }

    /**
     * Generates a new custom mailer class.
     *
     * @param InputInterface $input The input.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMailer(InputInterface $input): int {
        Tools::pathExists(self::$mailerPath);
        $mailerName = $input->getArgument('mailer-name');
        $mailerPath = self::$mailerPath . $mailerName . '.php';
        $content = self::mailerTemplate($mailerName);
        return Tools::writeFile($mailerPath, $content, "Custom mailer $mailerName");
    }
}