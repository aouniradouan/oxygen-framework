# Getting Started with OxygenFramework

This guide will help you get started with OxygenFramework, from installation to building your first application.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Directory Structure](#directory-structure)
- [First Application](#first-application)
- [Common Tasks](#common-tasks)

---

## Installation

### Requirements

- **PHP** 7.4 or higher
- **Composer** - Dependency manager
- **Database** - MySQL, MariaDB, or SQLite
- **Web Server** - Apache or Nginx with mod_rewrite

### Step 1: Clone or Download

```bash
git clone https://github.com/redwan-aouni/oxygen-framework.git my-project
cd my-project
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment

```bash
# Copy the example environment file
copy .env.example .env  # Windows
cp .env.example .env    # Linux/Mac
```

Edit `.env` and configure your database:

```env
# Application
APP_NAME="My Application"
APP_URL=http://localhost:8000
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oxygen_db
DB_USERNAME=root
DB_PASSWORD=

# Errors
DEV_MODE=true
```

### Step 4: Create Database

Create a database matching your `DB_DATABASE` value:

```sql
CREATE DATABASE oxygen_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 5: Run the Server

```bash
php oxygen serve
```

Visit `http://localhost:8000` - you should see the welcome page!

---

## Configuration

All configuration files are located in the `config/` directory.

### Main Configuration Files

- **`config/app.php`** - Application settings
- **`config/database.php`** - Database connections
- **`config/errors.php`** - Error handling settings
- **`config/session.php`** - Session configuration

### Environment Variables

The `.env` file contains environment-specific settings. Never commit this file to version control!

Common environment variables:

```env
# Application
APP_NAME="OxygenFramework"
APP_URL=http://localhost:8000
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oxygen
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_LIFETIME=120
SESSION_DRIVER=file

# Error Handling
DEV_MODE=true
```

Access environment variables in code:

```php
$appName = env('APP_NAME', 'Default Name');
$debug = env('APP_DEBUG', false);
```

---

## Directory Structure

```
oxygen-framework/
├── app/
│   ├── Console/              # CLI commands
│   │   ├── Commands/         # Custom commands
│   │   └── OxygenKernel.php  # Console kernel
│   ├── Controllers/          # HTTP controllers
│   │   └── Controller.php    # Base controller
│   ├── Core/                 # Framework core (don't modify)
│   │   ├── Application.php   # Application container
│   │   ├── Model.php         # Base model
│   │   ├── View.php          # View engine
│   │   ├── Route.php         # Routing helper
│   │   └── ...
│   ├── Http/
│   │   └── Middleware/       # HTTP middleware
│   ├── Models/               # Database models
│   ├── Services/             # Business logic services
│   └── helpers/              # Helper functions
│       ├── helpers.php       # General helpers
│       ├── lang.php          # Localization helpers
│       └── storage.php       # Storage helpers
├── config/                   # Configuration files
│   ├── app.php              # App configuration
│   ├── database.php         # Database configuration
│   ├── errors.php           # Error handling
│   └── session.php          # Session configuration
├── database/
│   └── migrations/          # Database migrations
├── docs/                    # Documentation
├── public/                  # Public web root
│   ├── index.php           # Application entry point
│   ├── storage/            # Public storage (symlink)
│   └── assets/             # CSS, JS, images
├── resources/
│   ├── lang/               # Language files
│   └── views/              # Twig templates
│       ├── layouts/        # Layout templates
│       ├── components/     # Reusable components
│       └── errors/         # Error pages
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
├── storage/
│   ├── app/               # Application files
│   ├── logs/              # Log files
│   └── uploads/           # User uploads
├── vendor/                # Composer dependencies
├── .env                   # Environment configuration
├── .env.example           # Example environment file
├── composer.json          # PHP dependencies
└── oxygen                 # CLI executable
```

### Key Directories

- **`app/Controllers/`** - Your application controllers
- **`app/Models/`** - Your database models
- **`resources/views/`** - Your Twig templates
- **`routes/`** - Route definitions
- **`public/`** - Publicly accessible files
- **`storage/`** - File storage and logs

---

## First Application

Let's build a simple task manager application.

### Step 1: Create the Model and Migration

```bash
php oxygen make:model Task --migration
```

This creates:
- `app/Models/Task.php`
- `database/migrations/xxxx_create_tasks_table.php`

Edit the migration (`database/migrations/xxxx_create_tasks_table.php`):

```php
<?php

use Oxygen\Core\Database\Migration;

class CreateTasksTable extends Migration
{
    public function up()
    {
        $this->schema->create('tasks', function($table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropIfExists('tasks');
    }
}
```

Run the migration:

```bash
php oxygen migrate
```

### Step 2: Create the Controller

```bash
php oxygen make:controller TaskController
```

Edit `app/Controllers/TaskController.php`:

```php
<?php

namespace Oxygen\Controllers;

use Oxygen\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        echo view('tasks/index', ['tasks' => $tasks]);
    }

    public function create()
    {
        echo view('tasks/create');
    }

    public function store()
    {
        $data = request()->only(['title', 'description']);
        Task::create($data);
        redirect('/tasks');
    }

    public function edit($id)
    {
        $task = Task::find($id);
        echo view('tasks/edit', ['task' => $task]);
    }

    public function update($id)
    {
        $data = request()->only(['title', 'description', 'completed']);
        Task::update($id, $data);
        redirect('/tasks');
    }

    public function destroy($id)
    {
        Task::delete($id);
        redirect('/tasks');
    }
}
```

### Step 3: Create Views

Create `resources/views/tasks/index.twig.html`:

```twig
{% extends "layouts/app.twig" %}

{% block title %}Tasks{% endblock %}

{% block content %}
    <h1>My Tasks</h1>
    <a href="/tasks/create">Add New Task</a>
    
    <ul>
    {% for task in tasks %}
        <li>
            {{ task.title }}
            {% if task.completed %}✓{% endif %}
            <a href="/tasks/{{ task.id }}/edit">Edit</a>
        </li>
    {% endfor %}
    </ul>
{% endblock %}
```

### Step 4: Add Routes

Edit `routes/web.php`:

```php
<?php

use Bramus\Router\Router;
use Oxygen\Core\Route;

$router = app()->make(Router::class);

// Task routes
Route::get($router, '/tasks', 'TaskController@index');
Route::get($router, '/tasks/create', 'TaskController@create');
Route::post($router, '/tasks', 'TaskController@store');
Route::get($router, '/tasks/(\d+)/edit', 'TaskController@edit');
Route::post($router, '/tasks/(\d+)', 'TaskController@update');
Route::post($router, '/tasks/(\d+)/delete', 'TaskController@destroy');
```

Or use resource routing:

```php
Route::resource($router, '/tasks', 'TaskController');
```

### Step 5: Test Your Application

Visit `http://localhost:8000/tasks` and start managing tasks!

---

## Common Tasks

### Creating a Model

```bash
# Simple model
php oxygen make:model Post

# Model with migration
php oxygen make:model Post --migration

# Model with controller and views
php oxygen make:mvc Post
```

### Creating a Controller

```bash
php oxygen make:controller PostController
```

### Creating a Migration

```bash
php oxygen make:migration create_posts_table
```

### Running Migrations

```bash
# Run all pending migrations
php oxygen migrate

# Rollback last migration
php oxygen migrate:rollback
```

### Working with the Database

```php
// Create
$post = Post::create([
    'title' => 'Hello World',
    'content' => 'My first post'
]);

// Read
$posts = Post::all();
$post = Post::find(1);
$posts = Post::where('status', '=', 'published')->get();

// Update
Post::update(1, ['title' => 'Updated Title']);

// Delete
Post::delete(1);
```

### Rendering Views

```php
// In a controller
public function index()
{
    $data = ['name' => 'John'];
    echo view('welcome', $data);
}

// Using helper
echo view('posts/index', ['posts' => $posts]);
```

### Redirecting

```php
// Redirect to URL
redirect('/posts');

// Redirect back
back();
```

### Flash Messages

```php
// Set flash message
Flash::set('success', 'Post created successfully!');

// Display in view (automatic)
{{ flash_display() }}
```

---

## Next Steps

- [Learn about Routing](ROUTING.md)
- [Explore Database & Models](DATABASE_MODELS.md)
- [Master Views & Templates](VIEWS_TEMPLATES.md)
- [Understand Middleware](MIDDLEWARE.md)
- [Build APIs](API_DEVELOPMENT.md)

---

**Happy coding with OxygenFramework! 🚀**
