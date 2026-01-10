<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VitestTestRunner extends TestRunner {
    public const COMPONENT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'component'.DS;
    public const UNIT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'unit'.DS;
    public const VIEW_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'view'.DS;

    public const TEST_COMMAND = "npm test ";

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(InputInterface $input, OutputInterface $output) {
        $this->inputOptions = self::parseOptions($input);
        parent::__construct($output);
    }

    /**
     * Parses Vitest related arguments and ignore Symfony arguments.
     *
     * @param InputInterface $input Instance of InputInterface from command.
     * @return string A string containing the arguments to be provided to 
     * PHPUnit.
     */
    public static function parseOptions(InputInterface $input): string { return ""; }
}