<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputArgument;
use Core\Lib\Utilities\Str;

/**
 * Supports ability to generate a new menu file by running make:menu.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/layouts#menus">here</a>.
 */
class MakeMenuCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:menu')
            ->setDescription('Generates a new menu')
            ->setHelp('php console make:menu <menu_name>')
            ->addArgument('menu-name', InputArgument::OPTIONAL, 'Pass the name for the new menu');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $menuName = $this->getArgument('menu-name');
        $message = "Enter name for new menu file.";
        if($menuName) {
            View::argOptionValidate($menuName, $message, $this->question(), ['max:50']);
        } else {
            $menuName = View::prompt($message, $this->question(), ['max:50']);
        }
        return View::makeMenu(Str::lcfirst($menuName));
    }
}
