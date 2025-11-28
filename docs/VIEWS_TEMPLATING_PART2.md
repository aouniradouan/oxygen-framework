# Views & Templating - Complete Guide (Part 2)
## Layouts, Components, Forms & Advanced Techniques

---

## 9. Layouts & Inheritance {#layouts-inheritance}

### 9.1 Understanding Template Inheritance

Template inheritance allows you to build a base "skeleton" template that contains common elements and define **blocks** that child templates can override.

**Benefits:**
- **DRY (Don't Repeat Yourself)** - Write common code once
- **Consistency** - Same header/footer across all pages
- **Maintainability** - Update layout in one place
- **Flexibility** - Override specific sections per page

### 9.2 Creating a Master Layout

#### Basic Layout Structure

**File:** `resources/views/layouts/app.twig.html`

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {# Dynamic title #}
    <title>{% block title %}{{ APP_NAME }}{% endblock %}</title>
    
    {# Meta tags #}
    {% block meta %}
        <meta name="description" content="Welcome to {{ APP_NAME }}">
        <meta name="keywords" content="oxygen, framework, php">
    {% endblock %}
    
    {# CSS #}
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    {# Additional CSS per page #}
    {% block styles %}{% endblock %}
</head>
<body class="bg-gray-50">
    {# Flash messages #}
    {{ flash_display()|raw }}
    
    {# Navigation #}
    {% include 'components/navbar.twig.html' %}
    
    {# Main content area #}
    <main class="container mx-auto px-4 py-8">
        {% block content %}
            {# Default content #}
            <p>No content defined</p>
        {% endblock %}
    </main>
    
    {# Footer #}
    {% include 'components/footer.twig.html' %}
    
    {# JavaScript #}
    <script src="{{ asset('js/app.js') }}"></script>
    
    {# Additional JS per page #}
    {% block scripts %}{% endblock %}
</body>
</html>
```

### 9.3 Extending Layouts

#### Simple Page Extension

**File:** `resources/views/pages/about.twig.html`

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}About Us - {{ APP_NAME }}{% endblock %}

{% block content %}
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-6">About Us</h1>
        
        <p class="text-lg text-gray-700 mb-4">
            Welcome to {{ APP_NAME }}! We are a leading company in...
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-2">Our Mission</h3>
                <p>To provide the best service...</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-2">Our Vision</h3>
                <p>To be the industry leader...</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-xl font-semibold mb-2">Our Values</h3>
                <p>Integrity, Innovation, Excellence...</p>
            </div>
        </div>
    </div>
{% endblock %}
```

#### Page with Custom CSS and JS

**File:** `resources/views/products/index.twig.html`

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}Products - {{ APP_NAME }}{% endblock %}

{% block meta %}
    {{ parent() }}
    <meta name="description" content="Browse our amazing products">
{% endblock %}

{% block styles %}
    <link href="{{ asset('css/products.css') }}" rel="stylesheet">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
    </style>
{% endblock %}

{% block content %}
    <div class="mb-8">
        <h1 class="text-4xl font-bold">Our Products</h1>
        <p class="text-gray-600 mt-2">Discover our amazing collection</p>
    </div>
    
    {# Search form #}
    <form method="GET" class="mb-6">
        <input type="search" name="search" value="{{ _GET.search }}" 
               placeholder="Search products..." 
               class="border rounded px-4 py-2 w-full max-w-md">
    </form>
    
    {# Products grid #}
    <div class="product-grid">
        {% for product in products %}
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <img src="{{ storage(product.image) }}" alt="{{ product.name }}" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2">{{ product.name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ product.description|slice(0, 100) }}...</p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-blue-600">${{ product.price }}</span>
                        <a href="{{ url('products/' ~ product.id) }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        {% else %}
            <p class="col-span-full text-center text-gray-500">No products found</p>
        {% endfor %}
    </div>
{% endblock %}

{% block scripts %}
    <script src="{{ asset('js/products.js') }}"></script>
    <script>
        // Product-specific JavaScript
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                console.log('Product clicked:', this.dataset.productId);
            });
        });
    </script>
{% endblock %}
```

### 9.4 Multiple Layouts

#### Admin Layout

**File:** `resources/views/layouts/admin.twig.html`

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Admin Panel - {{ APP_NAME }}{% endblock %}</title>
    
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
    {% block styles %}{% endblock %}
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        {# Sidebar #}
        <aside class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-bold">{{ APP_NAME }}</h2>
                <p class="text-sm text-gray-400">Admin Panel</p>
            </div>
            
            <nav class="mt-8">
                <a href="{{ url('admin/dashboard') }}" class="block px-4 py-2 hover:bg-gray-700">
                    Dashboard
                </a>
                <a href="{{ url('admin/products') }}" class="block px-4 py-2 hover:bg-gray-700">
                    Products
                </a>
                <a href="{{ url('admin/users') }}" class="block px-4 py-2 hover:bg-gray-700">
                    Users
                </a>
                <a href="{{ url('admin/settings') }}" class="block px-4 py-2 hover:bg-gray-700">
                    Settings
                </a>
            </nav>
        </aside>
        
        {# Main content #}
        <div class="flex-1 flex flex-col overflow-hidden">
            {# Top bar #}
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h1 class="text-2xl font-semibold">{% block page_title %}Dashboard{% endblock %}</h1>
                    
                    <div class="flex items-center space-x-4">
                        <span>{{ auth.user.name }}</span>
                        <a href="{{ url('logout') }}" class="text-red-600 hover:text-red-800">Logout</a>
                    </div>
                </div>
            </header>
            
            {# Content area #}
            <main class="flex-1 overflow-y-auto p-6">
                {{ flash_display()|raw }}
                
                {% block content %}{% endblock %}
            </main>
        </div>
    </div>
    
    <script src="{{ asset('js/admin.js') }}"></script>
    {% block scripts %}{% endblock %}
</body>
</html>
```

#### Using Admin Layout

**File:** `resources/views/admin/products/index.twig.html`

```twig
{% extends 'layouts/admin.twig.html' %}

{% block page_title %}Manage Products{% endblock %}

{% block content %}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Products</h2>
            <a href="{{ url('admin/products/create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Add New Product
            </a>
        </div>
        
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">ID</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Price</th>
                    <th class="px-6 py-3 text-left">Stock</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                {% for product in products %}
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-6 py-4">{{ product.id }}</td>
                        <td class="px-6 py-4">{{ product.name }}</td>
                        <td class="px-6 py-4">${{ product.price }}</td>
                        <td class="px-6 py-4">{{ product.stock }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ url('admin/products/' ~ product.id ~ '/edit') }}" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>
                            <a href="{{ url('admin/products/' ~ product.id ~ '/delete') }}" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
{% endblock %}
```

### 9.5 Block Features

#### Parent Block Content

```twig
{% block meta %}
    {# Include parent block content #}
    {{ parent() }}
    
    {# Add additional meta tags #}
    <meta property="og:title" content="My Page">
    <meta property="og:description" content="Description">
{% endblock %}
```

#### Nested Blocks

```twig
{% block content %}
    <div class="container">
        {% block inner_content %}
            {# Can be overridden by child templates #}
        {% endblock %}
    </div>
{% endblock %}
```

#### Block Shortcuts

```twig
{# Short syntax for simple blocks #}
{% block title 'About Us - ' ~ APP_NAME %}

{# Equivalent to: #}
{% block title %}About Us - {{ APP_NAME }}{% endblock %}
```

---

## 10. Components & Partials {#components-partials}

### 10.1 Creating Reusable Components

Components are small, reusable template pieces that can be included in multiple places.

#### Navbar Component

**File:** `resources/views/components/navbar.twig.html`

```twig
<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            {# Logo #}
            <a href="{{ url('/') }}" class="flex items-center space-x-2">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10">
                <span class="text-xl font-bold">{{ APP_NAME }}</span>
            </a>
            
            {# Navigation links #}
            <div class="hidden md:flex space-x-6">
                <a href="{{ url('/') }}" class="text-gray-700 hover:text-blue-600">Home</a>
                <a href="{{ url('products') }}" class="text-gray-700 hover:text-blue-600">Products</a>
                <a href="{{ url('about') }}" class="text-gray-700 hover:text-blue-600">About</a>
                <a href="{{ url('contact') }}" class="text-gray-700 hover:text-blue-600">Contact</a>
            </div>
            
            {# User menu #}
            <div class="flex items-center space-x-4">
                {% if auth.check %}
                    <div class="relative group">
                        <button class="flex items-center space-x-2">
                            <img src="{{ storage_url(auth.user.avatar|default('avatars/default.png')) }}" 
                                 alt="{{ auth.user.name }}" 
                                 class="w-8 h-8 rounded-full">
                            <span>{{ auth.user.name }}</span>
                        </button>
                        
                        {# Dropdown menu #}
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block">
                            <a href="{{ url('profile') }}" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                            <a href="{{ url('settings') }}" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                            <hr>
                            <a href="{{ url('logout') }}" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                {% else %}
                    <a href="{{ url('login') }}" class="text-gray-700 hover:text-blue-600">Login</a>
                    <a href="{{ url('register') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Sign Up</a>
                {% endif %}
            </div>
            
            {# Mobile menu button #}
            <button class="md:hidden" onclick="toggleMobileMenu()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        {# Mobile menu #}
        <div id="mobileMenu" class="hidden md:hidden pb-4">
            <a href="{{ url('/') }}" class="block py-2 text-gray-700 hover:text-blue-600">Home</a>
            <a href="{{ url('products') }}" class="block py-2 text-gray-700 hover:text-blue-600">Products</a>
            <a href="{{ url('about') }}" class="block py-2 text-gray-700 hover:text-blue-600">About</a>
            <a href="{{ url('contact') }}" class="block py-2 text-gray-700 hover:text-blue-600">Contact</a>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}
</script>
```

#### Footer Component

**File:** `resources/views/components/footer.twig.html`

```twig
<footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            {# Company info #}
            <div>
                <h3 class="text-lg font-semibold mb-4">{{ APP_NAME }}</h3>
                <p class="text-gray-400 text-sm">
                    Your trusted partner for quality products and services.
                </p>
            </div>
            
            {# Quick links #}
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="{{ url('about') }}" class="text-gray-400 hover:text-white">About Us</a></li>
                    <li><a href="{{ url('products') }}" class="text-gray-400 hover:text-white">Products</a></li>
                    <li><a href="{{ url('contact') }}" class="text-gray-400 hover:text-white">Contact</a></li>
                    <li><a href="{{ url('faq') }}" class="text-gray-400 hover:text-white">FAQ</a></li>
                </ul>
            </div>
            
            {# Support #}
            <div>
                <h3 class="text-lg font-semibold mb-4">Support</h3>
                <ul class="space-y-2">
                    <li><a href="{{ url('help') }}" class="text-gray-400 hover:text-white">Help Center</a></li>
                    <li><a href="{{ url('terms') }}" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                    <li><a href="{{ url('privacy') }}" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                </ul>
            </div>
            
            {# Contact #}
            <div>
                <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li>Email: info@example.com</li>
                    <li>Phone: +1 234 567 8900</li>
                    <li>Address: 123 Main St, City, Country</li>
                </ul>
            </div>
        </div>
        
        {# Copyright #}
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; {{ 'now'|date('Y') }} {{ APP_NAME }}. All rights reserved.</p>
        </div>
    </div>
</footer>
```

### 10.2 Including Components

#### Basic Include

```twig
{# Include navbar #}
{% include 'components/navbar.twig.html' %}

{# Include footer #}
{% include 'components/footer.twig.html' %}
```

#### Include with Variables

```twig
{# Pass variables to component #}
{% include 'components/product-card.twig.html' with {
    'product': product,
    'showPrice': true
} %}
```

#### Conditional Include

```twig
{# Include only if file exists #}
{% include 'components/banner.twig.html' ignore missing %}

{# Include based on condition #}
{% if showSidebar %}
    {% include 'components/sidebar.twig.html' %}
{% endif %}
```

### 10.3 Product Card Component

**File:** `resources/views/components/product-card.twig.html`

```twig
{# Product card component #}
<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
    {# Product image #}
    <div class="relative">
        <img src="{{ storage(product.image) }}" 
             alt="{{ product.name }}" 
             class="w-full h-48 object-cover">
        
        {# Badge #}
        {% if product.is_new %}
            <span class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold">
                NEW
            </span>
        {% endif %}
        
        {% if product.discount > 0 %}
            <span class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                -{{ product.discount }}%
            </span>
        {% endif %}
    </div>
    
    {# Product info #}
    <div class="p-4">
        <h3 class="font-semibold text-lg mb-2 truncate">{{ product.name }}</h3>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ product.description }}</p>
        
        {# Rating #}
        <div class="flex items-center mb-4">
            {% for i in 1..5 %}
                <svg class="w-4 h-4 {% if i <= product.rating %}text-yellow-400{% else %}text-gray-300{% endif %}" 
                     fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            {% endfor %}
            <span class="ml-2 text-sm text-gray-600">({{ product.reviews_count }})</span>
        </div>
        
        {# Price #}
        <div class="flex justify-between items-center">
            <div>
                {% if product.discount > 0 %}
                    <span class="text-gray-400 line-through text-sm">${{ product.original_price }}</span>
                    <span class="text-2xl font-bold text-red-600 ml-2">${{ product.price }}</span>
                {% else %}
                    <span class="text-2xl font-bold text-gray-900">${{ product.price }}</span>
                {% endif %}
            </div>
            
            {# Action button #}
            <a href="{{ url('products/' ~ product.id) }}" 
               class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                View
            </a>
        </div>
    </div>
</div>
```

**Usage:**
```twig
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {% for product in products %}
        {% include 'components/product-card.twig.html' with {'product': product} %}
    {% endfor %}
</div>
```

### 10.4 Alert Component

**File:** `resources/views/components/alert.twig.html`

```twig
{# Alert component
   Usage: {% include 'components/alert.twig.html' with {
       'type': 'success',  // success, error, warning, info
       'message': 'Your message here'
   } %}
#}

{% set alertClasses = {
    'success': 'bg-green-100 border-green-400 text-green-700',
    'error': 'bg-red-100 border-red-400 text-red-700',
    'warning': 'bg-yellow-100 border-yellow-400 text-yellow-700',
    'info': 'bg-blue-100 border-blue-400 text-blue-700'
} %}

{% set icons = {
    'success': '✓',
    'error': '✕',
    'warning': '⚠',
    'info': 'ℹ'
} %}

<div class="border-l-4 p-4 mb-4 {{ alertClasses[type|default('info')] }}" role="alert">
    <div class="flex items-center">
        <span class="text-xl mr-3">{{ icons[type|default('info')] }}</span>
        <p>{{ message }}</p>
    </div>
</div>
```

**Usage:**
```twig
{% include 'components/alert.twig.html' with {
    'type': 'success',
    'message': 'Product created successfully!'
} %}

{% include 'components/alert.twig.html' with {
    'type': 'error',
    'message': 'Failed to save product'
} %}
```

---

*Continuing with Part 3 covering Forms, Authentication, Flash Messages, and Advanced Techniques...*
