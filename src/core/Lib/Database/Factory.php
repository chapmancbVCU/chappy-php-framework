<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Faker\Factory as FakerFactory;

/**
 * Base abstract class for all factory classes.
 */
abstract class Factory {
    protected $faker;
    protected $modelName;

    public function __construct() {
        $this->faker = FakerFactory::create();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    abstract protected function definition(): array;

    /**
     * Create a single record in the database.
     *
     * @return bool True if insert operation was successful.  Otherwise, 
     * we return false.
     */
    public function createOne(): bool {
        $data = $this->definition();
        return $this->insert($data, $this->modelName);
    }

    /**
     * Create multiple records in the database.
     *
     * @param int $count The number of records to create.
     * @return void
     */
    public function count(int $count): void {
        $i = 0;
        while($i < $count) {
            if($this->createOne()) $i++;
        }
    }

    /**
     * Insert data into the database table.
     *
     * @param array<string, mixed> $data
     * @return bool True if insert operation was successful.  Otherwise, 
     * we return false.
     */
    protected function insert(array $data, string $modelName): bool {
        $newModel = null;
        if(class_exists($modelName)) {
            $newModel = new $modelName();
        } else {
            console_error("The model {$newModel} does not exist");
        }

        foreach($data as $key => $value) {
            if(property_exists($modelName, $key)) {
                $newModel->$key = $value;
            }
        }

        try {
            if($newModel->save()) {
                console_info("Created record: " . json_encode($newModel));
                return true;
            } else {
                return false;
            }
        } catch(\Exception $e) {
            console_error("Database error " . $e->getMessage());
        }
        return false;
    }
}