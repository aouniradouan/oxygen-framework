# CLI Commands

This guide covers the command-line interface, available commands, creating custom commands, and using generators.

## Table of Contents

- [Introduction](#introduction)
- [Available Commands](#available-commands)
- [Generators](#generators)
- [Database Commands](#database-commands)
- [Creating Custom Commands](#creating-custom-commands)
- [Running Commands](#running-commands)

---

## Introduction

OxygenFramework includes a powerful CLI for scaffolding, database management, and custom tasks.

### List All Commands

```bash
php oxygen list
```

---

## Available Commands

### Server

```bash
# Start development server
php oxygen serve

# Custom host and port
php oxygen serve --host=0.0.0.0 --port=8080
```

### Generators

```bash
# Generate model
php oxygen make:model User

# Generate model with migration
php oxygen make:model Post --migration

# Generate controller
php oxygen make:controller UserController

# Generate middleware
php oxygen make:middleware CheckAge

# Generate migration
php oxygen make:migration create_users_table

# Generate service
php oxygen make:service PaymentService

# Generate complete MVC
php oxygen make:mvc Post
```

### Database

```bash
# Run migrations
php oxygen migrate

# Rollback last migration
php oxygen migrate:rollback
```

### Queue

```bash
# Process queued jobs
php oxygen queue:work
```

### Scheduler

```bash
# Run scheduled tasks
php oxygen schedule:run
```

### Testing

```bash
# Run all tests
php oxygen test:all

# Generate test
php oxygen test:generate
```

### Maintenance

```bash
# Clean up old logs
php oxygen cleanup:logs
```

---

## Generators

### make:model

Creates a new model class.

```bash
php oxygen make:model Post
```

Creates: `app/Models/Post.php`

```php
<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = [];
}
```

With migration:

```bash
php oxygen make:model Post --migration
```

### make:controller

Creates a new controller.

```bash
php oxygen make:controller PostController
```

Creates: `app/Controllers/PostController.php`

```php
<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;

class PostController extends Controller
{
    public function index()
    {
        //
    }
}
```

### make:mvc

Creates model, controller, views, and migration.

```bash
php oxygen make:mvc Post
```

Creates:
- `app/Models/Post.php`
- `app/Controllers/PostController.php`
- `resources/views/posts/index.twig.html`
- `resources/views/posts/create.twig.html`
- `resources/views/posts/edit.twig.html`
- `resources/views/posts/show.twig.html`
- `database/migrations/xxxx_create_posts_table.php`

### make:middleware

Creates a new middleware.

```bash
php oxygen make:middleware CheckAge
```

Creates: `app/Http/Middleware/CheckAge.php`

### make:migration

Creates a new migration file.

```bash
php oxygen make:migration create_posts_table
```

Creates: `database/migrations/xxxx_create_posts_table.php`

---

## Database Commands

### migrate

Runs all pending migrations.

```bash
php oxygen migrate
```

### migrate:rollback

Rolls back the last migration.

```bash
php oxygen migrate:rollback
```

---

## Creating Custom Commands

### Generate Command

```bash
php oxygen make:command SendEmails
```

### Command Structure

**File:** `app/Console/Commands/SendEmails.php`

```php
<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

class SendEmails extends Command
{
    protected $signature = 'emails:send {user} {--queue}';
    protected $description = 'Send emails to users';
    
    public function handle()
    {
        $user = $this->argument('user');
        $queue = $this->option('queue');
        
        $this->info('Sending emails...');
        
        // Your logic here
        
        $this->success('Emails sent successfully!');
    }
}
```

### Register Command

**File:** `app/Console/OxygenKernel.php`

```php
protected $commands = [
    \Oxygen\Console\Commands\SendEmails::class,
];
```

### Command Methods

```php
// Output
$this->info('Info message');
$this->error('Error message');
$this->success('Success message');
$this->line('Plain message');

// Arguments
$user = $this->argument('user');

// Options
$queue = $this->option('queue');

// Ask for input
$name = $this->ask('What is your name?');
$password = $this->secret('What is the password?');
$confirmed = $this->confirm('Do you wish to continue?');
```

---

## Running Commands

### Basic Execution

```bash
php oxygen command:name
```

### With Arguments

```bash
php oxygen emails:send john@example.com
```

### With Options

```bash
php oxygen emails:send john@example.com --queue
```

### Help

```bash
php oxygen help command:name
```

---

## See Also

- [Database Migrations](DATABASE_MODELS.md#migrations)
- [Testing](TESTING.md)
