<?php
namespace Console\Commands;

use Console\Helpers\View;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for making a new view file by running make:view.
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
        $viewArray = Tools::dotNotationVerify('view-name', $input);
        if($viewArray == Command::FAILURE) return Command::FAILURE;

        $directory = View::VIEW_PATH.$viewArray[0];
        $isDirMade = Tools::createDirWithPrompt($directory, $input, $output);
        
        if($isDirMade == Command::FAILURE) return Command::FAILURE;

        return View::makeView($directory . DS . $viewArray[1].'.php');
    }
}
