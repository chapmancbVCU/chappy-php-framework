<?php
namespace Console\Commands;
 
use Console\Helpers\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new email.
 */
class MakeEmailLayoutCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:email-layout')
            ->setDescription('Generates a new email layout')
            ->setHelp('php console make:email <email_layout>')
            ->addArgument('email-layout', InputArgument::REQUIRED, 'Pass the name of the new email layout');
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
        return Email::makeLayout($input);
    }
}
