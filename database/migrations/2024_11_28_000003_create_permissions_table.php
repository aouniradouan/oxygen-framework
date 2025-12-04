<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Permissions Table Migration
 */
class CreatePermissionsTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('permissions', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('slug');
        });
    }

    public function down()
    {
        $this->schema->dropTable('permissions');
    }
}
