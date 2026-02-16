<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Faker\Factory as FakerFactory;

/**
 * Base abstract class for all factory classes.
 */
abstract class Factory {
    /**
     * Instance of Faker\Factory object.
     *
     * @var FactoryFaker
     */
    protected $faker;

    /**
     * The model used by the child factory class.
     *
     * @var string
     */
    protected $modelName;

    /**
     * States for overriding definition.
     *
     * @var array
     */
    protected array $states = [];

    /**
     * Constructor for Faker class.  Should be called by any child class that 
     * overrides this constructor.
     */
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
    public function createOne(array $attributes = []): bool {
        $data = $this->getComputedAttributes($attributes);
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
     * Merges any overrides, attributes, and definitions into a single array.
     *
     * @param array $overrides Any overrides for database values.  Must be an 
     * associative array.
     * @return array The combined array.
     */
    protected function getComputedAttributes(array $overrides): array {
        $attributes = $this->definition();
        foreach($this->states as $state) {
            $attributes = array_merge($attributes, $state($attributes));
        }

        return array_merge($attributes, $overrides);
    }

    /**
     * Returns instance of new child factory class.
     *
     * @param string $factoryName The name of the factory class.
     * @return object The child factory class.
     */
    public static function factory(string $factoryName): object {
        $newFactory = null;
        if(class_exists($factoryName)) {
            $newFactory = new $factoryName();
        }
        return $newFactory;
    }

    /**
     * Insert data into the database table.
     *
     * @param array<string, mixed> $data
     * @param string $modelName The name of the model to reference correct table.
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
                console_warning("Could not insert record.  Ensure validation passes");
            }
        } catch(\Exception $e) {
            console_error("Database error " . $e->getMessage());
        }
        return false;
    }

    /**
     * Manages state for values of keys that should be overridden by state 
     * functions.
     *
     * @param callable $state The anonymous function for overriding a value 
     * for a specified key.
     * @return self
     */
    public function state(callable $state): self {
        $this->states[] = $state;
        return $this;
    }
}