<?php

namespace Database\Factories;

use Oxygen\Core\Database\Factory;
use Oxygen\Models\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            // 'email' => $this->faker->unique()->safeEmail,
            'email' => strtolower($this->faker->userName() . uniqid() . '@oxygen.com'),
            'password' => password_hash('password', PASSWORD_DEFAULT), // password is "password"
            'role_id' => 2, // Default user role
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
}
