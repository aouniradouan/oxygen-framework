# OxygenFramework Documentation

Welcome to the complete OxygenFramework documentation! 🚀

## 📚 Documentation Index

### Core Documentation

1. **[API Reference](API_REFERENCE.md)** - Complete reference for all framework components
   - String Helper (Str)
   - Model & ORM
   - Validator & Validation Rules
   - Schema Builder
   - View & Twig Functions
   - Flash Messages
   - Storage System
   - Request & Response

2. **[Database & Migrations](DATABASE.md)** - Everything about the database layer
   - Creating Migrations
   - Schema Builder (OxygenSchema)
   - Models & ORM
   - Relationships (belongsTo, hasMany, hasOne, belongsToMany)
   - Soft Deletes
   - Query Builder
   - Pagination

3. **[CLI & Scaffold Command](CLI.md)** - Command-line tools and scaffolding
   - Scaffold Command Usage
### 1. Generate a Resource

```bash
php oxygen scaffold:resource
```

Follow the interactive prompts to generate a complete CRUD resource with:
- Migration
- Model with relationships
- Controller with validation
- Views with Tailwind CSS
- Routes

### 2. Run Migration

```bash
php oxygen migrate
```

### 3. Visit Your Resource

```
http://your-domain/your-resource
```

---

## 🎯 Key Features

### 100% Framework Components

OxygenFramework uses ONLY its own components:
- ✅ `Oxygen\Core\Support\Str` for string operations
- ✅ `Oxygen\Core\Database\OxygenSchema` for migrations
- ✅ `Oxygen\Core\Model` for ORM
- ✅ `Oxygen\Core\Validator` for validation
- ✅ `Oxygen\Core\Flash` for messages
- ✅ `Oxygen\Core\Storage\OxygenStorage` for file uploads

### Professional Scaffold

The scaffold command generates production-ready code:
- ✅ Full CRUD operations
- ✅ Validation with error messages
- ✅ Search & pagination
- ✅ Relationships with dropdowns
- ✅ Soft deletes support
- ✅ File upload handling
- ✅ Flash messages
- ✅ Responsive Tailwind CSS design

---

## 📖 Common Tasks

### Create a Model with Relationship

```php
<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    
    protected $fillable = ['title', 'content', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### Validate Request Data

```php
use Oxygen\Core\Validator;

$validator = Validator::make($request->all(), [
    'title' => 'required|string|max:255',
    'email' => 'required|email',
    'age' => 'integer|min:18',
]);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### Display Flash Messages

```twig
{{ flash_display()|raw }}
```

```php
use Oxygen\Core\Flash;

Flash::success('Operation successful!');
Flash::error('Something went wrong!');
Flash::warning('Please be careful!');
Flash::info('FYI: Something happened');
```

### Use Storage for File Uploads

```php
use Oxygen\Core\Storage\OxygenStorage;

$result = OxygenStorage::put($_FILES['avatar'], 'avatars');
// Returns: ['success' => true, 'path' => '...', 'url' => '...']
```

```twig
<img src="{{ storage_url('avatars/user123.jpg') }}">
```

---

## 🔧 Framework Components

### String Helper

```php
use Oxygen\Core\Support\Str;

Str::plural('post');      // "posts"
Str::singular('posts');   // "post"
Str::snake('PostTitle');  // "post_title"
Str::studly('post_title'); // "PostTitle"
```

### Validation Rules

| Rule | Description |
|------|-------------|
| `required` | Field must be present |
| `string` | Must be a string |
| `integer` | Must be an integer |
| `numeric` | Must be numeric |
| `email` | Must be valid email |
| `url` | Must be valid URL |
| `min:value` | Minimum length/value |
| `max:value` | Maximum length/value |
| `in:val1,val2` | Must be in list |
| `boolean` | Must be boolean |
| `date` | Must be valid date |
| `regex:pattern` | Must match regex |

### Schema Column Types

| Type | SQL Type | Usage |
|------|----------|-------|
| `id()` | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| `string($name, $length)` | VARCHAR | Text fields |
| `text($name)` | TEXT | Long text |
| `integer($name)` | INT | Numbers |
| `bigInteger($name)` | BIGINT | Foreign keys |
| `decimal($name, $p, $s)` | DECIMAL | Money, prices |
| `boolean($name)` | BOOLEAN | True/false |
| `date($name)` | DATE | Dates |
| `datetime($name)` | DATETIME | Date & time |
| `timestamp($name)` | TIMESTAMP | Timestamps |
| `enum($name, $options)` | ENUM | Fixed options |
| `json($name)` | JSON | JSON data |

### Twig Functions

| Function | Purpose | Example |
|----------|---------|---------|
| `storage($path)` | Storage file URL | `{{ storage('images/logo.png') }}` |
| `storage_url($path)` | Storage file URL (alias) | `{{ storage_url('videos/intro.mp4') }}` |
| `asset($path)` | Public asset URL | `{{ asset('css/app.css') }}` |
| `url($path)` | Application URL | `{{ url('about') }}` |
| `flash_display()` | Display flash messages | `{{ flash_display()|raw }}` |

---

## 💡 Best Practices

### 1. Always Use Framework Components

```php
// ✅ Good - uses Oxygen Str
use Oxygen\Core\Support\Str;
$plural = Str::plural('post');

// ❌ Bad - custom implementation
$plural = $this->customPlural('post');
```

### 2. Define Fillable Properties

```php
// ✅ Good
protected $fillable = ['title', 'content', 'user_id'];

// ❌ Bad - mass assignment vulnerability
protected $fillable = [];
```

### 3. Use Validation

```php
// ✅ Good
$validator = Validator::make($data, [
    'email' => 'required|email',
]);

// ❌ Bad - no validation
$email = $_POST['email'];
```

### 4. Use Relationships

```php
// ✅ Good
$user = $post->user();

// ❌ Bad
$user = User::find($post->user_id);
```

### 5. Use Pagination

```php
// ✅ Good
$posts = Post::paginate(15);

// ❌ Bad - loads everything
$posts = Post::all();
```

---

## 🆘 Getting Help

### Documentation

- Read the [API Reference](API_REFERENCE.md) for detailed component documentation
- Check [Database & Migrations](DATABASE.md) for ORM and query help
- See [CLI & Scaffold](CLI.md) for scaffold command usage

### Common Issues

**Problem:** Validation fails
**Solution:** Check validation rules match your data types

**Problem:** Relationship returns null
**Solution:** Verify foreign key exists and has a value

**Problem:** Migration fails
**Solution:** Check database connection and table doesn't exist

---

## 📝 Contributing

When contributing to OxygenFramework:

1. **Use ONLY framework components** - No custom helpers
2. **Follow PSR-12** - Coding standards
3. **Document everything** - Clear comments and docs
4. **Test thoroughly** - Ensure it works
5. **Keep it simple** - Clean, maintainable code

---

## 🎉 What's New in v4.0

### ScaffoldResourceCommand Refactored

- ✅ 100% OxygenFramework components
- ✅ Uses `Oxygen\Core\Support\Str` for all string operations
- ✅ Uses `OxygenSchema` for migrations
- ✅ Uses `Oxygen\Core\Validator` for validation
- ✅ Removed all custom helper methods
- ✅ Professional code quality
- ✅ Complete documentation

### New Documentation

- ✅ Complete API Reference
- ✅ Comprehensive Database Guide
- ✅ Detailed CLI Documentation
- ✅ Best Practices
- ✅ Troubleshooting

---

## 📄 License

OxygenFramework is open-source software created by **Redwan Aouni** and the Oxygen Community.

Made with ❤️ in Algeria 🇩🇿

---

**Happy Coding! 🚀**
