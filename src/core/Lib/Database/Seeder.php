<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Core\DB;
use Console\Helpers\Tools;
use Core\Lib\Logging\Logger;

/**
 * Abstract class for seeders.
 */
abstract class Seeder {
    /**
     * Instance of the database connection.
     *
     * @var DB
     */
    protected DB $_db;


    /**
     * Constructor for Seeder class.  Primary role is to get DB instance.
     */
    public function __construct() {
        $this->_db = DB::getInstance();
    }

    /**
     * All seeders must implement the run method.
     *
     * @return void
     */
    abstract public function run(): void;

    /**
     * Call another seeder class.
     *
     * @param string $seederClass The name of the seeder class.
     * @return void
     */
    protected function call(string $seederClass): void {
        if(class_exists($seederClass)) {
            $seeder = new $seederClass();
            Tools::info("Running {$seederClass}");
            $seeder->run();
        } else {
            Tools::info("Seeder class {$seederClass} not found.", Logger::ERROR, Tools::BG_RED);
        }
    }
}