<?php
namespace Console\Commands;

use Console\Helpers\View;
use Console\Helpers\Tools;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Implements command for making a new widget file.
 */
class MakeWidgetCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:widget')
            ->setDescription('Generates a new widget')
            ->setHelp('php console make:view <directory_name>.<widget_name>')
            ->addArgument('widget-name', InputArgument::REQUIRED, 'Pass name of directory and widget');
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
        $widgetArray = Tools::dotNotationVerify('widget-name', $input);
        if($widgetArray == Command::FAILURE) return Command::FAILURE;
        
        $directory = View::WIDGET_PATH . $widgetArray[0];
        $isDirMade = Tools::createDirWithPrompt($directory, $input, $output);
        
        if($isDirMade == Command::FAILURE) return Command::FAILURE;

        return View::makeWidget($directory . DS . $widgetArray[1].'.php');
    }
}
