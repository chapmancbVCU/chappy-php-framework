<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestRunner {
    /**
     * The array of options allowed as input for the test command.
     */
    public const ALLOWED_OPTIONS = [];

    /**
     * A string of input options provided as input when running the 
     * test command.
     *
     * @var string 
     */
    public string $inputOptions;

    /**
     * The Symfony OutputInterface object.
     *
     * @var OutputInterface 
     */
    public OutputInterface $output;

    /**
     * Constructor
     *
     * @param InputInterface $input The Symfony InputInterface object.
     * @param OutputInterface $output The Symfony OutputInterface object.
     */
    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    /**
     * Retrieves all files in test suite so they can be run.
     *
     * @param string $path Path to test suite.
     * @param string $ext File extension to specify between php and js related tests.
     * @return array The array of all filenames in a particular directory.
     */
    public static function getAllTestsInSuite(string $path, string $ext): array {
        return glob($path."*.".$ext);
    }

}