<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\View;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Implements command for making a new view file by running make:view.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/views#make-views">here</a>.
 */
class MakeViewCommand extends ConsoleCommand {
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
            ->addArgument('view-name', InputArgument::OPTIONAL, 'Pass name of directory and view');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $viewName = $this->getArgument('view-name');
        $message = "Enter name for new directory and page in following format: <directory_name>.<view_name>";
        $attributes = ['max:100', 'dotNotation'];

        if($viewName) {
            View::argOptionValidate($viewName, $message, $this->question(), $attributes, true);
        } else {
            $viewName = View::prompt($message, $this->question(), $attributes, [], null, true);
        }

        // Validate directory and view.
        [$directory, $view] = explode('.', $viewName);
        $message = "Enter name for directory";
        View::argOptionValidate($directory, $message, $this->question(), ['max:50']);
        $message = "Enter name for the new view";
        View::argOptionValidate($view, $message, $this->question(), ['max:50']);

        // Check if directory exists and create it.
        $directory = View::VIEW_PATH.Str::ucfirst($directory);
        $isDirMade = Tools::createDirWithPrompt($directory, $this->question());
        if($isDirMade == self::FAILURE) return self::FAILURE;

        $view = Str::ucfirst($view);
        return View::makeView($directory . DS . $view.'.php');
    }
}
