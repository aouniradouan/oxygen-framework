# Views and Templates

This guide covers the Twig templating engine, view rendering, template inheritance, global variables, and helper functions.

## Table of Contents

- [Introduction](#introduction)
- [Rendering Views](#rendering-views)
- [Template Syntax](#template-syntax)
- [Template Inheritance](#template-inheritance)
- [Global Variables](#global-variables)
- [Global Functions](#global-functions)
- [Localization](#localization)
- [Best Practices](#best-practices)

---

## Introduction

OxygenFramework uses Twig as its templating engine, providing a powerful and secure way to render HTML.

### Template Locations

Templates are stored in `resources/views/`:

```
resources/views/
├── layouts/
│   └── app.twig
├── components/
│   ├── header.twig
│   └── footer.twig
├── posts/
│   ├── index.twig.html
│   ├── show.twig.html
│   └── create.twig.html
└── errors/
    ├── 404.twig.html
    └── 500.twig.html
```

---

## Rendering Views

### In Controllers

```php
// Simple view
echo view('welcome');

// With data
echo view('posts/index', ['posts' => $posts]);

// With compact
$posts = Post::all();
echo view('posts/index', compact('posts'));

// Using View class
$view = app()->make(\Oxygen\Core\View::class);
echo $view->render('posts/index', ['posts' => $posts]);
```

### In Routes

```php
Route::get($router, '/', function() {
    echo view('welcome');
});

Route::get($router, '/about', function() {
    echo view('pages/about', ['title' => 'About Us']);
});
```

---

## Template Syntax

### Variables

```twig
{# Output variable #}
{{ title }}
{{ post.title }}
{{ user.name }}

{# With default value #}
{{ post.excerpt|default('No excerpt available') }}
```

### Comments

```twig
{# This is a comment #}

{#
    Multi-line
    comment
#}
```

### Control Structures

**If Statements**

```twig
{% if posts|length > 0 %}
    <p>Found {{ posts|length }} posts</p>
{% else %}
    <p>No posts found</p>
{% endif %}

{# Multiple conditions #}
{% if user.role == 'admin' %}
    <p>Admin Panel</p>
{% elseif user.role == 'editor' %}
    <p>Editor Panel</p>
{% else %}
    <p>User Panel</p>
{% endif %}
```

**For Loops**

```twig
{% for post in posts %}
    <article>
        <h2>{{ post.title }}</h2>
        <p>{{ post.content }}</p>
    </article>
{% endfor %}

{# With else #}
{% for post in posts %}
    <article>{{ post.title }}</article>
{% else %}
    <p>No posts available</p>
{% endfor %}

{# Loop variables #}
{% for post in posts %}
    <div>
        Post {{ loop.index }} of {{ loop.length }}
        {% if loop.first %}(First){% endif %}
        {% if loop.last %}(Last){% endif %}
    </div>
{% endfor %}
```

### Filters

```twig
{# String filters #}
{{ title|upper }}
{{ title|lower }}
{{ title|capitalize }}
{{ content|length }}

{# Array filters #}
{{ posts|length }}
{{ posts|first }}
{{ posts|last }}

{# Date filters #}
{{ post.created_at|date('Y-m-d') }}
{{ post.created_at|date('F j, Y') }}

{# Default value #}
{{ post.excerpt|default('No excerpt') }}

{# Raw (unescaped) #}
{{ html_content|raw }}
```

---

## Template Inheritance

### Base Layout

**File:** `resources/views/layouts/app.twig`

```twig
<!DOCTYPE html>
<html lang="en" dir="{{ text_direction }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{% endblock %} - {{ APP_NAME }}</title>
    {{ oxygen_css() }}
    {% block styles %}{% endblock %}
</head>
<body>
    <header>
        {% include 'components/header.twig' %}
    </header>
    
    <main>
        {{ flash_display() }}
        {% block content %}{% endblock %}
    </main>
    
    <footer>
        {% include 'components/footer.twig' %}
    </footer>
    
    {{ oxygen_js() }}
    {% block scripts %}{% endblock %}
</body>
</html>
```

### Extending Layout

**File:** `resources/views/posts/index.twig.html`

```twig
{% extends "layouts/app.twig" %}

{% block title %}Posts{% endblock %}

{% block content %}
    <h1>All Posts</h1>
    
    {% for post in posts %}
        <article>
            <h2>{{ post.title }}</h2>
            <p>{{ post.content }}</p>
            <a href="/posts/{{ post.id }}">Read More</a>
        </article>
    {% endfor %}
{% endblock %}

{% block scripts %}
    <script src="{{ asset('js/posts.js') }}"></script>
{% endblock %}
```

### Including Partials

```twig
{# Include component #}
{% include 'components/header.twig' %}

{# Include with variables #}
{% include 'components/post-card.twig' with {'post': post} %}
```

---

## Global Variables

Available in all templates:

### Application Variables

```twig
{{ APP_URL }}        {# Application URL #}
{{ APP_NAME }}       {# Application name #}
```

### Authentication

```twig
{% if auth.check %}
    <p>Welcome, {{ auth.user.name }}</p>
{% else %}
    <a href="/login">Login</a>
{% endif %}
```

### Localization

```twig
{{ current_locale }}     {# Current locale code (e.g., 'en', 'fr') #}
{{ text_direction }}     {# Text direction ('ltr' or 'rtl') #}
{{ is_rtl }}            {# Boolean, true if RTL language #}
```

### CSRF Protection

```twig
<form method="POST">
    {{ csrf_field|raw }}
    <!-- form fields -->
</form>

{# Or manually #}
<input type="hidden" name="_token" value="{{ csrf_token }}">
```

---

## Global Functions

### Authentication

```twig
{% if auth_check() %}
    <p>Logged in as {{ auth_user().name }}</p>
{% endif %}
```

### URLs and Assets

```twig
{# Generate URL #}
<a href="{{ url('/posts') }}">Posts</a>

{# Asset URL #}
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/app.js') }}"></script>

{# Storage URL #}
<img src="{{ storage('uploads/avatar.jpg') }}" alt="Avatar">
<img src="{{ storage_url('uploads/avatar.jpg') }}" alt="Avatar">

{# Theme asset #}
<link rel="stylesheet" href="{{ theme_asset('css/style.css') }}">
```

### Flash Messages

```twig
{# Display all flash messages #}
{{ flash_display() }}

{# Custom flash display #}
{% if flash.success %}
    <div class="alert alert-success">{{ flash.success }}</div>
{% endif %}

{% if flash.error %}
    <div class="alert alert-danger">{{ flash.error }}</div>
{% endif %}
```

### Localization

```twig
{# Translate #}
{{ __('welcome.message') }}

{# With replacements #}
{{ __('welcome.greeting', {name: user.name}) }}

{# RTL helpers #}
<div class="{{ rtl_class('text-left', 'text-right') }}">
    Content
</div>

{{ direction() }}  {# Returns 'ltr' or 'rtl' #}
{{ locale() }}     {# Returns current locale #}
```

### JSON Helpers

```twig
{# Decode JSON #}
{% set data = json_decode(post.metadata) %}
{{ data.key }}
```

### Framework Assets

```twig
{# Include framework CSS #}
{{ oxygen_css() }}

{# Include framework JS #}
{{ oxygen_js() }}
```

---

## Localization

### Translation Files

**File:** `resources/lang/en/welcome.php`

```php
<?php

return [
    'message' => 'Welcome to OxygenFramework',
    'greeting' => 'Hello, :name!',
];
```

### Using Translations

```twig
{# Simple translation #}
<h1>{{ __('welcome.message') }}</h1>

{# With replacements #}
<p>{{ __('welcome.greeting', {name: user.name}) }}</p>

{# In PHP #}
echo __('welcome.message');
echo __('welcome.greeting', ['name' => $user->name]);
```

### RTL Support

```twig
<html dir="{{ text_direction }}">
    <body class="{{ rtl_class('ltr-class', 'rtl-class') }}">
        <div class="{{ rtl_class('text-left', 'text-right') }}">
            Content
        </div>
    </body>
</html>
```

---

## Best Practices

### 1. Escape Output

```twig
{# Good - auto-escaped #}
{{ user.name }}

{# Only use raw for trusted content #}
{{ trusted_html|raw }}
```

### 2. Use Template Inheritance

```twig
{# Good - DRY principle #}
{% extends "layouts/app.twig" %}

{# Avoid - repeating layout code #}
```

### 3. Keep Logic Out of Templates

```twig
{# Good - logic in controller #}
{% for post in publishedPosts %}
    {{ post.title }}
{% endfor %}

{# Avoid - logic in template #}
{% for post in posts %}
    {% if post.status == 'published' %}
        {{ post.title }}
    {% endif %}
{% endfor %}
```

### 4. Use Components

```twig
{# Create reusable components #}
{% include 'components/post-card.twig' with {'post': post} %}
```

### 5. Name Blocks Clearly

```twig
{% block page_title %}{% endblock %}
{% block page_content %}{% endblock %}
{% block page_scripts %}{% endblock %}
```

---

## See Also

- [Localization](LOCALIZATION.md)
- [Helper Functions](HELPERS.md)
- [Controllers](ROUTING.md#controllers)
