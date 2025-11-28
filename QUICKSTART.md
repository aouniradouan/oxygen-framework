# OxygenFramework - Quick Start Guide

## 🚀 Getting Started

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Setup Database

```bash
# Run migrations (creates tables and default admin user)
php oxygen migrate
```

**Default Admin Credentials:**
- Email: `admin@oxygen.local`
- Password: `password`

### 3. Start Development Server

```bash
# Terminal 1: Start Vite dev server (for Tailwind CSS)
npm run dev

# Terminal 2: Start PHP server
php -S localhost:8000 -t public
```

### 4. Access the Application

- **Homepage**: http://localhost:8000
- **Login**: http://localhost:8000/login
- **Register**: http://localhost:8000/register
- **Dashboard**: http://localhost:8000/dashboard (after login)

---

## 🌍 Testing RTL Support

### Change Language to Arabic

Add this route to test RTL:

```php
// In routes/web.php
Route::get($router, '/lang/{locale}', function($locale) {
    \Oxygen\Core\Lang::setLocale($locale);
    \Oxygen\Core\OxygenSession::put('locale', $locale);
    redirect('/');
});
```

Then visit: `http://localhost:8000/lang/ar`

### Or Set in Code

```php
// In any controller or route
\Oxygen\Core\Lang::setLocale('ar'); // Arabic
\Oxygen\Core\Lang::setLocale('he'); // Hebrew
\Oxygen\Core\Lang::setLocale('fa'); // Persian
```

---

## ✅ Features Checklist

- [x] RTL Support (Arabic, Hebrew, Persian)
- [x] Authentication System
- [x] Role-Based Access Control
- [x] Permissions System
- [x] Tailwind CSS Integration
- [x] Vite Build System
- [x] Beautiful Auth Pages
- [x] Smooth Animations
- [x] Default Migrations
- [x] Helper Functions

---

## 📁 Key Files

### Routes
- `routes/web.php` - All application routes

### Controllers
- `app/Controllers/AuthController.php` - Authentication logic

### Models
- `app/Models/User.php` - User model with roles
- `app/Models/Role.php` - Role model
- `app/Models/Permission.php` - Permission model

### Views
- `resources/views/auth/login.twig.html` - Login page
- `resources/views/auth/register.twig.html` - Registration page
- `resources/views/dashboard.twig.html` - Dashboard

### Migrations
- `database/migrations/2024_11_28_000001_create_users_table.php`
- `database/migrations/2024_11_28_000002_create_roles_table.php`
- `database/migrations/2024_11_28_000003_create_permissions_table.php`
- `database/migrations/2024_11_28_000004_create_role_permission_table.php`
- `database/migrations/2024_11_28_000005_seed_default_admin_user.php`

---

## 🎨 Customization

### Change Colors

Edit `tailwind.config.js`:

```javascript
colors: {
  primary: {
    // Your custom primary colors
  },
  secondary: {
    // Your custom secondary colors
  }
}
```

### Add New Permissions

```php
// In database or via code
Permission::create([
    'name' => 'Delete Posts',
    'slug' => 'posts.delete',
    'description' => 'Can delete posts'
]);
```

### Protect Routes

```php
// Using middleware
Route::get($router, '/admin', function() {
    $auth = auth();
    if (!$auth->hasRole('admin')) {
        redirect('/dashboard');
    }
    // Admin only content
});
```

---

## 🐛 Troubleshooting

### Vite Not Working?
Make sure Vite dev server is running: `npm run dev`

### Migrations Failing?
Check database connection in `.env` file

### Auth Not Working?
Run `composer dump-autoload` to reload helper functions

### CSS Not Loading?
Ensure Vite is running on port 5173

---

## 📚 Documentation

See `walkthrough.md` for complete documentation of all features.
