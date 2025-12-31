<?php
namespace Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psy\Shell;

/**
 * Supports execution for tinker command. 
 */
class TinkerCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void 
    {
        $this->setName('tinker')
            ->setDescription('Interact with the application');
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
        $variables = [
            'db' => \Core\DB::getInstance(),
            'user' => new \App\Models\Users()
        ];

        $shell = new Shell();
        $shell->setScopeVariables($variables);
        $shell->run();

        return self::SUCCESS;
    }
}