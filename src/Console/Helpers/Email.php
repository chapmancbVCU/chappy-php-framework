<?php
declare(strict_types=1);
namespace Console\Helpers;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Supports commands related to the MailerService.
 */
class Email {
    /**
     * Path for email layouts.
     */
    private const LAYOUT_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;

    /**
     * Path for custom mailers.
     */
    private const MAILER_PATH = CHAPPY_BASE_PATH.DS.'app'.DS.'CustomMailers'.DS;

    /**
     * Path for email templates.
     */
    private const TEMPLATE_PATH = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;

    /**
     * Creates directory for layout if it does not exist.
     *
     * @return void
     */
    public static function layoutPath(): void {
        if(!is_dir(self::LAYOUT_PATH)) {
            mkdir(self::LAYOUT_PATH, 0755, true);
        }
    }

    /**
     * Template for E-mail layout.
     *
     * @return string The layout template.
     */
    public static function layoutTemplate(): string {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>

</body>
</html>
HTML;
    }

    /**
     * Template for custom mailer class.
     *
     * @param string $mailerName The name of the custom mailer class.
     * @return string The class' contents.
     */
    public static function mailerTemplate(string $mailerName): string {
        return <<<PHP
<?php
declare(strict_types=1);
namespace App\CustomMailers;

use core\Lib\Mail\AbstractMailer;

/**
 * Document class
 */
class {$mailerName} extends AbstractMailer {
    /**
     * Overrides getData from parent.
     *
     * @return array Data to be used by E-mail.
     */
    protected function getData(): array {
        return [];
    }

    /**
     * Overrides getSubject from parent.
     *
     * @return string The E-mail\'s subject.
     */
    protected function getSubject(): string {
        return '';
    }

    /**
     * Overrides getTemplate from parent.
     *
     * @return string The template to be used.
     */
    protected function getTemplate(): string {
        return '';
    }
}      
PHP;
    }

    /**
     * Generates a new E-mail.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeEmail(InputInterface $input): int {
        Tools::pathExists(self::TEMPLATE_PATH);
        $emailName = self::TEMPLATE_PATH . $input->getArgument('email-name') . '.php';
        return Tools::writeFile($emailName, '', 'E-mail file');
    }

    /**
     * Generates a new E-mail layout.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeLayout(InputInterface $input): int {
        Tools::pathExists(self::LAYOUT_PATH);
        $layoutName = self::LAYOUT_PATH . $input->getArgument('email-layout') . '.php';
        return Tools::writeFile($layoutName, self::layoutTemplate(), 'E-mail layout');
    }

    /**
     * Generates a new custom mailer class.
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @return int A value that indicates success, invalid, or failure.
     */
    public static function makeMailer(InputInterface $input): int {
        Tools::pathExists(self::MAILER_PATH);
        $mailerName = Str::ucfirst($input->getArgument('mailer-name')) . 'Mailer';
        $mailerPath = self::MAILER_PATH . $mailerName . '.php';
        $content = self::mailerTemplate($mailerName);
        return Tools::writeFile($mailerPath, $content, "Custom mailer $mailerName");
    }
}