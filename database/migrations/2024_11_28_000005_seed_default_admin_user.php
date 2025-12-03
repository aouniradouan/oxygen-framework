<?php

use Oxygen\Core\Database\Migration;

/**
 * Seed Default Admin User
 * 
 * Creates a default admin user for initial system access
 */
class SeedDefaultAdminUser extends Migration
{
    public function up()
    {
        // Create default admin user
        // Email: admin@oxygen.local
        // Password: password
        $hashedPassword = password_hash('password', PASSWORD_BCRYPT);

        $this->execute("
            INSERT INTO users (name, email, password, role_id, email_verified_at, created_at, updated_at)
            VALUES (
                'Administrator',
                'admin@oxygen.local',
                '{$hashedPassword}',
                1,
                NOW(),
                NOW(),
                NOW()
            )
        ");
    }

    public function down()
    {
        $this->execute("DELETE FROM users WHERE email = 'admin@oxygen.local'");
    }
}
