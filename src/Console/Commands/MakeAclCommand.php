<?php
namespace Console\Commands;
 
use Console\HasValidators;
use Console\Helpers\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Supports ability to generate a menu_acl json file by running make:acl.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/layouts#menu-acls">here</a>.
 */
class MakeAclCommand extends Command {
    use HasValidators;

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
     * @param InputInterface $input The input.
     * @param OutputInterface $output The output.
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $menuName = $input->getArgument('acl-name');
        if($menuName) {
            $isValidated = $this->required()
                ->noSpecialChars()
                ->alpha()
                ->notReservedKeyword()
                ->max(50)
                ->validate($menuName);
            if(!$isValidated) return Command::FAILURE;
        } else {
            $message = "Enter name for new acl file.";
            $menuName = View::prompt($message, $input, $output);
        }
            
        return View::makeMenuAcl($menuName);
    }
}
