# 🌍 Localization (i18n)

OxygenFramework provides a convenient way to retrieve strings in various languages, allowing you to easily support multiple languages within your application.

## 🚀 Quick Start

### 1. Configuration

The default locale is set in `config/app.php` (or defaults to `en`).

```php
// config/app.php
return [
    'locale' => 'en',
    'fallback_locale' => 'en',
];
```

### 2. Creating Language Files

Language files are stored in the `resources/lang` directory. Within this directory, there should be a subdirectory for each language supported by the application:

```
/resources
    /lang
        /en
            messages.php
        /fr
            messages.php
        /es
            messages.php
```

**Example `resources/lang/en/messages.php`:**
```php
<?php

return [
    'welcome' => 'Welcome to our application!',
    'greeting' => 'Hello, :name!',
];
```

**Example `resources/lang/fr/messages.php`:**
```php
<?php

return [
    'welcome' => 'Bienvenue sur notre application!',
    'greeting' => 'Bonjour, :name!',
];
```

### 3. Retrieving Translation Strings

You can retrieve lines from language files using the `__` helper function. The method accepts the file and key using "dot" notation.

#### In PHP Controllers / Classes
```php
echo __('messages.welcome');
// Output: Welcome to our application!
```

#### In Twig Templates
```html
<h1>{{ __('messages.welcome') }}</h1>
```

### 4. Replacing Parameters

If you define place-holders in your translation strings, you can pass an array of replacements as the second argument.

**Definition:**
```php
'greeting' => 'Hello, :name!',
```

**Usage:**
```php
echo __('messages.greeting', ['name' => 'John']);
// Output: Hello, John!
```

**In Twig:**
```html
<p>{{ __('messages.greeting', {'name': user.name}) }}</p>
```

---

## 🔄 Switching Languages

OxygenFramework comes with a built-in middleware `OxygenLocaleMiddleware` that automatically handles language switching.

### Via URL Parameter
You can switch the language by simply adding `?lang=code` to any URL. The middleware will detect this, update the session, and switch the locale.

```html
<a href="?lang=en">English</a>
<a href="?lang=fr">Français</a>
<a href="?lang=es">Español</a>
```

### Via Session
The locale is stored in the session key `locale`. You can manually set this in your controller if needed:

```php
use Oxygen\Core\OxygenSession;

OxygenSession::put('locale', 'fr');
```

---

## 🛠️ Advanced Usage

### The `trans()` Helper
The `trans()` function is an alias for `__().`

```php
echo trans('messages.welcome');
```

### Fallback Locale
If a translation key is missing in the current locale, Oxygen will look for it in the `fallback_locale` (usually `en`). This ensures your users never see a broken key like `messages.welcome`.

### Pluralization
*Currently, simple string replacement is supported. Advanced pluralization (choice) is coming in v2.1.*

---

## 📝 Summary

| Feature | Description |
|---------|-------------|
| **File Structure** | `resources/lang/{locale}/{file}.php` |
| **Helper** | `__('file.key', [params])` |
| **Twig** | `{{ __('file.key') }}` |
| **Switching** | `?lang=fr` (Automatic via Middleware) |
