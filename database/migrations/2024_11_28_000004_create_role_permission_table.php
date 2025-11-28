<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Role Permission Pivot Table Migration
 * 
 * Links roles to permissions (many-to-many relationship)
 */
class CreateRolePermissionTable extends Migration
{
    public function up()
    {
        $sql = "
            CREATE TABLE `role_permission` (
                `role_id` INT NOT NULL,
                `permission_id` INT NOT NULL,
                PRIMARY KEY (`role_id`, `permission_id`),
                INDEX `idx_role_id` (`role_id`),
                INDEX `idx_permission_id` (`permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $this->execute($sql);

        // Assign all permissions to Admin role (role_id = 1)
        $this->execute("
            INSERT INTO role_permission (role_id, permission_id)
            SELECT 1, id FROM permissions
        ");

        // Assign basic permissions to User role (role_id = 2)
        $this->execute("
            INSERT INTO role_permission (role_id, permission_id)
            SELECT 2, id FROM permissions WHERE slug IN ('dashboard.view', 'content.view', 'content.create')
        ");

        // Assign moderate permissions to Moderator role (role_id = 3)
        $this->execute("
            INSERT INTO role_permission (role_id, permission_id)
            SELECT 3, id FROM permissions WHERE slug IN ('dashboard.view', 'content.view', 'content.create', 'content.edit', 'content.delete')
        ");
    }

    public function down()
    {
        $this->dropTable('role_permission');
    }
}
