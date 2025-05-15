<?php
namespace Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psy\Shell;

class TinkerCommand extends Command
{

    protected function configure(): void {
        $this->setName('tinker')
            ->setDescription('Interact with the application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
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