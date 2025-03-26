<?php
namespace Console\Commands;
 
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** 
 * Generates a new Custom Form Validator
 */
class MakeValidatorCommand extends Command {
    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure(): void {
        $this->setName('make:validator')
            ->setDescription('Creates a new custom form validator')
            ->setHelp('run php console make:validator')
            ->addArgument('validator-name', InputArgument::REQUIRED, 'Pass the name for the custom form validator');
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
        $validatorName = Str::ucfirst($input->getArgument('validatorName'));
        
        $content = '<?php
namespace App\CustomValidators;
use Core\Validators\CustomValidator;
/**
 * Describe your validator class.
 */
class '.$validatorName.'Validator extends CustomValidator {

    /**
     * Describe your function.
     * 
     * @return bool
     */ 
    public function runValidation(): bool {
        // Implement your custom validator.
    }
}
';
        // Generate unit test class
        return Tools::writeFile(
            ROOT.DS.'app'.DS.'CustomValidators'.DS.$validatorName.'.php',
            $content,
            'Custom validator class'
        );
    }
}