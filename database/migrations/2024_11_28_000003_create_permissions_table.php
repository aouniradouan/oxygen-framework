<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Permissions Table Migration
 * 
 * Stores granular permissions that can be assigned to roles
 */
class CreatePermissionsTable extends Migration
{
    public function up()
    {
        $sql = "
            CREATE TABLE `permissions` (
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

        // Insert default permissions
        $this->execute("
            INSERT INTO permissions (name, slug, description, created_at, updated_at) VALUES
            ('View Dashboard', 'dashboard.view', 'Access to dashboard area', NOW(), NOW()),
            ('Manage Users', 'users.manage', 'Create, edit, and delete users', NOW(), NOW()),
            ('Manage Roles', 'roles.manage', 'Create, edit, and delete roles', NOW(), NOW()),
            ('Manage Permissions', 'permissions.manage', 'Assign permissions to roles', NOW(), NOW()),
            ('View Content', 'content.view', 'View all content', NOW(), NOW()),
            ('Create Content', 'content.create', 'Create new content', NOW(), NOW()),
            ('Edit Content', 'content.edit', 'Edit existing content', NOW(), NOW()),
            ('Delete Content', 'content.delete', 'Delete content', NOW(), NOW())
        ");
    }

    public function down()
    {
        $this->dropTable('permissions');
    }
}
