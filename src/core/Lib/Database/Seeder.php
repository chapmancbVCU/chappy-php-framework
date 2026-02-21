<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Core\DB;
use Core\Exceptions\FactorySeeder\FactorySeederException;

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
     * Processes parameters provided and calls seed class to perform actual 
     * work.  This function accepts a string or an array of strings.
     *
     * @param string|array $seederClass The name of the seeder class or array 
     * of seeder classes.
     * @return void
     */
    protected function call(string|array $seederClass): void {
        if(env('APP_ENV') === 'production') {
            throw new FactorySeederException("Factories and seeders can only be run in development mode");
            return;
        }
        
        if(is_array($seederClass)) {
            foreach($seederClass as $class) {
                $this->seed($class);
            }
        } else {
            $this->seed($seederClass);
        }
    }

    /**
     * Performs seeding of data.
     *
     * @param string $seederClass The name of the seeder class.
     * @return void
     */
    private function seed(string $seederClass): void {
        if(class_exists($seederClass)) {
            $seeder = new $seederClass();
            console_info("Running {$seederClass}");
            $seeder->run();
        } else {
            console_error("Seeder class {$seederClass} not found.");
        }
    }
}