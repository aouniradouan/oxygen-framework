<?php

use Oxygen\Core\Database\Migration;

class CreatePostersTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('posters', function($table) {
            $table->id();
            $table->string('title', 255);
            $table->string('picture', 500);
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropTable('posters');
    }
}
