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
            ->addArgument('layout-name', InputArgument::REQUIRED, 'Pass the name of the new layout')
            ->addOption(
                'menu',
                null,
                InputOption::VALUE_OPTIONAL,
                'Menu file associated with a layout',
                false)
            ->addOption(
                'menu-acl',
                null,
                InputOption::VALUE_OPTIONAL,
                'menu_acl json file for menus and layouts',
                false);
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
    
        // Process menu-acl input
        if($menuAcl === false) {
            console_warning('--menu-acl argument not set so we ignore operation');
        } else if($menuAcl === null) {
            View::makeMenuAcl($layoutName);
        } else {
            console_warning('--menu-acl does not accept an argument');
        }

        // Process menu input
        if($menu === false) {
            console_notice('--menu argument not set so we ignore operation');
            return View::makeLayout($layoutName, 'main');
        }
        else if($menu === null) {
            View::makeMenu($layoutName);
            return View::makeLayout($layoutName, $layoutName);
        } else {
            console_notice('--menu does not accept an argument');
            return Command::FAILURE;
        }
    }
}
