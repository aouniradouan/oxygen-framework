<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Users Table Migration
 * 
 * This is a default migration that creates the users table with role support
 */
class CreateUsersTable extends Migration
{
    public function up()
    {
        $sql = "
            CREATE TABLE `users` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `role_id` INT NULL DEFAULT 2,
                `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
                `remember_token` VARCHAR(100) NULL DEFAULT NULL,
                `created_at` TIMESTAMP NULL DEFAULT NULL,
                `updated_at` TIMESTAMP NULL DEFAULT NULL,
                INDEX `idx_email` (`email`),
                INDEX `idx_role_id` (`role_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $this->execute($sql);
    }

    public function down()
    {
        $this->dropTable('users');
    }
}
