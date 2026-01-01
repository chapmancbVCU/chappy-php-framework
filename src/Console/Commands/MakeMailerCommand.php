<?php
namespace Console\Commands;

use Console\Helpers\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new custom mailer by running make:mailer.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/email#custom-mailers">here</a>.
 */
class MakeMailerCommand extends Command {
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
            ->addArgument('mailer-name', InputArgument::REQUIRED, 'Pass the name of the new custom mailer');
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Email::makeMailer($input);
    }
}
