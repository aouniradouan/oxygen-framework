<?php

use Oxygen\Core\Database\Migration;

/**
 * Add Foreign Keys Migration
 * 
 * Adds foreign key constraints after all tables are created.
 */
class AddForeignKeys extends Migration
{
    public function up()
    {
        // Add foreign key from users.role_id -> roles.id
        $this->addForeignKey('users', 'role_id', 'roles', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('users', 'fk_users_role_id');
    }
}
