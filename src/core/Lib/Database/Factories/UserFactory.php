<?php
declare(strict_types=1);
namespace Core\Lib\Database\Factories;

use App\Models\Users;
use Core\Lib\Database\Factory;

/**
 * Factory for creating new user table records.
 */
class UserFactory extends Factory {
    protected string $modelName = Users::class;

    /**
     * Overrides default value for acl.
     *
     * @return static
     */
    public function admin(): static {
        return $this->state(function (array $data , array $attributes) {
            return [
                'acl' => json_encode(["Admin"])
            ];
        });
    }

    /**
     * Definition for UsersFactory.
     *
     * @return array
     */
    protected function definition(): array
    {
        $min = (int) env('PW_MIN_LENGTH');
        $max = max($min, (int) env('PW_MAX_LENGTH') - 5);
        $tempPassword = $this->faker->password($min, $max);
        $tempPassword .= $this->append();
    
        return [
            'username' => $this->faker->userName() . $this->append(),
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

    /**
     * Overrides default value for deleted.
     *
     * @return static
     */
    public function deleted(): static {
        return $this->state(function (array $data , array $attributes) {
            return [
                'deleted' => 1
            ];
        });
    }
    
    /**
     * Overrides default value for inactive.
     *
     * @return static
     */
    public function inactive(): static {
        return $this->state(function (array $data, array $attributes) {
            return [
                'inactive' => 1
            ];
        });
    }
    /**
     * Overrides default value for login_attempts.
     *
     * @return static
     */
    public function loginAttempts(): static {
        return $this->state(function (array $data , array $attributes) {
            return [
                'login_attempts' => 1
            ];
        });
    }

    /**
     * Overrides default value for resetPassword.
     *
     * @return static
     */
    public function resetPassword(): static {
        return $this->state(function (array $data , array $attributes) {
            return [
                'reset_password' => 1
            ];
        });
    }

    /**
     * State callback for generating profile images.
     *
     * @param int $count Number of images to create.
     * @return static
     */
    public function withImages(int $count = 2): static {
        return $this->afterCreating(function (Users $user) use ($count) {
            ProfileImageFactory::factory($user->id)->count($count)->create();
        });
    }
}