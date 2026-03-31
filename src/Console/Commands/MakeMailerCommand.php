<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Email;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new custom mailer by running make:mailer.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/email#custom-mailers">here</a>.
 */
class MakeMailerCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:mailer')
            ->setDescription('Generates a new custom mailer')
            ->setHelp('php console make:mailer <email_name>')
            ->addArgument('mailer-name', InputArgument::OPTIONAL, 'Pass the name of the new custom mailer');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $emailName = $this->getArgument('mailer-name');
        $message = "Enter name for new Email.";
        if($emailName) {
            Email::argOptionValidate($emailName, $message, $this->question(), ['max:50']);
        } else {
            $emailName = Email::prompt($message, $this->question(), ['max:50']);
        }
        return Email::makeMailer($emailName);
    }
}
