<?php
namespace Console\Commands;

use Console\Helpers\Tools;
use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new event class.
 */
class MakeListenerCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:listener')
            ->setDescription('Generates a new listener class')
            ->setHelp('php console make:listener <listener-name>')
            ->addArgument('listener-name', InputArgument::REQUIRED, 'Pass the name for the new listener')
            ->addOption('event', null, InputOption::VALUE_REQUIRED, 'Event name for listener', false);
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
        $listenerName = Str::ucfirst($input->getArgument('listener-name'));
        $eventName = Str::ucfirst($input->getOption('event'));

        if($eventName && $listenerName && ($eventName != $listenerName)) {
            return Events::makeListener($eventName, $listenerName);
        }
        Tools::info('Event and listener names should not be the same', 'warning', 'yellow');
        return Command::FAILURE;
    }
}