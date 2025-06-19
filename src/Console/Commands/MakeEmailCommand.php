<?php
namespace Console\Commands;
 
use Console\Helpers\View;
use Console\Helpers\Tools;
use Console\Helpers\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new email.
 */
class MakeEmailCommand extends Command {
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
            ->addArgument('email-name', InputArgument::REQUIRED, 'Pass the name of the new email')
            ->addOption('layout', null, InputOption::VALUE_REQUIRED, 'layout-name', false);
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
        Email::makeEmail($input);


        return Command::SUCCESS;
    }
}
