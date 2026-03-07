<?php
namespace Console\Commands;

use Console\Console;
use Console\HasValidators;
use Console\Helpers\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new css file by typing make:css.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/views#make-css">here</a>.
 */
class MakeCSSCommand extends Command {
    use HasValidators;

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:css')
            ->setDescription('Generates a new css')
            ->setHelp('php console make:css <css_name>')
            ->addArgument('css-name', InputArgument::OPTIONAL, 'Pass the name of the new css file');
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
        $fileName = $input->getArgument('css-name');
        if($fileName) {
            $isValidated = $this->required()
                ->noSpecialChars()
                ->alpha()
                ->notReservedKeyword()
                ->max(50)
                ->validate($fileName);
            if(!$isValidated) return Command::FAILURE;
        } else {
            $message = "Enter name for new CSS file.";
            $fileName = View::prompt($message, $input, $output);
        }

        return View::makeCSS($fileName);
    }
}
