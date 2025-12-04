<?php

use Oxygen\Core\Database\Migration;

class CreatePostersTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('posters', function($table) {
            $table->id();
            $table->text('title');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropTable('posters');
    }
}
