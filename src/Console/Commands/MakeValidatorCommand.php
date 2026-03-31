<?php
namespace Console\Commands;

use Console\ConsoleCommand;
use Console\Helpers\Validator;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Input\InputArgument;

/** 
 * Generates a new Custom Form Validator by running make:validator.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/server_side_validation#custom-validators">here</a>.
 */
class MakeValidatorCommand extends ConsoleCommand {
    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure(): void {
        $this->setName('make:validator')
            ->setDescription('Creates a new custom form validator')
            ->setHelp('run php console make:validator')
            ->addArgument('validator-name', InputArgument::OPTIONAL, 'Pass the name for the custom form validator');
    }

    /**
     * Executes the command
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    protected function handle(): int
    {
        $validatorName = $this->getArgument('validator-name');
        $message = "Enter name for new validator.";
        if($validatorName) {
            Validator::argOptionValidate($validatorName, $message, $this->question(), ['max:50']);
        } else {
            $validatorName = Validator::prompt($message, $this->question(), ['max:50']);
        }
        return Validator::makeValidator(Str::ucfirst($validatorName));
    }
}