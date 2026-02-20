<?php
declare(strict_types=1);
namespace Core\Lib\Database\Factories;

use App\Models\Users;
use Core\Lib\Database\Factory;

/**
 * Factory for creating new user table records.
 */
class UserFactory extends Factory {
    protected $modelName = Users::class;

    /**
     * Overrides default value for acl.
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

    /**
     * Overrides configure function from super class.
     *
     * @return static
     */
    protected function configure(): static {
        return $this->afterCreating(function (Users $user) {
            (new ProfileImageFactory($user->id))->count(2);
        });
    }

    /**
     * Definition for UsersFactory.
     *
     * @return array
     */
    public function definition(): array
    {
        $tempPassword = $this->faker->password((int)env('PW_MIN_LENGTH'), (int)env('PW_MAX_LENGTH') - 5);
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
     * Overrides default value for inactive.
     *
     * @return self
     */
    public function inactive(): self {
        return $this->state(function (array $attributes) {
            return [
                'inactive' => 1
            ];
        });
    }
    /**
     * Overrides default value for login_attempts.
     *
     * @return self
     */
    public function loginAttempts(): self {
        return $this->state(function (array $attributes) {
            return [
                'login_attempts' => 1
            ];
        });
    }

    /**
     * Overrides default value for resetPassword.
     *
     * @return self
     */
    public function resetPassword(): self {
        return $this->state(function (array $attributes) {
            return [
                'reset_password' => 1
            ];
        });
    }
}