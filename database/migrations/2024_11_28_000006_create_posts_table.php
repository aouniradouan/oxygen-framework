<?php

use Oxygen\Core\Database\Migration;

/**
 * Create Posts Table Migration
 * 
 * Blog posts with foreign key relationship to users.
 */
class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('posts', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('CASCADE');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('slug');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down()
    {
        $this->schema->dropTable('posts');
    }
}
