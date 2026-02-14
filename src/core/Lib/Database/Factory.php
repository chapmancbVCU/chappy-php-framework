<?php

declare(strict_types=1);
namespace Core\Lib\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator;

abstract class Factory {
    protected Generator $faker;
    protected $modelName;
    public function __construct(string $modelName) {
        $this->faker = FakerFactory::create();
        $this->modelName = new $modelName;
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
     * @return void
     */
    public function createOne(): void {
        $data = $this->definition();
        $this->insert($data);
    }

    /**
     * Create multiple records in the database.
     *
     * @param int $count The number of records to create.
     * @return void
     */
    public function count(int $count): void {
        for($i = 0; $i < $count; $i++) {
            $this->createOne();
        }
    }

    /**
     * Insert data into the database table.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    protected function insert(array $data): void {
        foreach($data as $key => $value) {
            if(property_exists($this->modelName, $key)) {
                $this->modelName->key = $value;
            }
        }
        $this->modelName->save();
    }
}