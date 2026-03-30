<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Email;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new email by running make:email.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/email#templates-and-layouts">here</a>.
 */
class MakeEmailCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:email')
            ->setDescription('Generates a new email')
            ->setHelp('php console make:email <email_name>')
            ->addArgument('email-name', InputArgument::OPTIONAL, 'Pass the name of the new email');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $emailName = $this->getArgument('email-name');
        $message = "Enter name for new email.";
        if($emailName) {
            Email::argOptionValidate($emailName, $message, $this->question(), ['max:50']);
        } else {
            $emailName = Email::prompt($message, $this->question(), ['max:50']);
        }
        return Email::makeEmail($emailName);
    }
}
