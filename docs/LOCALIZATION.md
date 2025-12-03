# Localization

This guide covers multi-language support, translation files, RTL (Right-to-Left) support, and locale management.

## Table of Contents

- [Introduction](#introduction)
- [Language Files](#language-files)
- [Using Translations](#using-translations)
- [Setting Locale](#setting-locale)
- [RTL Support](#rtl-support)
- [Best Practices](#best-practices)

---

## Introduction

OxygenFramework supports multi-language applications with built-in RTL (Right-to-Left) support for languages like Arabic, Hebrew, and Persian.

**File:** `app/Core/Lang.php`

---

## Language Files

### Directory Structure

```
resources/lang/
├── en/
│   ├── welcome.php
│   ├── messages.php
│   └── validation.php
├── fr/
│   ├── welcome.php
│   ├── messages.php
│   └── validation.php
└── ar/
    ├── welcome.php
    ├── messages.php
    └── validation.php
```

### Creating Language Files

**File:** `resources/lang/en/welcome.php`

```php
<?php

return [
    'title' => 'Welcome to OxygenFramework',
    'message' => 'Build amazing applications',
    'greeting' => 'Hello, :name!',
    'items' => 'You have :count items',
];
```

**File:** `resources/lang/fr/welcome.php`

```php
<?php

return [
    'title' => 'Bienvenue sur OxygenFramework',
    'message' => 'Créez des applications incroyables',
    'greeting' => 'Bonjour, :name!',
    'items' => 'Vous avez :count éléments',
];
```

**File:** `resources/lang/ar/welcome.php`

```php
<?php

return [
    'title' => 'مرحبا بك في OxygenFramework',
    'message' => 'قم ببناء تطبيقات رائعة',
    'greeting' => 'مرحبا، :name!',
    'items' => 'لديك :count عناصر',
];
```

---

## Using Translations

### In PHP

```php
// Simple translation
echo __('welcome.title');

// With replacements
echo __('welcome.greeting', ['name' => 'John']);
echo __('welcome.items', ['count' => 5]);

// Specific locale
echo __('welcome.title', [], 'fr');
```

### In Templates

```twig
{# Simple translation #}
<h1>{{ __('welcome.title') }}</h1>

{# With replacements #}
<p>{{ __('welcome.greeting', {name: user.name}) }}</p>
<p>{{ __('welcome.items', {count: items|length}) }}</p>
```

### In Controllers

```php
public function index()
{
    $title = __('welcome.title');
    $message = __('welcome.message');
    
    echo view('welcome', compact('title', 'message'));
}
```

---

## Setting Locale

### Get Current Locale

```php
use Oxygen\Core\Lang;

$locale = Lang::getLocale();  // Returns 'en', 'fr', 'ar', etc.
```

### Set Locale

```php
Lang::setLocale('fr');
```

### Locale Switcher

**Controller:** `app/Controllers/LanguageController.php`

```php
<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Core\Lang;
use Oxygen\Core\OxygenSession;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        // Validate locale
        $allowed = ['en', 'fr', 'ar', 'es'];
        
        if (in_array($locale, $allowed)) {
            Lang::setLocale($locale);
            OxygenSession::put('locale', $locale);
        }
        
        back();
    }
}
```

**Route:**

```php
Route::get($router, '/lang/(\w+)', 'LanguageController@switch');
```

**Template:**

```twig
<div class="language-switcher">
    <a href="/lang/en">English</a>
    <a href="/lang/fr">Français</a>
    <a href="/lang/ar">العربية</a>
</div>
```

---

## RTL Support

### RTL Languages

The framework automatically detects RTL languages:
- Arabic (ar)
- Hebrew (he)
- Persian (fa)
- Urdu (ur)
- Yiddish (yi)

### Check if RTL

```php
use Oxygen\Core\Lang;

if (Lang::isRTL()) {
    // Current language is RTL
}
```

### Get Text Direction

```php
$direction = Lang::getDirection();  // Returns 'rtl' or 'ltr'
```

### In Templates

```twig
<html dir="{{ text_direction }}">
<head>
    <title>{{ __('welcome.title') }}</title>
</head>
<body>
    <div class="{{ rtl_class('text-left', 'text-right') }}">
        {{ __('welcome.message') }}
    </div>
</body>
</html>
```

### RTL Helper Function

```twig
{# Returns first value for LTR, second for RTL #}
<div class="{{ rtl_class('float-left', 'float-right') }}">
    Content
</div>

{# CSS example #}
<style>
    .content {
        text-align: {{ rtl_class('left', 'right') }};
        margin-{{ rtl_class('left', 'right') }}: 20px;
    }
</style>
```

### Direction Functions

```twig
{{ direction() }}           {# Returns 'ltr' or 'rtl' #}
{{ locale() }}             {# Returns current locale #}
{{ is_rtl }}               {# Boolean, true if RTL #}
```

---

## Best Practices

### 1. Use Descriptive Keys

```php
// Good
'welcome.greeting' => 'Hello, :name!'
'auth.login_success' => 'Login successful'

// Avoid
'msg1' => 'Hello'
'text' => 'Success'
```

### 2. Group Related Translations

```
resources/lang/en/
├── auth.php        # Authentication messages
├── validation.php  # Validation messages
├── messages.php    # General messages
└── errors.php      # Error messages
```

### 3. Use Placeholders

```php
// Good - flexible
'greeting' => 'Hello, :name! You have :count messages.'

// Avoid - hardcoded
'greeting' => 'Hello, John! You have 5 messages.'
```

### 4. Provide Fallbacks

```php
// With default value
$message = __('custom.message', [], 'en') ?: 'Default message';
```

### 5. Test RTL Layout

```twig
{# Always test with RTL languages #}
<html dir="{{ text_direction }}">
    <body class="{{ rtl_class('ltr-layout', 'rtl-layout') }}">
        <!-- content -->
    </body>
</html>
```

---

## Complete Example

### Language Files

**resources/lang/en/app.php**

```php
<?php

return [
    'welcome' => 'Welcome',
    'login' => 'Login',
    'logout' => 'Logout',
    'dashboard' => 'Dashboard',
    'posts' => 'Posts',
    'create_post' => 'Create Post',
];
```

**resources/lang/ar/app.php**

```php
<?php

return [
    'welcome' => 'مرحبا',
    'login' => 'تسجيل الدخول',
    'logout' => 'تسجيل الخروج',
    'dashboard' => 'لوحة التحكم',
    'posts' => 'المقالات',
    'create_post' => 'إنشاء مقال',
];
```

### Template

```twig
<!DOCTYPE html>
<html dir="{{ text_direction }}" lang="{{ current_locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('app.dashboard') }}</title>
    <style>
        body {
            direction: {{ direction() }};
            text-align: {{ rtl_class('left', 'right') }};
        }
        .sidebar {
            float: {{ rtl_class('left', 'right') }};
        }
    </style>
</head>
<body>
    <nav>
        <a href="/dashboard">{{ __('app.dashboard') }}</a>
        <a href="/posts">{{ __('app.posts') }}</a>
        <a href="/logout">{{ __('app.logout') }}</a>
    </nav>
    
    <div class="language-switcher">
        <a href="/lang/en">English</a>
        <a href="/lang/ar">العربية</a>
    </div>
    
    <main>
        <h1>{{ __('app.welcome') }}</h1>
    </main>
</body>
</html>
```

---

## See Also

- [Views and Templates](VIEWS_TEMPLATES.md)
- [Configuration](CONFIGURATION.md)
