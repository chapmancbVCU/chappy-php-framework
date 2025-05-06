<?php
namespace Console\Commands;
 
use Console\Helpers\Model;
use Console\Helpers\Tools;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Supports ability to generate new model class.
 */
class GenerateModelCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:model')
            ->setDescription('Generates a new model file!')
            ->setHelp('Generates a new model file.')
            ->addArgument('modelname', InputArgument::REQUIRED, 'Pass the model\'s name.')
            ->addOption('upload', null, InputOption::VALUE_NONE, 'Upload flag', '');
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
        $modelName = Str::ucfirst($input->getArgument('modelname'));
        $path = ROOT.DS.'app'.DS.'Models'.DS.$modelName.'.php';

        if($input->getOption('upload')) {
            return Tools::writeFile($path, Model::makeUploadModel($modelName), 'Model');
        } else {
            return Tools::writeFile($path, Model::makeModel($modelName), 'Model');
        }

    }
}
