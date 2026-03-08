<?php
namespace Console\Commands;
 
use Console\Helpers\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new layout by running make:layout.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/layouts#build-layout">here</a>.
 */
class MakeLayoutCommand extends Command {
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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get inputs
        $layoutName = $input->getArgument('layout-name');
        $menu = $input->getOption('menu');
        $menuAcl = $input->getOption('menu-acl');
    
        if($layoutName) {
            View::argOptionValidate($layoutName, View::LAYOUT_PROMPT, $input, $output, 'layout-name', ['max:50']);
            $menuName = View::menu($layoutName, $menu);
            if($menuAcl) View::makeMenuAcl($layoutName);
            return View::makeLayout($layoutName, $menuName);
        }

        $layoutName = View::layoutNamePrompt($input, $output);
        $menuName = View::menuConfirm($layoutName, $menu, $input, $output);
        View::menuAclConfirm($layoutName, $menuAcl, $input, $output);
        return View::makeLayout($layoutName, $menuName);
    }
}
