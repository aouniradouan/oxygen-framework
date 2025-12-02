# OxygenFramework Documentation

**Version:** 2.0.0
**Author:** Oxygen Team

Welcome to **OxygenFramework**, the ultimate PHP framework designed for developers who demand speed, simplicity, and power. This documentation is an exhaustive **"A to Z"** guide covering every aspect of the system.

---

## 📚 Table of Contents

1.  [🚀 Introduction & Installation](#-introduction--installation)
2.  [⚙️ Configuration](#-configuration)
3.  [📂 Architecture & Directory Structure](#-architecture--directory-structure)
4.  [🛣️ Routing](#-routing)
5.  [🎮 Controllers](#-controllers)
6.  [📥 Requests & Input](#-requests--input)
7.  [📤 Responses](#-responses)
8.  [🎨 Views & Templating (Twig)](#-views--templating-twig)
    *   [Twig Functions & Helpers](#twig-functions--helpers)
    *   [Global Variables](#global-variables)
9.  [🗄️ Database & Query Builder](#-database--query-builder)
10. [🏗️ ORM (Models)](#-orm-models)
11. [🔐 Security (Auth, CSRF, XSS)](#-security-auth-csrf-xss)
12. [🌍 Localization & RTL](#-localization--rtl)
13. [✅ Validation](#-validation)
14. [🛠️ Global Helpers](#-global-helpers)
15. [💻 CLI Tools (Oxygen Console)](#-cli-tools-oxygen-console)
16. [🎨 Frontend (Tailwind & Vite)](#-frontend-tailwind--vite)
17. [🚀 Deployment](#-deployment)

---

## 🚀 Introduction & Installation

OxygenFramework is built on modern PHP principles, offering a lightweight yet feature-rich environment.

### Requirements
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB
- **Composer**: For dependency management
- **Node.js & NPM**: For frontend assets

### Installation Steps

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/Oxygen-dz/oxygenframework.git my-app
    cd my-app
    ```

2.  **Install PHP Dependencies**:
    ```bash
    composer install
    ```

3.  **Install Frontend Dependencies**:
    ```bash
    npm install
    ```

4.  **Environment Setup**:
    Copy the example config file:
    ```bash
    cp config.example.php config.php
    ```
    Edit `config.php` with your database credentials.

5.  **Run Migrations**:
    Initialize your database tables:
    ```bash
    php oxygen migrate
    ```

6.  **Start the Server**:
    ```bash
    php oxygen serve
    ```
    Visit `http://localhost:8000` in your browser.

---

## ⚙️ Configuration

All configuration is located in `config.php`.

```php
return [
    'app' => [
        'APP_NAME' => 'My Oxygen App',
        'APP_ENV' => 'local', // local or production
        'APP_DEBUG' => true,  // Show errors
        'APP_URL' => 'http://localhost:8000',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'database' => 'oxygen',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    // ...
];
```

**Accessing Config:**
Use the `config()` helper:
```php
$appName = config('app.APP_NAME');
$dbHost = config('db.host');
```

---

## 📂 Architecture & Directory Structure

*   **`app/`**: The heart of your application.
    *   **`Console/`**: CLI commands.
    *   **`Controllers/`**: Request handlers.
    *   **`Core/`**: Framework core files (Router, View, Model, etc.).
    *   **`Middleware/`**: HTTP middleware (Auth, CSRF).
    *   **`Models/`**: Database models.
*   **`database/`**:
    *   **`migrations/`**: Schema definition files.
    *   **`seeds/`**: Data seeders.
*   **`public/`**: Web root. Contains `index.php` and assets.
*   **`resources/`**:
    *   **`css/` & `js/`**: Uncompiled assets.
    *   **`lang/`**: Localization files (`en`, `fr`, `ar`).
    *   **`views/`**: Twig templates.
*   **`routes/`**:
    *   **`web.php`**: Route definitions.
*   **`storage/`**: Logs, cache, and file uploads.
*   **`vendor/`**: Composer dependencies.

---

## 🛣️ Routing

Routes are defined in `routes/web.php`. We use `Bramus\Router`.

**Basic Routes:**
```php
$router->get('/', 'HomeController@index');
$router->post('/login', 'AuthController@login');
$router->match('GET|POST', '/contact', 'ContactController@handle');
```

**Route Parameters:**
```php
$router->get('/posts/(\d+)', 'PostController@show'); // ID must be numeric
$router->get('/users/(\w+)', 'UserController@profile'); // Username
```

**Route Groups & Middleware:**
```php
$router->before('GET|POST', '/admin/.*', function() {
    if (!auth()->check()) {
        redirect('/login');
    }
});

$router->mount('/admin', function() use ($router) {
    $router->get('/', 'AdminController@dashboard');
    $router->get('/users', 'AdminController@users');
});
```

---

## 🎮 Controllers

Controllers reside in `app/Controllers`. They should extend `Oxygen\Core\Controller`.

**Basic Controller:**
```php
namespace Oxygen\Controllers;

use Oxygen\Core\Controller;

class PageController extends Controller
{
    public function about()
    {
        return $this->view('pages/about', ['title' => 'About Us']);
    }
}
```

**Resource Controller:**
A resource controller handles CRUD operations.
- `index()`: List items.
- `create()`: Show create form.
- `store()`: Save new item.
- `show($id)`: Show single item.
- `edit($id)`: Show edit form.
- `update($id)`: Update item.
- `destroy($id)`: Delete item.

Generate one using: `php oxygen make:controller PostController`

---

## 📥 Requests & Input

The `Oxygen\Core\Request` class provides access to HTTP request data.

**Dependency Injection:**
```php
use Oxygen\Core\Request;

public function store()
{
    $request = app()->make(Request::class);
    // ...
}
```

**Retrieving Input:**
```php
$name = $request->input('name');
$email = $request->input('email', 'default@example.com');
$allData = $request->all();
```

**File Uploads:**
```php
if ($request->hasFile('avatar')) {
    $file = $request->file('avatar');
    // $file is the $_FILES array for 'avatar'
}
```

**Input Sanitization (Security):**
To prevent Stored XSS, always sanitize input before saving to the database.
```php
$cleanData = $request->clean(); // Strips ALL HTML tags
```

---

## 📤 Responses

**Returning Views:**
```php
return $this->view('home', ['name' => 'John']);
```

**JSON Response:**
```php
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
exit;
```

**Redirects:**
```php
redirect('/dashboard');
redirect('/login', 301); // Permanent
back(); // Redirect to previous page
```

---

## 🎨 Views & Templating (Twig)

Views are stored in `resources/views`. We use **Twig** for templating.

**Basic Syntax:**
```html
<h1>Hello, {{ name }}</h1>
{% if user.isAdmin %}
    <button>Delete</button>
{% endif %}
```

**Inheritance:**
*layout.twig.html*
```html
<!DOCTYPE html>
<html>
<body>
    {% block content %}{% endblock %}
</body>
</html>
```

*page.twig.html*
```html
{% extends "layout.twig.html" %}
{% block content %}
    <p>My Content</p>
{% endblock %}
```

### Twig Functions & Helpers

| Function | Description | Example |
| :--- | :--- | :--- |
| `asset($path)` | URL to public asset | `{{ asset('css/style.css') }}` |
| `url($path)` | Absolute URL | `{{ url('/posts/1') }}` |
| `storage($path)` | URL to storage file | `{{ storage('uploads/img.jpg') }}` |
| `csrf_field()` | Hidden CSRF input | `{{ csrf_field|raw }}` |
| `flash_display()` | Show flash messages | `{{ flash_display()|raw }}` |
| `__($key)` | Translate string | `{{ __('messages.welcome') }}` |
| `rtl_class($l, $r)` | Conditional class | `class="{{ rtl_class('ml-2', 'mr-2') }}"` |
| `direction()` | 'ltr' or 'rtl' | `dir="{{ direction() }}"` |

### Global Variables

| Variable | Description |
| :--- | :--- |
| `APP_NAME` | Application name from config |
| `APP_URL` | Application URL from config |
| `auth.check` | Boolean: is user logged in? |
| `auth.user` | Current user object (or null) |
| `csrf_token` | The CSRF token string |
| `current_locale` | Current language code (e.g., 'en') |
| `is_rtl` | Boolean: is current language RTL? |

---

## 🗄️ Database & Query Builder

OxygenFramework uses a fluent Query Builder.

**Retrieving Data:**
```php
// Get all
$users = User::all();

// Find by ID
$user = User::find(1);

// Where Clause
$active = User::where('status', 'active')->get();

// Complex Where
$users = User::where('age', '>', 18)->where('role', 'admin')->get();

// Ordering & Limits
$latest = User::orderBy('created_at', 'DESC')->limit(5)->get();
```

**Pagination:**
```php
$posts = Post::paginate(10); // 10 per page
// In View: {{ posts.links()|raw }}
```

**Insert, Update, Delete:**
```php
// Insert
User::create(['name' => 'John', 'email' => 'john@doe.com']);

// Update
User::update($id, ['name' => 'Jane']);

// Delete
User::delete($id);
```

---

## 🏗️ ORM (Models)

Models are located in `app/Models`. They represent database tables.

**Defining a Model:**
```php
namespace Oxygen\Models;

use Oxygen\Core\Model;

class Product extends Model
{
    protected $table = 'products'; // Optional if name matches
    protected $fillable = ['name', 'price', 'category_id']; // Required for mass assignment
}
```

**Relationships:**

*Belongs To:*
```php
public function category() {
    return $this->belongsTo(Category::class);
}
```

*Has Many:*
```php
public function products() {
    return $this->hasMany(Product::class);
}
```

*Usage:*
```php
$product = Product::find(1);
echo $product->category->name; // Automatically fetches category
```

---

## 🔐 Security (Auth, CSRF, XSS)

### Authentication
- **Login**: `auth()->attempt($credentials)`
- **Logout**: `auth()->logout()`
- **Check**: `auth()->check()`
- **User**: `auth()->user()`

### Authorization (RBAC)
The `User` model has built-in RBAC methods.
```php
// Check Role
if ($user->hasRole('admin')) { ... }

// Check Permission
if ($user->can('delete_users')) { ... }
```

### CSRF Protection
Cross-Site Request Forgery protection is enabled by default.
**Always** include `{{ csrf_field|raw }}` in your POST forms.

### XSS Protection
- **Output**: Twig automatically escapes output. Use `|raw` only when necessary.
- **Input**: Use `$request->clean()` to strip tags from input.

---

## 🌍 Localization & RTL

**Language Files**: `resources/lang/{locale}/messages.php`.

**Example (`resources/lang/fr/messages.php`):**
```php
return [
    'welcome' => 'Bienvenue',
];
```

**Usage:**
```php
echo __('messages.welcome');
```

**RTL Support:**
The framework detects RTL languages (like Arabic) and sets `is_rtl` global.
Use `{{ rtl_class('left', 'right') }}` to swap CSS classes dynamically.

---

## ✅ Validation

Use `OxygenValidator` to validate request data.

**Example:**
```php
use Oxygen\Core\Validation\OxygenValidator as Validator;

$validator = Validator::make($request->all(), [
    'username' => 'required|string|min:3|max:20',
    'email'    => 'required|email',
    'password' => 'required|min:8|confirmed',
    'age'      => 'numeric|min:18'
]);

if ($validator->fails()) {
    $_SESSION['errors'] = $validator->errors();
    back();
}
```

**Available Rules:**
- `required`: Must be present and not empty.
- `email`: Must be a valid email.
- `string`: Must be a string.
- `numeric`: Must be a number.
- `min:value`: Minimum length (string) or value (numeric).
- `max:value`: Maximum length (string) or value (numeric).
- `confirmed`: Checks if `field_confirmation` matches `field`.

---

## 🛠️ Global Helpers

These functions are available everywhere in your application.

| Helper | Description |
| :--- | :--- |
| `app()` | Get the Application instance |
| `auth()` | Get the Auth instance |
| `back()` | Redirect to the previous page |
| `config($key, $default)` | Get configuration value |
| `dd(...$vars)` | Dump variables and die (debug) |
| `dump(...$vars)` | Dump variables (debug) |
| `env($key, $default)` | Get environment variable |
| `old($key)` | Get old input value (after validation fail) |
| `redirect($url)` | Redirect to a URL |
| `session($key, $value)` | Get or set session data |
| `view($name, $data)` | Render a view |

---

## 💻 CLI Tools (Oxygen Console)

Run commands using `php oxygen <command>`.

| Command | Usage | Description |
| :--- | :--- | :--- |
| `serve` | `php oxygen serve` | Start the development server |
| `migrate` | `php oxygen migrate` | Run pending database migrations |
| `make:controller` | `php oxygen make:controller Name` | Create a new controller class |
| `make:model` | `php oxygen make:model Name` | Create a new model class |
| `make:migration` | `php oxygen make:migration Name` | Create a new migration file |
| `make:mvc` | `php oxygen make:mvc Name` | **Power Command**: Generates Model, View, Controller, Migration, and Routes all at once! |
| `generate:app` | `php oxygen generate:app` | Scaffold a complete basic application structure |

---

## 🎨 Frontend (Tailwind & Vite)

**Development:**
By default, the layout uses Tailwind CDN. This allows you to start coding immediately without running `npm`.

**Production:**
1.  Run `npm run build` to compile assets.
2.  In `resources/views/layouts/app.twig.html`, comment out the CDN and uncomment the Vite/Build lines.

**Custom CSS/JS:**
Place your custom files in `resources/css` and `resources/js`. Use `{{ asset('css/custom.css') }}` to link them.

---

## 🚀 Deployment

Ready to go live? Follow this checklist:

1.  **Environment**: Set `APP_ENV` to `production` and `APP_DEBUG` to `false` in `config.php`.
2.  **Database**: Update `db` config with production credentials.
3.  **Assets**: Run `npm run build` to generate optimized assets.
4.  **Permissions**: Ensure the `storage` directory is writable by the web server.
5.  **Web Server**: Point your Document Root to the `public/` folder.
6.  **Security**: Ensure `config.php` is NOT accessible from the web.

---

**OxygenFramework** - *Thnks to Oxyegn O2*
