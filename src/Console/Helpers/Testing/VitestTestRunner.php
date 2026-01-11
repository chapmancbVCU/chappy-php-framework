<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Console\Helpers\Tools;
use Core\Lib\Utilities\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VitestTestRunner extends TestRunner {
    /**
     * Path for component tests.
     */
    public const COMPONENT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'component'.DS;

    /**
     * Path for view tests.
     */
    public const VIEW_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'view'.DS;

    /**
     * The command for Vitest
     */
    public const TEST_COMMAND = "npm test ";

    /**
     * File extension for Vitest unit tests.
     */
    public const TEST_FILE_EXTENSION = ".test.js";

    /**
     * Path for unit tests.
     */
    public const UNIT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'unit'.DS;

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
     * Performs all tests.
     *
     * @return int A value that indicates success, invalid, or failure.
     */
    public function allTests(): int {
        $componentTests = self::getAllTestsInSuite(self::COMPONENT_PATH, self::TEST_FILE_EXTENSION);
        $unitTests = self::getAllTestsInSuite(self::UNIT_PATH, self::TEST_FILE_EXTENSION);
        $viewTests = self::getAllTestsInSuite(self::VIEW_PATH, self::TEST_FILE_EXTENSION);

        if(Arr::isEmpty($componentTests) && Arr::isEmpty($unitTests) && Arr::isEmpty($viewTests)) {
            $this->noAvailableTestsMessage();
            return Command::FAILURE;
        }

        $this->testSuite($componentTests, self::TEST_COMMAND);
        $this->testSuite($unitTests, self::TEST_COMMAND);
        $this->testSuite($viewTests, self::TEST_COMMAND);

        Tools::info("All available test have been completed");
        return Command::SUCCESS;
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