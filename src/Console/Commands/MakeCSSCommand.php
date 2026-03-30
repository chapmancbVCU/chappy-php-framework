<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\HasValidators;
use Console\Helpers\View;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Generates a new css file by typing make:css.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/views#make-css">here</a>.
 */
class MakeCSSCommand extends ConsoleCommand {
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
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $fileName = $this->getArgument('css-name');
        $message = "Enter name for new CSS file.";
        if($fileName) {
            View::argOptionValidate($fileName, $message, $this->question(), ['max:50']);
        } else {
            $fileName = View::prompt($message, $this->question(), ['max:50']);
        }

        return View::makeCSS($fileName);
    }
}
