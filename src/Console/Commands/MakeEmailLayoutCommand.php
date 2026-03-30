<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Email;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new email layout by typing make:email-layout.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/email#templates-and-layouts">here</a>.
 */
class MakeEmailLayoutCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:email:layout')
            ->setDescription('Generates a new email layout')
            ->setHelp('php console make:email <email_layout>')
            ->addArgument('email-layout', InputArgument::OPTIONAL, 'Pass the name of the new email layout');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $layoutName = $this->getArgument('email-layout');
        $message = "Enter name for email layout.";
        if($layoutName) {
            Email::argOptionValidate($layoutName, $message, $this->question(), ['max:50']);
        } else {
            $layoutName = Email::prompt($message, $this->question(), ['max:50']);
        }
        return Email::makeLayout($layoutName);
    }
}
