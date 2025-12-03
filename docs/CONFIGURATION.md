# Configuration

This guide covers all configuration options in OxygenFramework.

## Table of Contents

- [Environment Configuration](#environment-configuration)
- [Application Configuration](#application-configuration)
- [Database Configuration](#database-configuration)
- [Session Configuration](#session-configuration)
- [Error Configuration](#error-configuration)
- [Accessing Configuration](#accessing-configuration)

---

## Environment Configuration

The `.env` file contains environment-specific settings. **Never commit this file to version control!**

### Creating .env File

```bash
copy .env.example .env  # Windows
cp .env.example .env    # Linux/Mac
```

### Environment Variables

```env
# Application Settings
APP_NAME="OxygenFramework"
APP_URL=http://localhost:8000
APP_DEBUG=true

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oxygen
DB_USERNAME=root
DB_PASSWORD=

# Session Configuration
SESSION_LIFETIME=120
SESSION_DRIVER=file

# Error Handling
DEV_MODE=true

# Localization
DEFAULT_LOCALE=en
FALLBACK_LOCALE=en
```

### Accessing Environment Variables

```php
// Using env() helper
$appName = env('APP_NAME', 'Default Name');
$debug = env('APP_DEBUG', false);

// Check if variable exists
if (env('CUSTOM_VAR')) {
    // Variable is set
}
```

---

## Application Configuration

File: `config/app.php`

```php
<?php

return [
    // Application name
    'APP_NAME' => env('APP_NAME', 'OxygenFramework'),
    
    // Application URL
    'APP_URL' => env('APP_URL', 'http://localhost'),
    
    // Debug mode
    'APP_DEBUG' => env('APP_DEBUG', false),
    
    // Default template
    'default_template' => 'Tabler',
    
    // Service providers
    'providers' => [
        // Add your service providers here
    ],
];
```

### Accessing App Config

```php
// Get app name
$name = config('app.APP_NAME');

// Get debug mode
$debug = config('app.APP_DEBUG', false);

// Get service providers
$providers = config('app.providers', []);
```

---

## Database Configuration

File: `config/database.php`

```php
<?php

return [
    // Default connection
    'default' => env('DB_CONNECTION', 'mysql'),
    
    // Database connections
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'oxygen'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'dsn' => 'mysql:host=' . env('DB_HOST', '127.0.0.1') . 
                     ';port=' . env('DB_PORT', '3306') . 
                     ';dbname=' . env('DB_DATABASE', 'oxygen') . 
                     ';charset=utf8mb4',
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database/database.sqlite',
            'prefix' => '',
            'dsn' => 'sqlite:' . __DIR__ . '/../database/database.sqlite',
        ],
    ],
];
```

### Multiple Database Connections

```php
// In .env
DB_CONNECTION=mysql
DB_SECONDARY_CONNECTION=sqlite

// In config/database.php
'connections' => [
    'mysql' => [...],
    'secondary' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database/secondary.sqlite',
        'dsn' => 'sqlite:' . __DIR__ . '/../database/secondary.sqlite',
    ],
],
```

---

## Error Configuration

File: `config/errors.php`

```php
<?php

return [
    // Development mode (show detailed errors)
    'dev_mode' => env('DEV_MODE', false),
    
    // Error reporting level
    'error_reporting' => E_ALL,
    
    // Display errors
    'display_errors' => env('APP_DEBUG', false),
];
```

### Error Modes

**Development Mode** (`DEV_MODE=true`):
- Detailed error pages with stack traces
- File and line numbers shown
- Whoops error handler (if installed)

**Production Mode** (`DEV_MODE=false`):
- Generic error messages
- No sensitive information exposed
- Errors logged to files

---

## Accessing Configuration

### Using config() Helper

```php
// Get configuration value
$value = config('database.default');

// With default value
$value = config('app.custom_setting', 'default');

// Nested configuration
$host = config('database.connections.mysql.host');
```

### Using OxygenConfig Class

```php
use Oxygen\Core\OxygenConfig;

// Get configuration
$value = OxygenConfig::get('app.APP_NAME');

// With default
$value = OxygenConfig::get('app.custom', 'default');

// Check if exists
if (OxygenConfig::has('app.APP_NAME')) {
    // Config exists
}
```

---

## Custom Configuration Files

Create custom configuration files in `config/` directory.

### Example: config/services.php

```php
<?php

return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],
];
```

### Accessing Custom Config

```php
// Get Stripe key
$stripeKey = config('services.stripe.key');

// Get Mailgun domain
$mailgunDomain = config('services.mailgun.domain');
```

---

## Best Practices

### 1. Use Environment Variables for Sensitive Data

```php
// ✅ Good - Use environment variables
'api_key' => env('API_KEY'),

// ❌ Bad - Hardcode sensitive data
'api_key' => 'sk_live_abc123',
```

### 2. Provide Default Values

```php
// ✅ Good - Provide sensible defaults
$timeout = config('app.timeout', 30);

// ❌ Bad - No default, may cause errors
$timeout = config('app.timeout');
```

### 3. Cache Configuration in Production

```php
// In production, consider caching config
// (Future feature - not yet implemented)
```

### 4. Never Commit .env File

```gitignore
# .gitignore
.env
.env.backup
.env.production
```

### 5. Document Custom Configuration

```php
<?php

return [
    // Maximum upload size in MB
    'max_upload_size' => env('MAX_UPLOAD_SIZE', 10),
    
    // Allowed file extensions
    'allowed_extensions' => ['jpg', 'png', 'pdf'],
];
```

---

## Environment-Specific Configuration

### Development

`.env`:
```env
APP_DEBUG=true
DEV_MODE=true
DB_DATABASE=oxygen_dev
```

### Production

`.env`:
```env
APP_DEBUG=false
DEV_MODE=false
DB_DATABASE=oxygen_prod
```

### Testing

`.env.testing`:
```env
APP_DEBUG=true
DEV_MODE=true
DB_DATABASE=oxygen_test
```

---

## Configuration Reference

### Application (config/app.php)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `name` | string | "OxygenFramework" | Application name |
| `url` | string | "http://localhost" | Application URL |
| `debug` | boolean | false | Debug mode |
| `timezone` | string | "UTC" | Application timezone |
| `locale` | string | "en" | Default locale |

### Database (config/database.php)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `default` | string | "mysql" | Default connection |
| `connections.mysql.driver` | string | "mysql" | Database driver |
| `connections.mysql.dsn` | string | - | Database DSN |
| `connections.mysql.username` | string | "root" | Database username |
| `connections.mysql.password` | string | "" | Database password |

### Errors (config/errors.php)

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `dev_mode` | boolean | false | Development mode |
| `display_errors` | boolean | false | Display errors |
| `error_reporting` | integer | E_ALL | Error reporting level |

---

**See also:**
- [Getting Started](GETTING_STARTED.md)
- [Error Handling](ERROR_HANDLING.md)
- [Database & Models](DATABASE_MODELS.md)
