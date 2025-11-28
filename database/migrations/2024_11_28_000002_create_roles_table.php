<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Roles Table Migration
 * 
 * Stores user roles (Admin, User, Moderator, etc.)
 */
class CreateRolesTable extends Migration
{
    public function up()
    {
        $sql = "
            CREATE TABLE `roles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT NULL,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                INDEX `idx_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $this->execute($sql);

        // Insert default roles
        $this->execute("
            INSERT INTO roles (name, slug, description, created_at, updated_at) VALUES
            ('Administrator', 'admin', 'Full system access with all permissions', NOW(), NOW()),
            ('User', 'user', 'Standard user with basic permissions', NOW(), NOW()),
            ('Moderator', 'moderator', 'Can moderate content and manage users', NOW(), NOW())
        ");
    }

    public function down()
    {
        $this->dropTable('roles');
    }
}
