<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new layout by running make:layout.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/layouts#build-layout">here</a>.
 */
class MakeLayoutCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:layout')
            ->setDescription('Generates a new layout')
            ->setHelp('php console make:layout <layout_name>')
            ->addArgument('layout-name', InputArgument::OPTIONAL, 'Pass the name of the new layout')
            ->addOption(
                'menu',
                null,
                InputOption::VALUE_NONE,
                'Menu file associated with a layout')
            ->addOption(
                'menu-acl',
                null,
                InputOption::VALUE_NONE,
                'menu_acl json file for menus and layouts'
            );
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        // Get inputs
        $layoutName = $this->getArgument('layout-name');
        $menu = $this->getOption('menu');
        $menuAcl = $this->getOption('menu-acl');
    
        if($layoutName) {
            View::argOptionValidate($layoutName, View::LAYOUT_PROMPT, $this->question(), ['max:50', 'fieldName:layout-name']);
            $menuName = View::menu($layoutName, $menu);
            if($menuAcl) View::makeMenuAcl($layoutName);
            return View::makeLayout($layoutName, $menuName);
        }

        $layoutName = View::layoutNamePrompt($this->question());
        $menuName = View::menuConfirm($layoutName, $menu, $this->question());
        View::menuAclConfirm($layoutName, $menuAcl, $this->question());
        return View::makeLayout($layoutName, $menuName);
    }
}
