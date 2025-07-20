<?php
namespace Console\Commands;

use Console\Helpers\Events;
use Core\Lib\Utilities\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates a new event class.
 */
class MakeEventCommand extends Command
{
    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:event')
            ->setDescription('Generates a new event class')
            ->setHelp('php console make:event <event-name>')
            ->addArgument('event-name', InputArgument::REQUIRED, 'Pass the name for the new event');
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
        $eventName = Str::ucfirst($input->getArgument('event-name'));
        return Events::makeEvent($eventName);
    }
}