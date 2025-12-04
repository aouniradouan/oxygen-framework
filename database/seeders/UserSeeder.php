<?php

namespace Database\Seeders;

use Oxygen\Core\Database\Seeder;
use Oxygen\Models\User;
use Database\Factories\UserFactory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create 250 users using the factory
        $factory = new UserFactory();
        $factory->count(75000)->create();


        // Create a specific admin user
        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@oxygen.com',
        //     'password' => password_hash('password', PASSWORD_DEFAULT),
        //     'role_id' => 1, // Admin role
        //     'email_verified_at' => date('Y-m-d H:i:s'),
        // ]);
    }
}
