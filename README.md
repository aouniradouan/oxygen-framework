# OxygenFramework

**A Lightweight, Modern PHP Framework for Rapid Development**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://www.php.net/)

---

## Introduction

**OxygenFramework** is a lightweight, modern PHP framework designed for developers who want the power of a full-featured framework without the bloat. Built with simplicity and performance in mind, Oxygen provides all the essential tools you need to build web applications quickly and efficiently.

**Created by:** REDWAN AOUNI

---

## Key Features

### Core Features
- **🎯 Simple Routing** - Clean, expressive routing with support for GET, POST, PUT, DELETE, PATCH
- **🗄️ Elegant ORM** - Active Record pattern with relationships, pagination, and query building
- **🎨 Twig Templates** - Powerful templating engine with inheritance and components
- **🔐 Authentication** - Built-in session-based authentication system
- **🛡️ Security** - CSRF protection, XSS prevention, and SQL injection protection
- **📦 Dependency Injection** - Service container for clean, testable code
- **⚡ Middleware** - Global and route-specific middleware support
- **🔧 CLI Tools** - Powerful command-line interface for scaffolding and management

### Developer Experience
- **📝 Code Generators** - Generate models, controllers, migrations, and complete CRUD scaffolds
- **🔄 Database Migrations** - Version control for your database schema
- **📊 Query Builder** - Fluent interface for building database queries
- **🌐 Localization** - Multi-language support with RTL compatibility
- **📁 File Storage** - Simple file upload and storage management
- **🎯 Validation** - Built-in validation for forms and API requests
- **📝 Logging** - Comprehensive logging system
- **⚙️ Configuration** - Environment-based configuration management

---

## Quick Start

### Requirements

- PHP 7.4 or higher
- Composer
- MySQL/MariaDB or SQLite
- Apache/Nginx with mod_rewrite

### Installation

```bash
# Clone the repository
git clone https://github.com/redwan-aouni/oxygen-framework.git
cd oxygen-framework

# Install dependencies
composer install

# Configure environment
copy .env.example .env

# Edit .env with your database credentials
# Then run the development server
php oxygen serve
```

Visit `http://localhost:8000`

### Your First Route

Edit `routes/web.php`:

```php
<?php

use Bramus\Router\Router;
use Oxygen\Core\Route;

$router = app()->make(Router::class);

// Simple route
Route::get($router, '/', function() {
    echo view('welcome');
});

// Route with controller
Route::get($router, '/users', 'UserController@index');
```

### Your First Model

```bash
# Generate a model
php oxygen make:model Post

# Generate model with migration
php oxygen make:model Post --migration
```

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
        return $this->belongsTo(User::class);
    }
}
```

### Using the Model

```php
// Create
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'Hello World!',
    'user_id' => 1
]);

// Read
$posts = Post::all();
$post = Post::find(1);
$posts = Post::where('user_id', '=', 1)->get();

// Update
Post::update(1, ['title' => 'Updated Title']);

// Delete
Post::delete(1);

// With relationships
$posts = Post::with('user')->get();
```

---

## Documentation

### Getting Started
- [Installation & Setup](docs/GETTING_STARTED.md)
- [Directory Structure](docs/GETTING_STARTED.md#directory-structure)
- [Configuration](docs/CONFIGURATION.md)

### Core Concepts
- [Routing](docs/ROUTING.md) - Define routes and handle requests
- [Controllers](docs/ROUTING.md#controllers) - Organize your application logic
- [Models & Database](docs/DATABASE_MODELS.md) - Work with databases
- [Views & Templates](docs/VIEWS_TEMPLATES.md) - Render beautiful interfaces
- [Middleware](docs/MIDDLEWARE.md) - Filter HTTP requests

### Advanced Features
- [CLI Commands](docs/CLI_COMMANDS.md) - Command-line tools and generators
- [Authentication](docs/AUTHENTICATION.md) - User authentication and authorization
- [Error Handling](docs/ERROR_HANDLING.md) - Handle errors gracefully
- [Localization](docs/LOCALIZATION.md) - Multi-language support
- [API Development](docs/API_DEVELOPMENT.md) - Build RESTful APIs

### Reference
- [Helper Functions](docs/HELPERS.md) - Available helper functions
- [Configuration Reference](docs/CONFIGURATION.md) - All configuration options

---

## CLI Commands

```bash
# Serve the application
php oxygen serve

# Generate resources
php oxygen make:model User
php oxygen make:controller UserController
php oxygen make:mvc Post  # Model + Controller + Views

# Database migrations
php oxygen make:migration create_users_table
php oxygen migrate
php oxygen migrate:rollback

# Other commands
php oxygen list              # List all commands
php oxygen queue:work        # Process queued jobs
php oxygen schedule:run      # Run scheduled tasks
```

---

## Project Structure

```
oxygen-framework/
├── app/
│   ├── Console/          # CLI commands
│   ├── Controllers/      # Application controllers
│   ├── Core/             # Framework core
│   ├── Models/           # Database models
│   ├── Middleware/       # HTTP middleware
│   └── helpers/          # Helper functions
├── config/               # Configuration files
├── database/
│   └── migrations/       # Database migrations
├── public/               # Public assets
│   └── index.php         # Entry point
├── resources/
│   └── views/            # Twig templates
├── routes/
│   ├── web.php           # Web routes
│   └── api.php           # API routes
├── storage/              # File storage & logs
└── vendor/               # Composer dependencies
```

---

## Example: Building a Blog

```bash
# Generate everything you need
php oxygen make:mvc Article

# This creates:
# - Model: app/Models/Article.php
# - Controller: app/Controllers/ArticleController.php
# - Views: resources/views/articles/{index,create,edit,show}.twig.html
# - Migration: database/migrations/xxxx_create_articles_table.php
```

Run the migration:
```bash
php oxygen migrate
```

Add routes in `routes/web.php`:
```php
Route::resource($router, '/articles', 'ArticleController');
```

That's it! You now have a fully functional CRUD interface for articles.

---

## Philosophy

OxygenFramework is built on these principles:

1. **Simplicity First** - Easy to learn, easy to use
2. **Convention over Configuration** - Sensible defaults that just work
3. **Developer Happiness** - Tools that make development enjoyable
4. **Performance** - Lightweight core, fast execution
5. **Flexibility** - Extend and customize as needed

---

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/redwan-aouni/oxygen-framework.git
cd oxygen-framework
composer install
copy .env.example .env
php oxygen serve
```

---

## Security

If you discover a security vulnerability, please email aouniradouan@gmail.com. All security vulnerabilities will be promptly addressed.

---

## License

OxygenFramework is open-sourced software licensed under the [MIT license](LICENSE).

---

## Credits

**Created by:** REDWAN AOUNI  
**Email:** aouniradouan@gmail.com  
**Location:** Algeria 🇩🇿

### Built With

- [Bramus Router](https://github.com/bramus/router) - Routing
- [Twig](https://twig.symfony.com/) - Templating
- [Nette Database](https://doc.nette.org/en/database) - Database layer
- [Whoops](https://github.com/filp/whoops) - Error handling (dev mode)

---

**Made with care in Algeria**