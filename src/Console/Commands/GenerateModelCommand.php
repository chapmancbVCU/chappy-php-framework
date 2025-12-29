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
            ->setHelp('make:model <modelname>; add --upload for model configured to support file uploads.')
            ->addArgument('modelname', InputArgument::REQUIRED, 'Pass the model\'s name.')
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
        return Model::makeModel($input);
    }
}
