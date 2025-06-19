<?php
namespace Console\Helpers;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class Email {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected static string $templatePath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;

    public static function layoutPath(): void {
        if(!is_dir(self::$layoutPath)) {
            mkdir(self::$layoutPath, 0755, true);
        }
    }

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

    public static function makeEmail(InputInterface $input): int {
        Tools::pathExists(self::$templatePath);
        $emailName = self::$templatePath . $input->getArgument('email-name') . '.php';
        return Tools::writeFile($emailName, '', 'E-mail file');
    }

    public static function makeLayout(InputInterface $input): int {
        Tools::pathExists(self::$layoutPath);
        $layoutName = self::$layoutPath . $input->getArgument('layout-name') . '.php';
        return Tools::writeFile($layoutName, self::layoutTemplate(), 'E-mail layout');
    }
}