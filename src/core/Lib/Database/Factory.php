<?php
declare(strict_types=1);
namespace Core\Lib\Database;

use Core\Exceptions\FactorySeeder\FactorySeederException;
use Faker\Factory as FakerFactory;

/**
 * Base abstract class for all factory classes.
 */
abstract class Factory {
    /**
     * Array containing callbacks that are used after database record is 
     * successfully saved.
     *
     * @var array
     */
    protected array $afterCreatingCallbacks = [];

    /**
     * Value for how many insertions to be performed by factory.
     *
     * @var int
     */
    protected $count = 1;

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
        $this->configure();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    abstract protected function definition(): array;

    /**
     * Adds callback to afterCreatingCallbacks array.
     *
     * @param callable $callback The anonymous callback for afterCreating.
     * @return static
     */
    public function afterCreating(callable $callback): static {
        $this->afterCreatingCallbacks[] = $callback;
        return $this;
    }

    /**
     * Helper function for satisfying complexity or length requirements.
     *
     * @return string A string containing a random digit, a lower case 
     * character, an upper case character, and a special character.
     */
    protected function append(): string {
        $contents = $this->faker->randomDigit();
        $contents .= lcfirst($this->faker->randomLetter());
        $contents .= ucfirst($this->faker->randomLetter());
        $contents .= $this->faker->randomElement(['!', '@', '#', '$', '%', '^', '&', '*']);
        return $contents;
    }

    /**
     * Create a record(s) in the database.
     *
     * @return bool True if insert operation was successful.  Otherwise, 
     * we return false.
     */
    public function create(array $attributes = []) {
        if(env('APP_ENV') === 'production') {
            throw new FactorySeederException("Factories and seeders can only be run in development mode");
            return;
        }

        $results = [];

        for ($i = 0; $i < $this->count; $i++) {
            $data = $this->definition();

            foreach ($this->states as $state) {
                $extra = is_callable($state) ? $state($attributes) : $state;
                $data = array_merge($data, (array)$extra);
            }

            $data = array_merge($data, $attributes);
            $model = $this->insert($data, $this->modelName);

            if($model instanceof $this->modelName) {
                foreach($this->afterCreatingCallbacks as $callback) {
                    $callback($model);
                }
                if ($model) $results[] = $model;
            }

        }

        return count($results) === 1 ? $results[0] : $results;
    }

    /**
     * Configure function used for registering afterCreating callbacks.
     *
     * @return static
     */
    protected function configure(): static {
        return $this;
    }

    /**
     * Specify number of records to insert into the database.
     *
     * @param int $count The number of records to create.
     * @return static
     */
    public function count(int $count): static {
        $clone = clone $this;
        $clone->count = $count;
        return $clone;
    }

    /**
     * Returns instance of new child factory class.
     *
     * @param string $factoryName The name of the factory class.
     * @return object The child factory class.
     */
    public static function factory(...$params): object {
        return new static(...$params);
    }

    /**
     * Insert data into the database table.
     *
     * @param array<string, mixed> $data
     * @param string $modelName The name of the model to reference correct table.
     * @return object The model object if save is successful.  Otherwise, we 
     * return null.
     */
    protected function insert(array $data, string $modelName): ?object {
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
                return $newModel;
            } else {
                console_warning("Could not insert record: " . json_encode($newModel->getErrorMessages()));
            }
        } catch(\Exception $e) {
            console_error("Database error " . $e->getMessage());
        }
        return null;
    }

    /**
     * Registers sequence callbacks.
     *
     * @param array ...$sequence Sequence of values to alternate between for 
     * consecutive record creations when using count.
     * @return static
     */
    public function sequence(array ...$sequence): static {
        $this->states[] = new Sequence(...$sequence);
        return $this;
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