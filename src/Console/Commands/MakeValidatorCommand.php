<?php
namespace Console\Commands;
 
use Console\Helpers\Validator;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** 
 * Generates a new Custom Form Validator by running make:validator.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/server_side_validation#custom-validators">here</a>.
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
        $validatorName = Str::ucfirst($input->getArgument('validator-name'));
        return Validator::makeValidator($validatorName);
    }
}