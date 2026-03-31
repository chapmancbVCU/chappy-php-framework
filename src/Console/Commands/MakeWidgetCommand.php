<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\View;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Implements command for making a new widget file by running make:widget.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/widgets#overview">here</a>.
 */
class MakeWidgetCommand extends ConsoleCommand {
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
            ->addArgument('widget-name', InputArgument::OPTIONAL, 'Pass name of directory and widget');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $widgetName = $this->getArgument('widget-name');
        $message = "Enter name for new widget in the following format: <directory_name>.<view_name>";
        $attributes = ['max:100', 'dotNotation'];

        if($widgetName) {
            View::argOptionValidate($widgetName, $message, $this->question(), $attributes, true);
        } else {
            $widgetName = View::prompt($message, $this->question(), $attributes, [], null, true);
        }

        // Validate directory and widget.
        [$directory, $widget] = explode('.', $widgetName);
        $message = "Enter name for directory";
        View::argOptionValidate($directory, $message, $this->question(), ['max:50']);
        $message = "Enter name for the new widget";
        View::argOptionValidate($widget, $message, $this->question(), ['max:50']);

        // Check if directory exists and create it.
        $directory = View::WIDGET_PATH.Str::ucfirst($directory);
        $isDirMade = Tools::createDirWithPrompt($directory, $this->question());
        if($isDirMade == self::FAILURE) return self::FAILURE;

        $widget = Str::ucfirst($widget);
        return View::makeWidget($directory . DS . $widget.'.php');
    }
}
