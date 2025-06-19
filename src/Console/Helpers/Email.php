<?php
namespace Console\Helpers;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class Email {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected static string $templatePath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;


    public static function layoutTemplate(): string {
        return '';
    }

    public static function makeEmail(InputInterface $input): int {
        $emailName = self::$templatePath . $input->getArgument('email-name') . '.php';
        dd($emailName);
        return Tools::writeFile($emailName, '', 'E-mail file');
    }

    public static function makeLayout(): int {

        return Command::SUCCESS;
    }
}