<?php
namespace Console\Commands;

use Console\Console;
use Console\HasValidators;
use Console\Helpers\Model;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new model class by typing make:model.
 * More information can be found <a href="https://chapmancbvcu.github.io/chappy-php-starter/models#overview">here</a>.
 */
class GenerateModelCommand extends Command
{
    use HasValidators;

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:model')
            ->setDescription('Generates a new model file!')
            ->setHelp('make:model <model-name>; add --upload for model configured to support file uploads.')
            ->addArgument('model-name', InputArgument::OPTIONAL, 'Pass the model\'s name.')
            ->addOption('upload', null, InputOption::VALUE_NONE, 'Upload flag');
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
        $modelName = $input->getArgument('model-name');

        if($modelName) {
            Console::argOptionValidate(
                $modelName, 
                Model::PROMPT_MESSAGE,
                $input,
                $output,
                'model-name',
                ['max:50']
            );
        }
        
        $uploadOption = $input->getOption('upload');
        if($modelName) {
            $modelName = Str::ucfirst($modelName);
            $contents =  Model::contents($modelName, $uploadOption);
        } else {
            $modelName = Model::modelNamePrompt($input, $output);
            $contents = Model::uploadPrompt($modelName, $input, $output, $uploadOption);
        }

        return Tools::writeFile(Model::MODEL_PATH.$modelName.'.php', $contents, "Model");
    }
}
