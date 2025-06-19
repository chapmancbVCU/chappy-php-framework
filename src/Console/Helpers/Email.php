<?php

use Symfony\Component\Console\Command\Command;

class Email {
    protected static string $layoutPath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS.'layouts'.DS;
    protected static string $templatePath = CHAPPY_BASE_PATH.DS.'resources'.DS.'views'.DS.'emails'.DS;

    public static function emailTemplate(): string {
        return '';
    }

    public static function layoutTemplate(): string {
        return '';
    }

    public static function makeEmail(): int {

        return Command::SUCCESS;
    }

    public static function makeLayout(): int {

        return Command::SUCCESS;
    }
}