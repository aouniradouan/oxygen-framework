# Database and Models

This guide covers database configuration, the ORM (Object-Relational Mapping) system, CRUD operations, relationships, query building, and migrations.

## Table of Contents

- [Database Configuration](#database-configuration)
- [Model Basics](#model-basics)
- [CRUD Operations](#crud-operations)
- [Query Building](#query-building)
- [Relationships](#relationships)
- [Pagination](#pagination)
- [Migrations](#migrations)
- [Best Practices](#best-practices)

---

## Database Configuration

### Configuration File

**File:** `config/database.php`

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'oxygen'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'dsn' => 'mysql:host=...'
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database/database.sqlite',
            'prefix' => '',
            'dsn' => 'sqlite:...'
        ]
    ]
];
```

### Environment Variables

In `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oxygen
DB_USERNAME=root
DB_PASSWORD=
```

---

## Model Basics

### Creating Models

```bash
# Create model
php oxygen make:model Post

# Create model with migration
php oxygen make:model Post --migration
```

### Model Structure

```php
<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'content', 'user_id', 'status'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean'
    ];
    protected $dates = ['created_at', 'updated_at', 'published_at'];
}
```

### Model Properties

- **$table** - Database table name (auto-guessed from class name if not set)
- **$primaryKey** - Primary key column (default: 'id')
- **$fillable** - Mass-assignable attributes (whitelist)
- **$hidden** - Attributes hidden from array/JSON output
- **$casts** - Attribute type casting
- **$dates** - Attributes treated as dates

### Table Name Guessing

If `$table` is not set, the framework guesses the table name:

```php
// Model: Post -> Table: posts
// Model: User -> Table: users
// Model: BlogPost -> Table: blog_posts
```

---

## CRUD Operations

### Create

```php
// Create new record
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is the content',
    'user_id' => 1,
    'status' => 'published'
]);

// Using model instance
$post = new Post();
$post->title = 'My First Post';
$post->content = 'This is the content';
$post->save();
```

### Read

```php
// Get all records
$posts = Post::all();

// Find by ID
$post = Post::find(1);

// Find or fail
$post = Post::find(1);
if (!$post) {
    // Handle not found
}
```

### Update

```php
// Update by ID
Post::update(1, [
    'title' => 'Updated Title',
    'status' => 'draft'
]);

// Update instance
$post = Post::find(1);
$post->title = 'Updated Title';
$post->save();
```

### Delete

```php
// Delete by ID
Post::delete(1);

// Delete instance
$post = Post::find(1);
$post->delete();
```

---

## Query Building

### Where Clauses

```php
// Simple where
$posts = Post::where('status', '=', 'published')->get();

// Where with different operators
$posts = Post::where('views', '>', 100)->get();
$posts = Post::where('title', 'LIKE', '%search%')->get();

// Multiple where clauses
$posts = Post::where('status', '=', 'published')
             ->where('user_id', '=', 1)
             ->get();
```

### Where In

```php
$posts = Post::whereIn('status', ['published', 'draft'])->get();
```

### Ordering

```php
// Order by
$posts = Post::where('status', '=', 'published')
             ->orderBy('created_at', 'DESC')
             ->get();
```

### Limiting

```php
// Limit results
$posts = Post::where('status', '=', 'published')
             ->limit(10)
             ->get();
```

### Counting

```php
// Count results
$count = Post::where('status', '=', 'published')->count();
```

---

## Relationships

The framework supports relationships through the HasRelationships trait.

### Defining Relationships

```php
use Oxygen\Core\Model;
use Oxygen\Core\Traits\HasRelationships;

class Post extends Model
{
    use HasRelationships;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class User extends Model
{
    use HasRelationships;
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Comment extends Model
{
    use HasRelationships;
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### Eager Loading

```php
// Load with relationships
$posts = Post::with('user')->get();

// Multiple relationships
$posts = Post::with(['user', 'comments'])->get();

// Access relationship data
foreach ($posts as $post) {
    echo $post->title;
    echo $post->user->name;
    foreach ($post->comments as $comment) {
        echo $comment->content;
    }
}
```

### Relationship Types

**belongsTo** - One-to-one (inverse)

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

**hasMany** - One-to-many

```php
public function posts()
{
    return $this->hasMany(Post::class);
}
```

---

## Pagination

```php
// Paginate results (15 per page)
$posts = Post::paginate(15);

// Custom per page
$posts = Post::paginate(20);

// With where clause
$posts = Post::where('status', '=', 'published')->paginate(15);

// Access pagination data
$posts->items;        // Current page items
$posts->total;        // Total items
$posts->currentPage;  // Current page number
$posts->lastPage;     // Last page number
$posts->perPage;      // Items per page
```

### In Templates

```twig
{% for post in posts.items %}
    <article>{{ post.title }}</article>
{% endfor %}

<div class="pagination">
    {% if posts.currentPage > 1 %}
        <a href="?page={{ posts.currentPage - 1 }}">Previous</a>
    {% endif %}
    
    Page {{ posts.currentPage }} of {{ posts.lastPage }}
    
    {% if posts.currentPage < posts.lastPage %}
        <a href="?page={{ posts.currentPage + 1 }}">Next</a>
    {% endif %}
</div>
```

---

## Migrations

### Creating Migrations

```bash
php oxygen make:migration create_posts_table
```

### Migration Structure

```php
<?php

use Oxygen\Core\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->schema->create('posts', function($table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->integer('user_id');
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropIfExists('posts');
    }
}
```

### Column Types

```php
$table->id();                          // Auto-increment ID
$table->string('name');                // VARCHAR(255)
$table->string('name', 100);           // VARCHAR(100)
$table->text('description');           // TEXT
$table->integer('count');              // INTEGER
$table->bigInteger('big_count');       // BIGINT
$table->float('price');                // FLOAT
$table->decimal('amount', 8, 2);       // DECIMAL(8,2)
$table->boolean('is_active');          // BOOLEAN
$table->date('birth_date');            // DATE
$table->datetime('published_at');      // DATETIME
$table->timestamp('created_at');       // TIMESTAMP
$table->timestamps();                  // created_at & updated_at
```

### Column Modifiers

```php
$table->string('email')->nullable();
$table->string('status')->default('active');
$table->integer('order')->unsigned();
```

### Running Migrations

```bash
# Run all pending migrations
php oxygen migrate

# Rollback last migration
php oxygen migrate:rollback
```

---

## Best Practices

### 1. Use Mass Assignment Protection

```php
// Define fillable attributes
protected $fillable = ['title', 'content', 'user_id'];

// Or define guarded attributes
protected $guarded = ['id', 'created_at', 'updated_at'];
```

### 2. Hide Sensitive Attributes

```php
protected $hidden = ['password', 'remember_token', 'api_key'];
```

### 3. Use Type Casting

```php
protected $casts = [
    'is_active' => 'boolean',
    'price' => 'float',
    'metadata' => 'array',
    'published_at' => 'datetime'
];
```

### 4. Eager Load Relationships

```php
// Good - one query per relationship
$posts = Post::with(['user', 'comments'])->get();

// Avoid - N+1 query problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // Separate query for each post
}
```

### 5. Use Query Scopes

```php
class Post extends Model
{
    public static function published()
    {
        return static::where('status', '=', 'published');
    }
}

// Usage
$posts = Post::published()->get();
```

### 6. Validate Before Saving

```php
$validator = Validator::make($data, [
    'title' => 'required|string|max:255',
    'content' => 'required',
    'user_id' => 'required|integer'
]);

if ($validator->fails()) {
    // Handle validation errors
}

Post::create($validator->validated());
```

---

## See Also

- [Migrations](MIGRATIONS.md)
- [Query Builder](QUERY_BUILDER.md)
- [Relationships](RELATIONSHIPS.md)
