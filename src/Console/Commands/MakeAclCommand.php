<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to generate a menu_acl json file by running make:acl.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/layouts#menu-acls">here</a>.
 */
class MakeAclCommand extends ConsoleCommand {
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:acl')
            ->setDescription('Generates a new menu_acl json file.')
            ->setHelp('php console make:acl <menu_acl_json_name>')
            ->addArgument('acl-name', InputArgument::OPTIONAL, 'Pass the name for the new menu_acl json file');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $menuName = $this->getArgument('acl-name');
        $message = "Enter name for new acl file.";
        if($menuName) {
            View::argOptionValidate($menuName, $message, $this->question(), ['max:50']);
        } else {
            $menuName = View::prompt($message, $this->question(), ['max:50']);
        }
            
        return View::makeMenuAcl($menuName);
    }
}
