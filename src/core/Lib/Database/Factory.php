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
     * Undocumented function
     *
     * @return void
     */
    public function createOne(): void {
        $data = $this->definition();
        $this->insert($data);
    }

    public function count(int $count): void {
        for($i = 0; $i < $count; $i++) {
            $this->createOne();
        }
    }

    protected function insert(array $data): void {
        foreach($data as $key => $value) {
            if(property_exists($this->modelName, $key)) {
                $this->modelName->key = $value;
            }
        }
        $this->modelName->save();
    }
}