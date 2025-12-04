<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Users Table Migration
 * 
 * Users table with role support - FK added in separate migration.
 */
class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('users', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->foreignId('role_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->index('email');
            $table->index('role_id');
        });
    }

    public function down()
    {
        $this->schema->dropTable('users');
    }
}
