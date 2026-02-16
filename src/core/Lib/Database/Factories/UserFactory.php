<?php
namespace Core\Lib\Database\Factories;

use App\Models\Users;
use Core\Lib\Database\Factory;

/**
 * Factory for creating new user table records.
 */
class UserFactory extends Factory {
    protected $modelName = Users::class;

    /**
     * Undocumented function
     *
     * @return self
     */
    public function admin(): self {
        return $this->state(function (array $attributes) {
            return [
                'acl' => json_encode(["Admin"])
            ];
        });
    }

    public function definition(): array
    {
        $tempPassword = $this->faker->password(12,30);
        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->safeEmail(),
            'acl' => json_encode([""]),
            'password' => $tempPassword,
            'confirm' => $tempPassword,
            'fname' => $this->faker->firstName(),
            'lname' => $this->faker->lastName(),
            'description' => $this->faker->sentence(3),
            'inactive' => 0,
            'reset_password' => 0,
            'login_attempts' => 0,
            'deleted' => 0
        ];
    }

    public function inactive(): self {
        return $this->state(function (array $attributes) {
            return [
                'inactive' => 1
            ];
        });
    }
}