# OxygenFramework - Advanced ORM Relationship System

## Overview

A professional, Laravel-inspired ORM relationship system with eager loading, query builder integration, and powerful collection methods.

## Features

✅ **Eager Loading** - Prevent N+1 queries  
✅ **Lazy Loading** - Load relationships on demand  
✅ **Query Builder** - Chain methods on relationships  
✅ **Collection Methods** - 30+ powerful array methods  
✅ **Pivot Tables** - Full many-to-many support  
✅ **Relationship Methods** - attach, detach, sync, toggle  

## Basic Usage

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
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Accessing Relationships

```php
// Lazy Loading (loads when accessed)
$user = User::find(1);
$posts = $user->posts; // Executes query here

// Eager Loading (loads upfront - NO N+1!)
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts; // No query! Already loaded
}

// Multiple Relationships
$users = User::with('posts', 'profile', 'roles')->get();

// Nested Eager Loading
$users = User::with('posts.comments')->get();

// Conditional Eager Loading
$users = User::with(['posts' => function($query) {
    $query->where('published', true)
          ->orderBy('created_at', 'desc')
          ->limit(5);
}])->get();
```

### Query Builder on Relationships

```php
$user = User::find(1);

// Add constraints to relationships
$publishedPosts = $user->posts()
    ->where('published', true)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Count relationships
$postCount = $user->posts()->count();

// Check if relationships exist
$hasPosts = $user->posts()->exists();

// Get first result
$latestPost = $user->posts()
    ->orderBy('created_at', 'desc')
    ->first();
```

### Many-to-Many Operations

```php
$user = User::find(1);

// Attach roles
$user->roles()->attach(1); // Attach role ID 1
$user->roles()->attach([2, 3]); // Attach multiple

// Attach with pivot data
$user->roles()->attach(1, ['assigned_at' => now()]);

// Detach roles
$user->roles()->detach(1); // Detach role ID 1
$user->roles()->detach(); // Detach all

// Sync roles (detach old, attach new)
$user->roles()->sync([1, 2, 3]);

// Toggle roles
$user->roles()->toggle([1, 2]); // Attach if not attached, detach if attached

// Access pivot data
$roles = $user->roles;
foreach ($roles as $role) {
    echo $role->pivot->assigned_at;
}

// With pivot columns
public function roles()
{
    return $this->belongsToMany(Role::class)
                ->withPivot('assigned_at', 'expires_at')
                ->withTimestamps();
}
```

### Collection Methods

```php
$users = User::all();

// Map
$names = $users->map(function($user) {
    return $user->name;
});

// Filter
$activeUsers = $users->filter(function($user) {
    return $user->status === 'active';
});

// Pluck
$ids = $users->pluck('id');
$emailsById = $users->pluck('email', 'id');

// First/Last
$first = $users->first();
$last = $users->last();

// Chunk
$chunks = $users->chunk(10);

// Sort
$sorted = $users->sortBy('created_at');
$sorted = $users->sortBy(function($user) {
    return $user->posts->count();
});

// Unique
$unique = $users->unique('email');

// Sum
$totalPosts = $users->sum(function($user) {
    return $user->posts->count();
});

// Contains
$hasJohn = $users->contains('name', 'John');
$hasActive = $users->contains(function($user) {
    return $user->status === 'active';
});

// Only/Except
$subset = $users->only([0, 1, 2]);
$remaining = $users->except([0, 1, 2]);

// To Array/JSON
$array = $users->toArray();
$json = $users->toJson();

// Count
$count = $users->count();

// Empty checks
if ($users->isEmpty()) {
    // No users
}

if ($users->isNotEmpty()) {
    // Has users
}
```

### Creating Related Models

```php
$user = User::find(1);

// Create related model
$post = $user->posts()->create([
    'title' => 'New Post',
    'content' => 'Content here'
]);

// Create multiple
$posts = $user->posts()->createMany([
    ['title' => 'Post 1', 'content' => 'Content 1'],
    ['title' => 'Post 2', 'content' => 'Content 2']
]);

// Save existing model
$post = new Post(['title' => 'Title', 'content' => 'Content']);
$user->posts()->save($post);

// Save multiple
$user->posts()->saveMany([$post1, $post2]);
```

### Associate/Dissociate (BelongsTo)

```php
$post = Post::find(1);
$user = User::find(2);

// Associate
$post->user()->associate($user);
// Now $post->user_id = 2

// Dissociate
$post->user()->dissociate();
// Now $post->user_id = null
```

## Advanced Examples

### Complex Queries

```php
// Users with at least 5 posts
$users = User::whereHas('posts', function($query) {
    $query->where('published', true);
}, '>=', 5)->get();

// Users without posts
$users = User::doesntHave('posts')->get();

// Load relationships after retrieval
$users = User::all();
$users->load('posts', 'profile');

// Lazy eager loading with constraints
$users->load(['posts' => function($query) {
    $query->where('published', true);
}]);
```

### Default Eager Loading

```php
class User extends Model
{
    // Always eager load these relationships
    protected $with = ['profile'];
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

// Profile is automatically loaded
$users = User::all(); // Includes profile
```

### Custom Foreign Keys

```php
// Custom foreign key names
public function posts()
{
    return $this->hasMany(Post::class, 'author_id');
}

public function user()
{
    return $this->belongsTo(User::class, 'author_id', 'id');
}

// Custom pivot table and keys
public function roles()
{
    return $this->belongsToMany(
        Role::class,
        'user_role_pivot', // table name
        'user_id',         // foreign key
        'role_id'          // related key
    );
}
```

## Performance Tips

### 1. Use Eager Loading

```php
// ❌ BAD - N+1 Problem
$users = User::all();
foreach ($users as $user) {
    echo $user->posts; // Query for each user!
}

// ✅ GOOD - Single Query
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts; // No query!
}
```

### 2. Load Only What You Need

```php
// Load specific columns
$users = User::with(['posts' => function($query) {
    $query->select('id', 'user_id', 'title');
}])->get();
```

### 3. Use Relationship Counting

```php
// Instead of loading all posts
$users = User::withCount('posts')->get();
echo $users[0]->posts_count; // No need to load posts
```

## Migration from Old System

### Old Way
```php
// Old trait
use Oxygen\Core\Traits\HasRelationships;

// Returns array
$posts = $user->posts(); // array

// No query builder
// Can't do: $user->posts()->where(...)
```

### New Way
```php
// New trait
use Oxygen\Core\Database\Concerns\HasRelationships;

// Returns Collection
$posts = $user->posts; // Collection object

// Full query builder
$posts = $user->posts()->where('published', true)->get();

// Eager loading
$users = User::with('posts')->get();
```

## What's Next?

Coming soon:
- `HasManyThrough` - Access distant relationships
- Polymorphic relationships (`morphTo`, `morphMany`, `morphToMany`)
- Relationship events
- Query scopes on relationships

---

**Your ORM is now professional-grade and ready for anything!** 🚀
