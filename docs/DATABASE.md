# Database & Migrations - OxygenFramework

## Overview

OxygenFramework provides a powerful database layer with migrations, schema builder, and ORM capabilities.

---

## Migrations

### Creating Migrations

Migrations are stored in `database/migrations/` with timestamp prefixes.

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
            $table->text('content');
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropTable('posts');
    }
}
```

### Running Migrations

```bash
php oxygen migrate
```

---

## Schema Builder (OxygenSchema)

The schema builder provides a fluent API for defining database tables.

### Available Column Types

#### Numeric Types

```php
$table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
$table->integer('count');              // INT NOT NULL
$table->bigInteger('user_id');         // BIGINT NOT NULL
$table->decimal('price', 8, 2);        // DECIMAL(8,2) NOT NULL
$table->float('rating');               // FLOAT NOT NULL
$table->double('amount');              // DOUBLE NOT NULL
```

#### String Types

```php
$table->string('name', 255);           // VARCHAR(255) NOT NULL
$table->text('description');           // TEXT NOT NULL
```

#### Date/Time Types

```php
$table->date('published_date');        // DATE NOT NULL
$table->datetime('event_time');        // DATETIME NOT NULL
$table->timestamp('verified_at');      // TIMESTAMP NOT NULL
```

#### Special Types

```php
$table->boolean('is_active');          // BOOLEAN NOT NULL
$table->enum('status', ['draft', 'published']); // ENUM
$table->json('metadata');              // JSON NOT NULL
```

### Column Modifiers

```php
// Make column nullable
$table->string('email')->nullable();

// Add unique constraint
$table->string('username')->unique();

// Set default value
$table->integer('views')->default(0);
$table->boolean('active')->default(true);
```

### Special Helper Methods

```php
// Add created_at and updated_at timestamps
$table->timestamps();

// Add deleted_at for soft deletes
$table->softDeletes();
```

### Complete Example

```php
public function up()
{
    $this->schema->createTable('products', function($table) {
        $table->id();
        $table->string('name', 255);
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('stock')->default(0);
        $table->boolean('is_active')->default(true);
        $table->enum('status', ['draft', 'published', 'archived']);
        $table->bigInteger('category_id')->nullable();
        $table->json('attributes')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
```

---

## Models (ORM)

### Creating a Model

```php
<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'published_at'
    ];
}
```

### Basic CRUD Operations

#### Create

```php
// Create a new record
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is the content',
    'user_id' => 1
]);
```

#### Read

```php
// Get all records
$posts = Post::all();

// Find by ID
$post = Post::find(1);

// Query with WHERE
$posts = Post::where('user_id', 1);
$posts = Post::where('views', '>', 100);
$posts = Post::where('title', 'LIKE', '%Laravel%');

// WHERE IN
$posts = Post::whereIn('id', [1, 2, 3]);
```

#### Update

```php
// Update a record
Post::update(1, [
    'title' => 'Updated Title',
    'content' => 'Updated content'
]);
```

#### Delete

```php
// Delete a record
Post::delete(1);
```

### Pagination

```php
// Get paginated results (15 per page)
$posts = Post::paginate(15);

// Custom per page
$posts = Post::paginate(20);

// In view:
{{ posts.links()|raw }}
```

---

## Relationships

Models include the `HasRelationships` trait automatically.

### BelongsTo (Many-to-One)

```php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

// Usage:
$post = Post::find(1);
$user = $post->user();
```

### HasMany (One-to-Many)

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}

// Usage:
$user = User::find(1);
$posts = $user->posts();
```

### HasOne (One-to-One)

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }
}

// Usage:
$user = User::find(1);
$profile = $user->profile();
```

### BelongsToMany (Many-to-Many)

```php
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}

// Usage:
$post = Post::find(1);
$tags = $post->tags();
```

---

## Soft Deletes

Enable soft deletes to keep deleted records in the database.

### Setup

```php
<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;
use Oxygen\Core\Traits\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    
    protected $table = 'posts';
    protected $fillable = ['title', 'content'];
}
```

### Migration

```php
public function up()
{
    $this->schema->createTable('posts', function($table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
        $table->softDeletes(); // Adds deleted_at column
    });
}
```

### Usage

```php
// Soft delete (sets deleted_at timestamp)
Post::delete(1);

// Query only non-deleted records (automatic)
$posts = Post::all();

// Include soft-deleted records
$posts = Post::withTrashed();

// Get only soft-deleted records
$posts = Post::onlyTrashed();

// Permanently delete
Post::forceDelete(1);

// Restore soft-deleted record
Post::restore(1);
```

---

## Query Builder

### Basic Queries

```php
// Simple where
$users = User::where('status', 'active');

// With operators
$products = Product::where('price', '>', 100);
$products = Product::where('price', '<=', 50);

// LIKE queries
$users = User::where('name', 'LIKE', '%John%');

// Multiple conditions
$users = User::where('status', 'active')
             ->where('age', '>=', 18);
```

### Available Operators

- `=` - Equal
- `>` - Greater than
- `<` - Less than
- `>=` - Greater than or equal
- `<=` - Less than or equal
- `!=` - Not equal
- `<>` - Not equal
- `LIKE` - Pattern matching

---

## Best Practices

### 1. Always Define Fillable

```php
protected $fillable = ['title', 'content', 'user_id'];
```

This protects against mass-assignment vulnerabilities.

### 2. Use Relationships

```php
// ❌ Bad
$user_id = $post->user_id;
$user = User::find($user_id);

// ✅ Good
$user = $post->user();
```

### 3. Use Pagination

```php
// ❌ Bad - loads all records
$posts = Post::all();

// ✅ Good - paginated
$posts = Post::paginate(15);
```

### 4. Use Soft Deletes for Important Data

```php
use Oxygen\Core\Traits\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
}
```

### 5. Name Relationships Clearly

```php
// ❌ Unclear
public function u() { return $this->belongsTo(User::class); }

// ✅ Clear
public function author() { return $this->belongsTo(User::class, 'user_id'); }
```

---

## Common Patterns

### Search Functionality

```php
$search = $_GET['search'] ?? null;

if ($search) {
    $products = Product::where('name', 'LIKE', "%{$search}%");
} else {
    $products = Product::paginate(20);
}
```

### Filter by Status

```php
$status = $_GET['status'] ?? 'all';

if ($status !== 'all') {
    $posts = Post::where('status', $status);
} else {
    $posts = Post::all();
}
```

### Eager Loading Relationships

```php
// Get posts with their authors
$posts = Post::all();
foreach ($posts as $post) {
    $post->author = $post->user();
}
```

---

## Troubleshooting

### Migration Errors

**Problem:** Table already exists

**Solution:**
```bash
# Drop the table manually or use down() method
php oxygen migrate:rollback
```

### Model Not Found

**Problem:** Class 'Oxygen\Models\Post' not found

**Solution:** Ensure the model file exists at `app/Models/Post.php` with correct namespace.

### Relationship Returns Null

**Problem:** `$post->user()` returns null

**Solution:** Check that:
1. Foreign key column exists in database
2. Foreign key value is not null
3. Related record exists in the related table
