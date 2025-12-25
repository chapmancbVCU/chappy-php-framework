<?php
namespace Console\Commands;

use Console\Helpers\View;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for making a new view file.
 */
class MakeViewCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:view')
            ->setDescription('Generates a new view')
            ->setHelp('php console make:view <directory_name>.<view_name>')
            ->addArgument('view-name', InputArgument::REQUIRED, 'Pass name of directory and view');
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
        $viewArray = explode(".", $input->getArgument('view-name'));

        if (sizeof($viewArray) !== 2) {
            Tools::info(
                'Issue parsing argument. Make sure your input is in the format: <directory_name>.<file_name>',
                'debug',
                'red'
            );
            return Command::FAILURE;
        }

        $directory = View::VIEW_PATH.$viewArray[0];
        Tools::createDirWithPrompt($directory, $input, $output);
        return View::makeView($directory . DS . $viewArray[1].'.php');
    }
}
