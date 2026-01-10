<?php
declare(strict_types=1);
namespace Console\Helpers\Testing;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VitestTestRunner extends TestRunner {
    public const UNIT_PATH = 'resources'.DS.'js'.DS.'tests'.DS.'unit'.DS;

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

    public static function parseOptions(InputInterface $input): string { return ""; }
}