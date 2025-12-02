# Migrations & Database Relationships Guide

## Overview

OxygenFramework provides a powerful and intuitive migration system with excellent support for database relationships and foreign keys.

---

## Creating Migrations

### Basic Migration

```php
<?php

use Oxygen\Core\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->schema->createTable('posts', function($table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->dropTable('posts');
    }
}
```

---

## Schema Builder Methods

### Column Types

#### Numeric Types
```php
$table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
$table->integer('count');              // INT NOT NULL
$table->bigInteger('user_id');         // BIGINT NOT NULL
$table->unsignedBigInteger('user_id'); // BIGINT UNSIGNED NOT NULL
$table->unsignedInteger('count');      // INT UNSIGNED NOT NULL
$table->decimal('price', 8, 2);        // DECIMAL(8,2) NOT NULL
$table->float('rating');               // FLOAT NOT NULL
$table->double('amount');              // DOUBLE NOT NULL
```

#### String Types
```php
$table->string('name', 255);           // VARCHAR(255) NOT NULL
$table->text('description');           // TEXT NOT NULL
$table->text('content')->nullable();   // TEXT NULL
```

#### Date/Time Types
```php
$table->date('published_date');        // DATE NOT NULL
$table->datetime('event_time');        // DATETIME NOT NULL
$table->timestamp('verified_at');     // TIMESTAMP NOT NULL
$table->timestamps();                  // created_at, updated_at
$table->softDeletes();                 // deleted_at
```

#### Special Types
```php
$table->boolean('is_active');          // TINYINT(1) NOT NULL DEFAULT 0
$table->enum('status', ['draft', 'published']); // ENUM
$table->json('metadata');              // JSON NOT NULL
```

### Column Modifiers

```php
$table->string('email')->unique();           // Add UNIQUE constraint
$table->string('name')->nullable();          // Allow NULL
$table->integer('count')->default(0);        // Set default value
$table->string('status')->default('active'); // Default string
```

---

## Foreign Keys & Relationships

### Method 1: Using `foreignId()` and `constrained()`

```php
$this->schema->createTable('posts', function($table) {
    $table->id();
    $table->string('title');
    $table->foreignId('user_id')
          ->constrained('users')
          ->onDelete('cascade');
    $table->timestamps();
});
```

### Method 2: Using `foreign()` Method

```php
$this->schema->createTable('comments', function($table) {
    $table->id();
    $table->text('content');
    $table->bigInteger('post_id')->unsigned();
    $table->bigInteger('user_id')->unsigned();
    
    $table->foreign('post_id')
          ->references('id')
          ->on('posts')
          ->onDelete('cascade');
    
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');
});
```

### Method 3: Using Migration Helper Methods

```php
public function up()
{
    $this->schema->createTable('comments', function($table) {
        $table->id();
        $table->text('content');
        $table->bigInteger('post_id')->unsigned();
        $table->bigInteger('user_id')->unsigned();
        $table->timestamps();
    });
    
    // Add foreign keys after table creation
    $this->addForeignKey('comments', 'post_id', 'posts', 'id', 'CASCADE', 'CASCADE');
    $this->addForeignKey('comments', 'user_id', 'users', 'id', 'CASCADE', 'CASCADE');
}
```

---

## Pivot Tables (Many-to-Many)

### Easy Method: `createPivotTable()`

```php
public function up()
{
    // Automatically creates pivot table with foreign keys
    $this->createPivotTable('posts', 'tags');
    // Creates: post_tag table with post_id and tag_id
}
```

### Manual Method

```php
public function up()
{
    $this->schema->createTable('post_tag', function($table) {
        $table->bigInteger('post_id')->unsigned();
        $table->bigInteger('tag_id')->unsigned();
        $table->primary(['post_id', 'tag_id']);
        $table->index('post_id');
        $table->index('tag_id');
    });
    
    $this->addForeignKey('post_tag', 'post_id', 'posts', 'id');
    $this->addForeignKey('post_tag', 'tag_id', 'tags', 'id');
}
```

---

## Indexes

### Adding Indexes

```php
// Single column index
$table->index('user_id');

// Named index
$this->addIndex('posts', 'slug', 'idx_posts_slug');

// Composite index
$this->addIndex('comments', ['post_id', 'user_id'], 'idx_post_user');
```

### Dropping Indexes

```php
$this->dropIndex('posts', 'idx_posts_slug');
```

---

## Modifying Tables

### Adding Columns

```php
public function up()
{
    $this->addColumn('users', 'phone', 'VARCHAR(20) NULL');
}
```

### Dropping Columns

```php
public function down()
{
    $this->dropColumn('users', 'phone');
}
```

### Modifying Columns

```php
public function up()
{
    $this->modifyColumn('users', 'email', 'VARCHAR(255) NOT NULL UNIQUE');
}
```

### Renaming Columns

```php
public function up()
{
    $this->renameColumn('users', 'old_name', 'new_name');
}
```

---

## Model Relationships

### Defining Relationships

```php
use Oxygen\Core\Model;

class User extends Model
{
    protected $fillable = ['name', 'email'];
    
    // One-to-One
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    
    // One-to-Many
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    // Many-to-Many
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];
    
    // Inverse Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Many-to-Many
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

### Using Relationships

```php
// Lazy Loading
$user = User::find(1);
$posts = $user->posts; // Automatically loads

// Eager Loading (prevents N+1 queries)
$users = User::with('posts', 'profile')->get();

// Nested Eager Loading
$users = User::with('posts.comments')->get();

// Query Relationships
$user->posts()->where('published', true)->get();
$user->posts()->orderBy('created_at', 'desc')->limit(5)->get();

// Create Related Models
$post = $user->posts()->create([
    'title' => 'New Post',
    'content' => 'Content here'
]);

// Many-to-Many Operations
$user->roles()->attach($roleId);
$user->roles()->detach($roleId);
$user->roles()->sync([1, 2, 3]);
$user->roles()->toggle([1, 2]);
```

---

## Complete Example

```php
<?php

use Oxygen\Core\Database\Migration;

class CreateBlogTables extends Migration
{
    public function up()
    {
        // Posts table
        $this->schema->createTable('posts', function($table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content')->nullable();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->boolean('published')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('slug');
            $table->index('published');
        });
        
        // Tags table
        $this->schema->createTable('tags', function($table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });
        
        // Post-Tag pivot table
        $this->createPivotTable('posts', 'tags');
        
        // Comments table
        $this->schema->createTable('comments', function($table) {
            $table->id();
            $table->text('content');
            $table->foreignId('post_id')
                  ->constrained('posts')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('comments')
                  ->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['post_id', 'user_id']);
        });
    }

    public function down()
    {
        $this->dropTable('comments');
        $this->dropTable('post_tag');
        $this->dropTable('tags');
        $this->dropTable('posts');
    }
}
```

---

## Best Practices

1. **Always define foreign keys** - Ensures data integrity
2. **Use cascade deletes** - Automatically clean up related records
3. **Add indexes** - On foreign keys and frequently queried columns
4. **Use pivot tables** - For many-to-many relationships
5. **Test migrations** - Always test both `up()` and `down()` methods
6. **Use timestamps** - `$table->timestamps()` for created_at/updated_at
7. **Use soft deletes** - `$table->softDeletes()` for deleted_at

---

## Running Migrations

```bash
# Run all pending migrations
php oxygen migrate

# Rollback last batch
php oxygen migrate:rollback

# Rollback all migrations
php oxygen migrate:reset

# Refresh (rollback + migrate)
php oxygen migrate:refresh
```

