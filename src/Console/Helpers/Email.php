<?php
namespace Console\Helpers;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;


/**
 * Supports commands related to the MailerService.
 */
class Email {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
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
}