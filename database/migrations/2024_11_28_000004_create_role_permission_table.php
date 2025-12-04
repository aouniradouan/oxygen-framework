<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Role Permission Pivot Table Migration
 * 
 * Many-to-many relationship between roles and permissions.
 */
class CreateRolePermissionTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('role_permission', function ($table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('CASCADE');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('CASCADE');
            $table->primary(['role_id', 'permission_id']);
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    public function down()
    {
        $this->schema->dropTable('role_permission');
    }
}
