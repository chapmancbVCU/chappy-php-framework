<?php
namespace Console\Commands;

use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Console\Helpers\React;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Implements command for making a new react view by running react:page.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class MakeReactPageCommand extends Command {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('react:page')
            ->setDescription('Generates a new React Page')
            ->setHelp('php console react:page <directory_name>.<page_name>')
            ->addArgument('page-name', InputArgument::REQUIRED, 'Pass name of directory and React page')
            ->addOption('named', null, InputOption::VALUE_NONE, 'Creates as a named export');
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
        $pageArray = Tools::dotNotationVerify('page-name', $input);
        if($pageArray == Command::FAILURE) return Command::FAILURE;

        $directory = React::PAGE_PATH . $pageArray[0];
        $isDirMade = Tools::createDirWithPrompt($directory, $input, $output);

        if($isDirMade == Command::FAILURE) return Command::FAILURE;
        
        $pageName = Str::ucfirst($pageArray[1]);
        $filePath = $directory . DS . $pageName.'.jsx';
        $named = $input->getOption('named');

        return React::makePage($filePath, $pageName, $named);
    }
}
