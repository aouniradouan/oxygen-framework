# Contributing to OxygenFramework

Welcome! We're thrilled that you want to contribute to OxygenFramework. This guide will help you understand our codebase and make your first contribution.

## 🌟 Why Contribute?

OxygenFramework aims to be one of the best PHP frameworks by combining:
- **Security-first design** - Built-in XSS, CSRF, and SQL injection protection
- **Developer experience** - Powerful generators and intuitive APIs
- **Modern architecture** - Clean code, PSR standards, and best practices

## 📋 Table of Contents

1. [Getting Started](#getting-started)
2. [Understanding the Architecture](#understanding-the-architecture)
3. [Core Components](#core-components)
4. [Contribution Workflow](#contribution-workflow)
5. [Coding Standards](#coding-standards)
6. [Testing Guidelines](#testing-guidelines)
7. [Documentation](#documentation)

---

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL/MariaDB
- Basic understanding of MVC pattern

### Local Setup

```bash
# Clone the repository
git clone https://github.com/your-org/oxygenframework.git
cd oxygenframework

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Configure database in .env
# DB_HOST=localhost
# DB_NAME=oxygen
# DB_USER=root
# DB_PASS=

# Run migrations
php oxygen migrate

# Start development server
php -S localhost:8000 -t public
```

---

## 🏗️ Understanding the Architecture

OxygenFramework follows a **clean MVC architecture** with dependency injection.

### Directory Structure

```
oxygenframework/
├── app/
│   ├── Console/          # CLI commands
│   ├── Controllers/      # HTTP controllers
│   ├── Core/            # Framework core
│   ├── Middleware/      # HTTP middleware
│   └── Models/          # Database models
├── config/              # Configuration files
├── database/
│   └── migrations/      # Database migrations
├── public/              # Web root
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript
│   ├── lang/           # Translations
│   └── views/          # Twig templates
├── routes/             # Route definitions
└── storage/            # File uploads, logs
```

### Request Lifecycle

```
1. public/index.php
   ↓
2. bootstrap/app.php (Container setup)
   ↓
3. Router matches route
   ↓
4. Middleware pipeline
   ↓
5. Controller action
   ↓
6. Response sent
```

---

## 🔧 Core Components

### 1. **Dependency Container** (`app/Core/Container.php`)

The heart of the framework. Manages all dependencies.

**How it works:**
```php
// Register a service
$container->bind('database', function() {
    return new Database();
});

// Resolve a service
$db = $container->make('database');

// Singleton (shared instance)
$container->singleton('auth', function() {
    return new Auth();
});
```

**When to modify:**
- Adding new core services
- Changing service resolution logic
- Adding auto-wiring features

### 2. **Router** (`app/Core/Router.php`)

Handles HTTP routing with regex patterns.

**How it works:**
```php
// Define a route
Route::get($router, '/users/(\d+)', 'UserController@show');

// Route parameters are passed to controller
public function show($id) {
    // $id contains the captured \d+
}
```

**When to modify:**
- Adding new HTTP methods
- Implementing route caching
- Adding route groups/prefixes

### 3. **Request** (`app/Core/Request.php`)

Handles HTTP requests with security features.

**Key methods:**
```php
// Get all input
$data = $request->all();

// Get specific input
$email = $request->input('email');

// Clean input (XSS protection)
$clean = $request->clean();

// Validate input
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8'
]);
```

**When to modify:**
- Adding new validation rules
- Improving XSS protection
- Adding file upload features

### 4. **Database** (`app/Core/Database/Database.php`)

Query builder with fluent API.

**How it works:**
```php
// Select
$users = Database::table('users')
    ->where('active', '=', 1)
    ->orderBy('created_at', 'DESC')
    ->get();

// Insert
Database::table('users')->insert([
    'name' => 'John',
    'email' => 'john@example.com'
]);

// Update
Database::table('users')
    ->where('id', '=', 1)
    ->update(['name' => 'Jane']);

// Delete
Database::table('users')
    ->where('id', '=', 1)
    ->delete();
```

**When to modify:**
- Adding new query methods
- Implementing query caching
- Adding database drivers

### 5. **Model** (`app/Core/Model.php`)

Active Record ORM pattern.

**How it works:**
```php
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email'];

    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Usage
$user = User::find(1);
$user->posts; // Lazy loading
```

**When to modify:**
- Adding new relationship types
- Implementing eager loading
- Adding model events

### 6. **Validator** (`app/Core/Validator.php`)

Input validation engine.

**Available rules:**
- `required` - Field must be present
- `email` - Valid email format
- `min:n` - Minimum length
- `max:n` - Maximum length
- `string` - Must be string
- `integer` - Must be integer
- `numeric` - Must be numeric
- `boolean` - Must be boolean
- `date` - Valid date format
- `in:a,b,c` - Must be in list
- `exists:table,column` - Must exist in database

**When to modify:**
- Adding new validation rules
- Improving error messages
- Adding custom validators

### 7. **View** (`app/Core/View.php`)

Twig template engine wrapper.

**How it works:**
```php
// In controller
return $this->view('users/index', [
    'users' => $users
]);

// In template
{% for user in users %}
    <p>{{ user.name }}</p>
{% endfor %}
```

**Custom Twig functions:**
- `{{ url('path') }}` - Generate URL
- `{{ asset('file.css') }}` - Asset URL
- `{{ storage('file.jpg') }}` - Storage URL
- `{{ csrf_field|raw }}` - CSRF token
- `{{ flash_display()|raw }}` - Flash messages
- `{{ locale() }}` - Current locale
- `{{ direction() }}` - Text direction (ltr/rtl)

**When to modify:**
- Adding new Twig functions
- Adding view composers
- Implementing view caching

### 8. **Auth** (`app/Core/Auth.php`)

Authentication system.

**How it works:**
```php
// Login
Auth::login($user);

// Check authentication
if (Auth::check()) {
    $user = Auth::user();
}

// Logout
Auth::logout();

// Middleware protection
Route::get($router, '/dashboard', 'DashboardController@index')
    ->middleware('auth');
```

**When to modify:**
- Adding OAuth support
- Implementing 2FA
- Adding API token authentication

---

## 🔄 Contribution Workflow

### 1. Find an Issue

- Check [GitHub Issues](https://github.com/your-org/oxygenframework/issues)
- Look for `good first issue` or `help wanted` labels
- Comment on the issue to claim it

### 2. Create a Branch

```bash
# Update main branch
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/your-feature-name
```

### 3. Make Changes

- Write clean, documented code
- Follow coding standards (see below)
- Add tests for new features
- Update documentation

### 4. Test Your Changes

```bash
# Run tests (when implemented)
composer test

# Manual testing
php -S localhost:8000 -t public
```

### 5. Commit Changes

```bash
git add .
git commit -m "feat: add user profile feature"
```

**Commit message format:**
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation
- `refactor:` - Code refactoring
- `test:` - Adding tests
- `chore:` - Maintenance

### 6. Push and Create PR

```bash
git push origin feature/your-feature-name
```

Then create a Pull Request on GitHub.

---

## 📝 Coding Standards

### PHP Standards

Follow **PSR-12** coding style:

```php
<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Core\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        return $this->view('users/index', [
            'users' => $users
        ]);
    }
}
```

**Key rules:**
- 4 spaces for indentation (no tabs)
- Opening braces on same line for methods
- One blank line between methods
- Type hints for parameters
- Return type declarations
- DocBlocks for all methods

### Naming Conventions

- **Classes**: `PascalCase` (e.g., `UserController`)
- **Methods**: `camelCase` (e.g., `getUserById`)
- **Variables**: `camelCase` (e.g., `$userName`)
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `MAX_USERS`)
- **Database tables**: `snake_case` plural (e.g., `user_profiles`)
- **Database columns**: `snake_case` (e.g., `created_at`)

### Security Guidelines

**Always:**
- ✅ Use `Request::clean()` for user input
- ✅ Use parameterized queries
- ✅ Validate all input
- ✅ Hash passwords with `Hash::make()`
- ✅ Include CSRF protection
- ✅ Escape output in views

**Never:**
- ❌ Use raw SQL with user input
- ❌ Store passwords in plain text
- ❌ Trust user input
- ❌ Expose sensitive data in errors

---

## 🧪 Testing Guidelines

### Writing Tests

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Oxygen\Core\Validator;

class ValidatorTest extends TestCase
{
    public function testRequiredRule()
    {
        $validator = Validator::make(['name' => ''], [
            'name' => 'required'
        ]);
        
        $this->assertTrue($validator->fails());
    }
    
    public function testEmailRule()
    {
        $validator = Validator::make(['email' => 'invalid'], [
            'email' => 'email'
        ]);
        
        $this->assertTrue($validator->fails());
    }
}
```

### Test Coverage

- **Unit tests**: Test individual methods
- **Integration tests**: Test component interactions
- **Feature tests**: Test complete features

**Aim for 80%+ code coverage**

---

## 📚 Documentation

### Code Documentation

Use PHPDoc for all classes and methods:

```php
/**
 * Find a user by ID
 *
 * @param int $id User ID
 * @return User|null User instance or null if not found
 * @throws DatabaseException If database connection fails
 */
public function find(int $id): ?User
{
    return Database::table('users')
        ->where('id', '=', $id)
        ->first();
}
```

### README Updates

When adding features, update:
- `README.md` - Main documentation
- `DOCUMENTATION.md` - Detailed API reference
- `CHANGELOG.md` - Version history

---

## 🎯 Priority Areas for Contribution

### High Priority

1. **Testing** - Add comprehensive test suite
2. **Performance** - Query optimization, caching
3. **Security** - Security audits, penetration testing
4. **Documentation** - API docs, tutorials, examples

### Medium Priority

1. **Features** - REST API, WebSockets, queues
2. **CLI** - More generators, better commands
3. **Database** - More drivers, query caching
4. **Validation** - More rules, custom validators

### Low Priority

1. **UI** - Admin panel, debugging tools
2. **Integrations** - Third-party services
3. **Packages** - Package ecosystem

---

## 💬 Getting Help

- **Discord**: [Join our community](#)
- **GitHub Discussions**: Ask questions
- **Email**: contribute@oxygenframework.com

---

## 📜 Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Provide constructive feedback
- Focus on what's best for the community

---

## 🙏 Thank You!

Every contribution, no matter how small, makes OxygenFramework better. We appreciate your time and effort!

**Happy coding!** 🚀
