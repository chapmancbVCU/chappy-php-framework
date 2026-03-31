<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Console\Helpers\React;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Implements command for making a new react view by running react:page.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/controllers_and_views#view-commands">here</a>.
 */
class MakeReactPageCommand extends ConsoleCommand {
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
            ->addArgument('page-name', InputArgument::OPTIONAL, 'Pass name of directory and React page')
            ->addOption('named', null, InputOption::VALUE_NONE, 'Creates as a named export');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $pageName = $this->getArgument('page-name');
        $named = $this->getOption('named');
        $message = "Enter name for new directory and page in following format: <directory_name>.<page_name>";
        $attributes = ['max:100', 'dotNotation'];

        if($pageName) {
            React::argOptionValidate($pageName, $message, $this->question(), $attributes, true);
        } else {
            $pageName = React::prompt($message, $this->question(), $attributes, [], null, true);
            $named = React::namedComponentPrompt($named, $this->question());
        }

        // Validate directory and page.
        [$directory, $page] = explode('.', $pageName);
        $message = "Enter name for directory";
        React::argOptionValidate($directory, $message, $this->question(), ['max:50']);
        $message = "Enter name for the new page";
        React::argOptionValidate($page, $message, $this->question(), ['max:50']);

        // Check if directory exists and create it.
        $directory = React::PAGE_PATH . Str::ucfirst($directory);
        $isDirMade = Tools::createDirWithPrompt($directory, $this->question());
        if($isDirMade == self::FAILURE) return self::FAILURE;

        $page = Str::ucfirst($page);
        $filePath = $directory . DS . $page.'.jsx';
        return React::makePage($filePath, $page, $named);
    }
}
