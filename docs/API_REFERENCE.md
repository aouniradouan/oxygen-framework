# OxygenFramework - Complete API Reference

## Core Components Overview

OxygenFramework provides a comprehensive set of components for building modern PHP applications. This document covers all available classes, methods, and their usage.

---

## String Helper (`Oxygen\Core\Support\Str`)

The `Str` class provides string manipulation utilities.

### Available Methods

#### `Str::plural($value)`
Convert a word to its plural form.

```php
use Oxygen\Core\Support\Str;

Str::plural('post');      // "posts"
Str::plural('category');  // "categories"
Str::plural('person');    // "people"
```

#### `Str::singular($value)`
Convert a word to its singular form.

```php
Str::singular('posts');      // "post"
Str::singular('categories'); // "category"
```

#### `Str::snake($value)`
Convert a string to snake_case.

```php
Str::snake('PostTitle');     // "post_title"
Str::snake('userProfile');   // "user_profile"
```

#### `Str::studly($value)`
Convert a string to StudlyCase.

```php
Str::studly('post_title');   // "PostTitle"
Str::studly('user-profile'); // "UserProfile"
```

---

## Model (`Oxygen\Core\Model`)

Base model class for database interactions with ORM capabilities.

### Properties

```php
protected $table = 'table_name';      // Table name
protected $primaryKey = 'id';          // Primary key column
protected $fillable = [];              // Mass-assignable attributes
```

### Methods

#### `Model::all()`
Get all records from the table.

```php
$posts = Post::all();
```

#### `Model::find($id)`
Find a record by primary key.

```php
$post = Post::find(1);
```

#### `Model::create(array $data)`
Create a new record.

```php
$post = Post::create([
    'title' => 'My Post',
    'content' => 'Post content'
]);
```

#### `Model::update($id, array $data)`
Update an existing record.

```php
Post::update(1, [
    'title' => 'Updated Title'
]);
```

#### `Model::delete($id)`
Delete a record.

```php
Post::delete(1);
```

#### `Model::where($column, $operator, $value)`
Query records with WHERE clause.

```php
// Simple where
$posts = Post::where('status', 'published');

// With operator
$posts = Post::where('views', '>', 100);

// LIKE query
$posts = Post::where('title', 'LIKE', '%Laravel%');
```

**Supported Operators:** `=`, `>`, `<`, `>=`, `<=`, `LIKE`, `!=`, `<>`

#### `Model::whereIn($column, array $values)`
Query records where column is in array.

```php
$posts = Post::whereIn('id', [1, 2, 3]);
```

#### `Model::paginate($perPage = 15)`
Get paginated results.

```php
$posts = Post::paginate(20);

// In view:
{{ posts.links()|raw }}
```

---

## Relationships (`Oxygen\Core\Traits\HasRelationships`)

Models automatically include relationship support.

### `belongsTo($related, $foreignKey = null)`
Define a belongs-to relationship.

```php
class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### `hasMany($related, $foreignKey = null)`
Define a has-many relationship.

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}
```

### `hasOne($related, $foreignKey = null)`
Define a has-one relationship.

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }
}
```

### `belongsToMany($related, $pivotTable = null)`
Define a many-to-many relationship.

```php
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}
```

---

## Validator (`Oxygen\Core\Validator`)

Comprehensive validation system.

### Available Validation Rules

| Rule | Description | Example |
|------|-------------|---------|
| `required` | Field must be present and not empty | `'title' => 'required'` |
| `string` | Must be a string | `'name' => 'string'` |
| `integer` | Must be an integer | `'age' => 'integer'` |
| `numeric` | Must be numeric | `'price' => 'numeric'` |
| `email` | Must be valid email | `'email' => 'email'` |
| `url` | Must be valid URL | `'website' => 'url'` |
| `min:value` | Minimum length/value | `'password' => 'min:8'` |
| `max:value` | Maximum length/value | `'title' => 'max:255'` |
| `in:val1,val2` | Must be in list | `'status' => 'in:draft,published'` |
| `boolean` | Must be boolean | `'active' => 'boolean'` |
| `date` | Must be valid date | `'published_at' => 'date'` |
| `regex:pattern` | Must match regex | `'code' => 'regex:/^[A-Z]{3}$/'` |

### Usage

```php
use Oxygen\Core\Validator;

$validator = Validator::make($request->all(), [
    'title' => 'required|string|max:255',
    'email' => 'required|email',
    'age' => 'integer|min:18',
    'status' => 'in:draft,published'
]);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Handle errors
}

$validated = $validator->validated();
```

---

## Schema Builder (`Oxygen\Core\Database\OxygenSchema`)

Fluent API for building database schemas.

### Column Types

```php
$table->id();                          // Auto-increment ID
$table->string('name', 255);           // VARCHAR
$table->text('description');           // TEXT
$table->integer('count');              // INT
$table->bigInteger('user_id');         // BIGINT
$table->decimal('price', 8, 2);        // DECIMAL(8,2)
$table->float('rating');               // FLOAT
$table->double('amount');              // DOUBLE
$table->boolean('active');             // BOOLEAN
$table->date('published_date');        // DATE
$table->datetime('created_at');        // DATETIME
$table->timestamp('updated_at');       // TIMESTAMP
$table->enum('status', ['draft', 'published']); // ENUM
$table->json('metadata');              // JSON
```

### Column Modifiers

```php
$table->string('email')->nullable();   // Allow NULL
$table->string('code')->unique();      // Add UNIQUE constraint
$table->integer('count')->default(0);  // Set default value
```

### Special Methods

```php
$table->timestamps();                  // Adds created_at & updated_at
$table->softDeletes();                 // Adds deleted_at
```

---

## View & Twig Functions

### Available Twig Functions

#### `storage($path)` / `storage_url($path)`
Get storage file URL.

```twig
<img src="{{ storage('images/logo.png') }}">
<video src="{{ storage_url('videos/intro.mp4') }}"></video>
```

#### `asset($path)`
Get public asset URL.

```twig
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<script src="{{ asset('js/app.js') }}"></script>
```

#### `url($path)`
Generate application URL.

```twig
<a href="{{ url('about') }}">About</a>
```

#### `flash_display()`
Display flash messages.

```twig
{{ flash_display()|raw }}
```

---

## Flash Messages (`Oxygen\Core\Flash`)

### Methods

```php
use Oxygen\Core\Flash;

Flash::success('Operation successful!');
Flash::error('Something went wrong!');
Flash::warning('Please be careful!');
Flash::info('FYI: Something happened');
```

### Display in Views

```twig
{{ flash_display()|raw }}
```

---

## Storage (`Oxygen\Core\Storage\OxygenStorage`)

File storage system with local and S3 support.

### Methods

#### `OxygenStorage::put($file, $path, $name = null)`
Store a file.

```php
use Oxygen\Core\Storage\OxygenStorage;

$result = OxygenStorage::put($_FILES['avatar'], 'avatars');
// Returns: ['success' => true, 'path' => '...', 'url' => '...']
```

#### `OxygenStorage::url($path)`
Get file URL.

```php
$url = OxygenStorage::url('avatars/user123.jpg');
```

#### `OxygenStorage::exists($path)`
Check if file exists.

```php
if (OxygenStorage::exists('avatars/user.jpg')) {
    // File exists
}
```

#### `OxygenStorage::delete($path)`
Delete a file.

```php
OxygenStorage::delete('avatars/old-avatar.jpg');
```

#### `OxygenStorage::get($path)`
Get file contents.

```php
$contents = OxygenStorage::get('documents/file.txt');
```

---

## Request & Response

### Request (`Oxygen\Core\Request`)

```php
use Oxygen\Core\Request;

$request = $this->app->make(Request::class);

$all = $request->all();           // All input
$name = $request->get('name');    // Single input
$file = $request->file('avatar'); // File upload
```

### Response (`Oxygen\Core\Response`)

```php
use Oxygen\Core\Response;

Response::redirect('/home');
Response::json(['status' => 'success']);
```

---

## Console Commands

### Creating Commands

```php
namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

class MyCommand extends Command
{
    public function execute($arguments)
    {
        $this->info("Info message");
        $this->success("Success message");
        $this->warning("Warning message");
        $this->error("Error message");
        
        $answer = $this->ask("Question?", "default");
    }
}
```

---

## Best Practices

1. **Always use framework components** - Don't reinvent the wheel
2. **Use `Str` helper** for string operations
3. **Use `Validator`** for all input validation
4. **Use relationships** instead of manual joins
5. **Use `storage_url()`** for file paths in views
6. **Use `Flash`** for user feedback
7. **Follow PSR-12** coding standards
