<?php
namespace Console\Commands;

use Console\Helpers\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new css file by typing make:css.
 */
class MakeCSSCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:css')
            ->setDescription('Generates a new css')
            ->setHelp('php console make:css <css_name>')
            ->addArgument('css-name', InputArgument::REQUIRED, 'Pass the name of the new css file');
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
        return View::makeCSS($input->getArgument('css-name'));
    }
}
