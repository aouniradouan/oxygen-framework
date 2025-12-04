<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Roles Table Migration
 */
class CreateRolesTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('roles', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('slug');
        });

        // Insert default roles
        $this->execute("INSERT INTO `roles` (`name`, `slug`, `description`, `created_at`) VALUES 
            ('Administrator', 'admin', 'Full access to all features', NOW()),
            ('User', 'user', 'Standard user access', NOW()),
            ('Moderator', 'moderator', 'Can moderate content', NOW())
        ");
    }

    public function down()
    {
        $this->schema->dropTable('roles');
    }
}
