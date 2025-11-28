# Views & Templating - Complete Guide
## OxygenFramework Professional Documentation

**Version:** 4.0  
**Author:** Redwan Aouni & Oxygen Community  
**Last Updated:** 2025-11-27

---

## Table of Contents

1. [Introduction to Templating](#introduction)
2. [Twig Basics](#twig-basics)
3. [OxygenFramework View System](#oxygen-view-system)
4. [Global Variables](#global-variables)
5. [Helper Functions](#helper-functions)
6. [CSRF Protection](#csrf-protection)
7. [Assets Management](#assets-management)
8. [Storage & File Uploads](#storage-file-uploads)
9. [Layouts & Inheritance](#layouts-inheritance)
10. [Components & Partials](#components-partials)
11. [Forms](#forms)
12. [Authentication](#authentication)
13. [Flash Messages](#flash-messages)
14. [Advanced Techniques](#advanced-techniques)
15. [Best Practices](#best-practices)
16. [Troubleshooting](#troubleshooting)
17. [Complete Examples](#complete-examples)

---

## 1. Introduction to Templating {#introduction}

### What is Templating?

Templating is the process of separating your application's presentation layer (HTML) from your business logic (PHP). Instead of mixing PHP and HTML code, you use a template engine to create clean, maintainable views.

### Why Use Twig?

**Twig** is a modern, secure, and fast templating engine for PHP. OxygenFramework uses Twig because:

1. **Security** - Automatic escaping prevents XSS attacks
2. **Clean Syntax** - Easy to read and write
3. **Powerful** - Inheritance, filters, functions
4. **Fast** - Compiled templates for performance
5. **Flexible** - Extensible with custom functions

### OxygenFramework View Architecture

```
resources/views/
├── layouts/           # Master layouts
│   ├── app.twig.html
│   └── admin.twig.html
├── components/        # Reusable components
│   ├── navbar.twig.html
│   └── footer.twig.html
├── pages/            # Page templates
│   ├── home.twig.html
│   └── about.twig.html
└── products/         # Resource views
    ├── index.twig.html
    ├── create.twig.html
    ├── edit.twig.html
    └── show.twig.html
```

---

## 2. Twig Basics {#twig-basics}

### 2.1 Twig Syntax

Twig uses three types of delimiters:

#### Output (Print Variables)
```twig
{{ variable }}
{{ user.name }}
{{ product.price }}
```

#### Logic (Control Structures)
```twig
{% if condition %}
    ...
{% endif %}

{% for item in items %}
    ...
{% endfor %}
```

#### Comments
```twig
{# This is a comment #}
{# 
   Multi-line
   comment
#}
```

### 2.2 Variables

#### Accessing Variables
```twig
{# Simple variable #}
{{ name }}

{# Object property #}
{{ user.name }}
{{ user.email }}

{# Array element #}
{{ users[0] }}
{{ users['admin'] }}

{# Method call #}
{{ user.getName() }}
```

#### Variable Assignment
```twig
{% set name = 'John' %}
{% set total = price * quantity %}
{% set user = {name: 'John', age: 30} %}
```

### 2.3 Filters

Filters modify variables for display:

```twig
{# Convert to uppercase #}
{{ name|upper }}

{# Convert to lowercase #}
{{ email|lower }}

{# Capitalize first letter #}
{{ title|capitalize }}

{# Format date #}
{{ created_at|date('Y-m-d') }}

{# Default value if empty #}
{{ description|default('No description') }}

{# Truncate text #}
{{ content|slice(0, 100) ~ '...' }}

{# Strip HTML tags #}
{{ html_content|striptags }}

{# URL encode #}
{{ search_query|url_encode }}

{# JSON encode #}
{{ data|json_encode }}

{# Raw (no escaping) #}
{{ html_content|raw }}
```

### 2.4 Control Structures

#### If Statements
```twig
{% if user %}
    <p>Welcome, {{ user.name }}!</p>
{% endif %}

{% if age >= 18 %}
    <p>You are an adult</p>
{% else %}
    <p>You are a minor</p>
{% endif %}

{% if role == 'admin' %}
    <p>Admin Panel</p>
{% elseif role == 'moderator' %}
    <p>Moderator Panel</p>
{% else %}
    <p>User Panel</p>
{% endif %}
```

#### For Loops
```twig
{# Simple loop #}
{% for user in users %}
    <li>{{ user.name }}</li>
{% endfor %}

{# Loop with index #}
{% for key, user in users %}
    <li>{{ key }}: {{ user.name }}</li>
{% endfor %}

{# Loop with special variables #}
{% for user in users %}
    <li class="{% if loop.first %}first{% endif %} {% if loop.last %}last{% endif %}">
        {{ loop.index }}. {{ user.name }}
    </li>
{% endfor %}

{# Empty loop #}
{% for user in users %}
    <li>{{ user.name }}</li>
{% else %}
    <li>No users found</li>
{% endfor %}
```

**Loop Variables:**
- `loop.index` - Current iteration (1-indexed)
- `loop.index0` - Current iteration (0-indexed)
- `loop.first` - True if first iteration
- `loop.last` - True if last iteration
- `loop.length` - Total number of items
- `loop.revindex` - Iterations from end (1-indexed)
- `loop.revindex0` - Iterations from end (0-indexed)
- `loop.parent` - Parent loop context

### 2.5 Comparisons & Logic

```twig
{# Equality #}
{% if name == 'John' %}...{% endif %}
{% if name != 'John' %}...{% endif %}

{# Comparison #}
{% if age > 18 %}...{% endif %}
{% if age >= 18 %}...{% endif %}
{% if age < 18 %}...{% endif %}
{% if age <= 18 %}...{% endif %}

{# Logical operators #}
{% if age >= 18 and country == 'US' %}...{% endif %}
{% if role == 'admin' or role == 'moderator' %}...{% endif %}
{% if not user %}...{% endif %}

{# Contains #}
{% if 'admin' in user.roles %}...{% endif %}
{% if user.email starts with 'admin@' %}...{% endif %}
{% if user.email ends with '@example.com' %}...{% endif %}

{# Null checks #}
{% if user is defined %}...{% endif %}
{% if user is null %}...{% endif %}
{% if user is not null %}...{% endif %}
```

### 2.6 Whitespace Control

```twig
{# Remove whitespace before #}
{{- variable }}

{# Remove whitespace after #}
{{ variable -}}

{# Remove whitespace both sides #}
{{- variable -}}

{# In control structures #}
{%- if condition -%}
    ...
{%- endif -%}
```

---

## 3. OxygenFramework View System {#oxygen-view-system}

### 3.1 Rendering Views from Controllers

#### Basic View Rendering

```php
<?php

namespace Oxygen\Controllers;

use Controller;

class HomeController extends Controller
{
    public function index()
    {
        // Render view without data
        return $this->view('pages/home');
    }
    
    public function about()
    {
        // Render view with data
        return $this->view('pages/about', [
            'title' => 'About Us',
            'description' => 'Learn more about our company'
        ]);
    }
}
```

#### Passing Multiple Variables

```php
public function products()
{
    $products = Product::all();
    $categories = Category::all();
    $featured = Product::where('featured', true);
    
    return $this->view('products/index', [
        'products' => $products,
        'categories' => $categories,
        'featured' => $featured,
        'title' => 'Our Products',
        'meta_description' => 'Browse our amazing products'
    ]);
}
```

#### Passing Objects

```php
public function show($id)
{
    $product = Product::find($id);
    $related = Product::where('category_id', $product->category_id);
    
    return $this->view('products/show', [
        'product' => $product,
        'related' => $related
    ]);
}
```

### 3.2 View File Extensions

OxygenFramework supports two file extensions:

1. **`.twig.html`** - Recommended (better IDE support)
2. **`.twig`** - Alternative

**Example:**
```
resources/views/products/index.twig.html  ✅ Recommended
resources/views/products/index.twig       ✅ Also works
```

### 3.3 View Paths

Views are loaded from multiple directories in order:

1. `resources/views/templates/{theme}/`
2. `resources/views/templates/{theme}/mobile/`
3. `resources/views/templates/{theme}/desktop/`
4. `resources/views/`
5. `resources/views/admin/`

**Example:**
```php
// Looks for: resources/views/products/index.twig.html
$this->view('products/index');

// Looks for: resources/views/admin/dashboard.twig.html
$this->view('admin/dashboard');
```

### 3.4 Template Caching

OxygenFramework automatically compiles Twig templates for performance:

```php
// In View.php setup
$this->twig = new Environment($loader, [
    'debug' => true,           // Enable debug mode
    'cache' => false,          // Disable cache for development
    'auto_reload' => true,     // Auto-reload on changes
    'autoescape' => 'html',    // Auto-escape HTML
]);
```

**Production Settings:**
```php
'debug' => false,
'cache' => '/path/to/cache',
'auto_reload' => false,
```

---

## 4. Global Variables {#global-variables}

OxygenFramework automatically provides global variables in all templates.

### 4.1 Application Variables

#### APP_URL
The application's base URL.

```twig
<a href="{{ APP_URL }}">Home</a>
<img src="{{ APP_URL }}/images/logo.png">
```

#### APP_NAME
The application's name from config.

```twig
<title>{{ APP_NAME }} - Dashboard</title>
<h1>Welcome to {{ APP_NAME }}</h1>
```

### 4.2 CSRF Variables

#### csrf_token
The CSRF token value.

```twig
<input type="hidden" name="_token" value="{{ csrf_token }}">
```

#### csrf_field
Complete CSRF hidden input field.

```twig
<form method="POST" action="/products/store">
    {{ csrf_field|raw }}
    <!-- form fields -->
</form>
```

### 4.3 Authentication Variables

#### auth.check
Boolean indicating if user is authenticated.

```twig
{% if auth.check %}
    <p>Welcome back!</p>
{% else %}
    <a href="/login">Login</a>
{% endif %}
```

#### auth.user
The authenticated user object.

```twig
{% if auth.check %}
    <p>Hello, {{ auth.user.name }}!</p>
    <p>Email: {{ auth.user.email }}</p>
{% endif %}
```

**Complete Example:**
```twig
<nav>
    {% if auth.check %}
        <div class="user-menu">
            <img src="{{ storage_url(auth.user.avatar) }}" alt="{{ auth.user.name }}">
            <span>{{ auth.user.name }}</span>
            <a href="/logout">Logout</a>
        </div>
    {% else %}
        <a href="/login">Login</a>
        <a href="/register">Register</a>
    {% endif %}
</nav>
```

### 4.4 Request Variables

#### _GET
Access GET parameters.

```twig
{# URL: /products?search=laptop&sort=price #}
<p>Search: {{ _GET.search }}</p>
<p>Sort: {{ _GET.sort }}</p>

{# With default value #}
<p>Page: {{ _GET.page|default(1) }}</p>
```

#### _POST
Access POST data (use with caution).

```twig
{# Better to use controller validation #}
{{ _POST.username }}
```

#### _SERVER
Access server variables.

```twig
<p>Request Method: {{ _SERVER.REQUEST_METHOD }}</p>
<p>User Agent: {{ _SERVER.HTTP_USER_AGENT }}</p>
```

---

## 5. Helper Functions {#helper-functions}

OxygenFramework provides powerful helper functions for common tasks.

### 5.1 Storage Functions

#### storage($path)
Generate URL for files in `public/storage/`.

```twig
{# Images #}
<img src="{{ storage('images/logo.png') }}" alt="Logo">
<img src="{{ storage('avatars/user123.jpg') }}" alt="Avatar">

{# Videos #}
<video src="{{ storage('videos/intro.mp4') }}" controls></video>

{# Documents #}
<a href="{{ storage('documents/brochure.pdf') }}" download>Download Brochure</a>

{# Dynamic paths #}
<img src="{{ storage(product.image_path) }}" alt="{{ product.name }}">
```

#### storage_url($path)
Alias for `storage()` - same functionality.

```twig
<img src="{{ storage_url('images/banner.jpg') }}">
```

**How It Works:**

The storage functions automatically handle:
- Subdirectory installations (`/oxygenframework/storage/...`)
- Production URLs (`https://example.com/storage/...`)
- Local development (`http://localhost/storage/...`)

**Generated URLs:**
```twig
{# Development #}
{{ storage('images/logo.png') }}
{# Output: /oxygenframework/storage/images/logo.png #}

{# Production #}
{{ storage('images/logo.png') }}
{# Output: https://example.com/storage/images/logo.png #}
```

### 5.2 Asset Functions

#### asset($path)
Generate URL for files in `public/`.

```twig
{# CSS #}
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

{# JavaScript #}
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/jquery.min.js') }}"></script>

{# Images in public #}
<img src="{{ asset('images/icon.png') }}">

{# Fonts #}
<link href="{{ asset('fonts/custom-font.woff2') }}" rel="preload">
```

**Directory Structure:**
```
public/
├── css/
│   └── app.css
├── js/
│   └── app.js
├── images/
│   └── icon.png
└── storage/      # For uploaded files
    └── avatars/
```

#### url($path)
Generate application URL.

```twig
{# Navigation links #}
<a href="{{ url('about') }}">About</a>
<a href="{{ url('products') }}">Products</a>
<a href="{{ url('contact') }}">Contact</a>

{# With parameters #}
<a href="{{ url('products/category/electronics') }}">Electronics</a>

{# Form actions #}
<form action="{{ url('products/store') }}" method="POST">
```

### 5.3 Theme Functions

#### theme_asset($path)
Get asset from current theme.

```twig
<link href="{{ theme_asset('css/theme.css') }}" rel="stylesheet">
<script src="{{ theme_asset('js/theme.js') }}"></script>
```

#### oxygen_css()
Render registered CSS assets.

```twig
<head>
    {{ oxygen_css()|raw }}
</head>
```

#### oxygen_js()
Render registered JavaScript assets.

```twig
<body>
    {{ oxygen_js()|raw }}
</body>
```

### 5.4 Flash Message Function

#### flash_display()
Display flash messages.

```twig
{# At top of layout #}
{{ flash_display()|raw }}

{# In specific location #}
<div class="messages">
    {{ flash_display()|raw }}
</div>
```

**Flash Message Types:**
- Success (green)
- Error (red)
- Warning (yellow)
- Info (blue)

**Controller Usage:**
```php
use Oxygen\Core\Flash;

Flash::success('Product created successfully!');
Flash::error('Failed to save product');
Flash::warning('Stock is running low');
Flash::info('New feature available');
```

---

## 6. CSRF Protection {#csrf-protection}

### 6.1 What is CSRF?

**Cross-Site Request Forgery (CSRF)** is an attack where malicious websites trick users into performing unwanted actions on your site.

**Example Attack:**
```html
<!-- Malicious site -->
<form action="https://yoursite.com/account/delete" method="POST">
    <input type="submit" value="Click for free prize!">
</form>
```

### 6.2 CSRF Protection in OxygenFramework

OxygenFramework automatically protects all POST requests with CSRF tokens.

#### Using csrf_field

**Recommended Method:**
```twig
<form method="POST" action="{{ url('products/store') }}">
    {{ csrf_field|raw }}
    
    <input type="text" name="name">
    <button type="submit">Submit</button>
</form>
```

**Generated HTML:**
```html
<form method="POST" action="/products/store">
    <input type="hidden" name="_token" value="abc123...xyz789">
    
    <input type="text" name="name">
    <button type="submit">Submit</button>
</form>
```

#### Using csrf_token

**Manual Method:**
```twig
<form method="POST" action="{{ url('products/store') }}">
    <input type="hidden" name="_token" value="{{ csrf_token }}">
    
    <input type="text" name="name">
    <button type="submit">Submit</button>
</form>
```

#### AJAX Requests

```twig
<script>
// Get CSRF token
const csrfToken = '{{ csrf_token }}';

// Fetch API
fetch('/api/products', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        name: 'Product Name',
        price: 99.99
    })
});

// jQuery
$.ajax({
    url: '/api/products',
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken
    },
    data: {
        name: 'Product Name',
        price: 99.99
    }
});
</script>
```

### 6.3 CSRF Best Practices

1. **Always use CSRF protection** for POST, PUT, DELETE requests
2. **Never disable CSRF** in production
3. **Use `csrf_field|raw`** for simplicity
4. **Include token in AJAX** requests
5. **Regenerate tokens** after login

**Complete Form Example:**
```twig
<form method="POST" action="{{ url('products/store') }}" enctype="multipart/form-data">
    {{ csrf_field|raw }}
    
    <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" required>
    </div>
    
    <div class="form-group">
        <label>Price</label>
        <input type="number" name="price" step="0.01" required>
    </div>
    
    <div class="form-group">
        <label>Image</label>
        <input type="file" name="image">
    </div>
    
    <button type="submit">Create Product</button>
</form>
```

---

## 7. Assets Management {#assets-management}

### 7.1 CSS Assets

#### Inline CSS
```twig
<style>
    .custom-class {
        color: blue;
        font-size: 16px;
    }
</style>
```

#### External CSS
```twig
<head>
    {# Framework CSS #}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    {# Third-party CSS #}
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {# Custom CSS #}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
```

#### Theme CSS
```twig
<link href="{{ theme_asset('css/theme.css') }}" rel="stylesheet">
```

### 7.2 JavaScript Assets

#### Inline JavaScript
```twig
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded');
    });
</script>
```

#### External JavaScript
```twig
<body>
    {# Framework JS #}
    <script src="{{ asset('js/app.js') }}"></script>
    
    {# Third-party JS #}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {# Custom JS #}
    <script src="{{ asset('js/custom.js') }}"></script>
</body>
```

### 7.3 Images

#### Static Images
```twig
{# In public/images/ #}
<img src="{{ asset('images/logo.png') }}" alt="Logo">
<img src="{{ asset('images/banner.jpg') }}" alt="Banner">

{# Responsive images #}
<img src="{{ asset('images/hero.jpg') }}" 
     srcset="{{ asset('images/hero-small.jpg') }} 480w,
             {{ asset('images/hero-medium.jpg') }} 768w,
             {{ asset('images/hero-large.jpg') }} 1200w"
     sizes="(max-width: 768px) 100vw, 50vw"
     alt="Hero Image">
```

#### Uploaded Images
```twig
{# In public/storage/ #}
<img src="{{ storage('products/product-123.jpg') }}" alt="Product">
<img src="{{ storage_url('avatars/user-456.jpg') }}" alt="Avatar">

{# Dynamic from database #}
<img src="{{ storage(product.image_path) }}" alt="{{ product.name }}">
```

### 7.4 Fonts

#### Web Fonts
```twig
<head>
    {# Google Fonts #}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    {# Custom fonts #}
    <style>
        @font-face {
            font-family: 'CustomFont';
            src: url('{{ asset('fonts/custom-font.woff2') }}') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
    </style>
</head>
```

### 7.5 Icons

#### Font Icons
```twig
{# Font Awesome #}
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<i class="fas fa-user"></i>
<i class="fas fa-shopping-cart"></i>
<i class="fas fa-heart"></i>
```

#### SVG Icons
```twig
{# Inline SVG #}
<svg width="24" height="24" viewBox="0 0 24 24">
    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
</svg>

{# SVG file #}
<img src="{{ asset('icons/check.svg') }}" alt="Check">
```

### 7.6 Asset Versioning

#### Cache Busting
```twig
{# Add version parameter #}
<link href="{{ asset('css/app.css') }}?v=1.2.3" rel="stylesheet">
<script src="{{ asset('js/app.js') }}?v=1.2.3"></script>

{# Using timestamp #}
<link href="{{ asset('css/app.css') }}?t={{ 'now'|date('U') }}" rel="stylesheet">
```

---

## 8. Storage & File Uploads {#storage-file-uploads}

### 8.1 Understanding Storage

OxygenFramework stores uploaded files in `public/storage/`:

```
public/storage/
├── avatars/          # User avatars
├── products/         # Product images
├── documents/        # PDF, DOC files
├── videos/           # Video files
└── temp/             # Temporary files
```

### 8.2 File Upload Forms

#### Basic File Upload
```twig
<form method="POST" action="{{ url('profile/avatar') }}" enctype="multipart/form-data">
    {{ csrf_field|raw }}
    
    <div class="form-group">
        <label>Profile Picture</label>
        <input type="file" name="avatar" accept="image/*" required>
    </div>
    
    <button type="submit">Upload</button>
</form>
```

#### Multiple File Upload
```twig
<form method="POST" action="{{ url('products/images') }}" enctype="multipart/form-data">
    {{ csrf_field|raw }}
    
    <div class="form-group">
        <label>Product Images</label>
        <input type="file" name="images[]" multiple accept="image/*">
    </div>
    
    <button type="submit">Upload Images</button>
</form>
```

#### File Upload with Preview
```twig
<form method="POST" action="{{ url('products/store') }}" enctype="multipart/form-data">
    {{ csrf_field|raw }}
    
    <div class="form-group">
        <label>Product Image</label>
        <input type="file" name="image" id="imageInput" accept="image/*">
        
        <div id="imagePreview" style="margin-top: 10px; display: none;">
            <img id="preview" src="" style="max-width: 200px;">
        </div>
    </div>
    
    <button type="submit">Create Product</button>
</form>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
```

### 8.3 Displaying Uploaded Files

#### Images
```twig
{# Single image #}
<img src="{{ storage(product.image) }}" alt="{{ product.name }}">

{# With fallback #}
<img src="{{ storage(product.image|default('images/placeholder.png')) }}" alt="{{ product.name }}">

{# Thumbnail and full size #}
<a href="{{ storage(product.image) }}" target="_blank">
    <img src="{{ storage(product.thumbnail) }}" alt="{{ product.name }}">
</a>
```

#### Videos
```twig
{# Video player #}
<video controls width="640" height="360">
    <source src="{{ storage(video.path) }}" type="video/mp4">
    Your browser does not support the video tag.
</video>

{# With poster #}
<video controls poster="{{ storage(video.thumbnail) }}">
    <source src="{{ storage(video.path) }}" type="video/mp4">
</video>
```

#### Documents
```twig
{# Download link #}
<a href="{{ storage(document.path) }}" download="{{ document.filename }}">
    <i class="fas fa-download"></i> Download {{ document.filename }}
</a>

{# PDF viewer #}
<iframe src="{{ storage(document.path) }}" width="100%" height="600px"></iframe>
```

#### Audio
```twig
<audio controls>
    <source src="{{ storage(audio.path) }}" type="audio/mpeg">
    Your browser does not support the audio tag.
</audio>
```

### 8.4 File Upload Controller

```php
<?php

namespace Oxygen\Controllers;

use Controller;
use Oxygen\Core\Request;
use Oxygen\Core\Response;
use Oxygen\Core\Storage\OxygenStorage;
use Oxygen\Core\Flash;

class ProductController extends Controller
{
    public function store()
    {
        $request = $this->app->make(Request::class);
        
        // Handle file upload
        $imagePath = null;
        if ($request->file('image')) {
            $result = OxygenStorage::put(
                $request->file('image'),
                'products'  // Directory in storage
            );
            
            if ($result['success']) {
                $imagePath = $result['path'];
            } else {
                Flash::error('Failed to upload image');
                Response::redirect('/products/create');
                return;
            }
        }
        
        // Create product with image path
        Product::create([
            'name' => $request->get('name'),
            'price' => $request->get('price'),
            'image' => $imagePath
        ]);
        
        Flash::success('Product created successfully!');
        Response::redirect('/products');
    }
}
```

### 8.5 File Type Validation

```twig
{# Images only #}
<input type="file" name="avatar" accept="image/png, image/jpeg, image/jpg">

{# Documents #}
<input type="file" name="document" accept=".pdf, .doc, .docx">

{# Videos #}
<input type="file" name="video" accept="video/mp4, video/webm">

{# Audio #}
<input type="file" name="audio" accept="audio/mpeg, audio/wav">

{# Multiple types #}
<input type="file" name="file" accept="image/*, .pdf, .doc, .docx">
```

### 8.6 File Size Limits

```twig
<form method="POST" action="{{ url('upload') }}" enctype="multipart/form-data">
    {{ csrf_field|raw }}
    
    <div class="form-group">
        <label>Upload File (Max 5MB)</label>
        <input type="file" name="file" id="fileInput">
        <small class="text-muted">Maximum file size: 5MB</small>
    </div>
    
    <button type="submit">Upload</button>
</form>

<script>
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
    
    if (file && file.size > maxSize) {
        alert('File size must be less than 5MB');
        this.value = '';
    }
});
</script>
```

### 8.7 Drag and Drop Upload

```twig
<div id="dropZone" style="border: 2px dashed #ccc; padding: 50px; text-align: center;">
    <p>Drag and drop files here or click to select</p>
    <input type="file" id="fileInput" style="display: none;" multiple>
</div>

<div id="fileList"></div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');

// Click to select
dropZone.addEventListener('click', () => fileInput.click());

// Drag over
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#007bff';
});

// Drag leave
dropZone.addEventListener('dragleave', () => {
    dropZone.style.borderColor = '#ccc';
});

// Drop
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#ccc';
    
    const files = e.dataTransfer.files;
    handleFiles(files);
});

// File input change
fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    fileList.innerHTML = '';
    Array.from(files).forEach(file => {
        const div = document.createElement('div');
        div.textContent = `${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
        fileList.appendChild(div);
    });
}
</script>
```

---

*This is Part 1 of the documentation. Due to length constraints, I'll continue with the remaining sections...*
