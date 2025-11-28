# Views & Templating - Complete Guide (Part 4)
## Advanced Techniques, Best Practices & Complete Examples

---

## 14. Advanced Techniques {#advanced-techniques}

### 14.1 Macros (Reusable Template Functions)

Macros are like functions in templates - they allow you to create reusable template snippets with parameters.

#### Creating Macros

**File:** `resources/views/macros/forms.twig.html`

```twig
{# Text input macro #}
{% macro input(name, label, value='', type='text', required=false) %}
    <div class="form-group mb-4">
        <label for="{{ name }}" class="block text-gray-700 font-semibold mb-2">
            {{ label }}
            {% if required %}<span class="text-red-500">*</span>{% endif %}
        </label>
        <input type="{{ type }}" 
               id="{{ name }}" 
               name="{{ name }}" 
               value="{{ value }}"
               class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
               {% if required %}required{% endif %}>
    </div>
{% endmacro %}

{# Select dropdown macro #}
{% macro select(name, label, options, selected='', required=false) %}
    <div class="form-group mb-4">
        <label for="{{ name }}" class="block text-gray-700 font-semibold mb-2">
            {{ label }}
            {% if required %}<span class="text-red-500">*</span>{% endif %}
        </label>
        <select id="{{ name }}" 
                name="{{ name }}" 
                class="w-full border rounded px-3 py-2"
                {% if required %}required{% endif %}>
            <option value="">Select {{ label }}</option>
            {% for value, text in options %}
                <option value="{{ value }}" {% if value == selected %}selected{% endif %}>
                    {{ text }}
                </option>
            {% endfor %}
        </select>
    </div>
{% endmacro %}

{# Button macro #}
{% macro button(text, type='submit', color='blue') %}
    <button type="{{ type }}" 
            class="bg-{{ color }}-500 text-white px-6 py-2 rounded hover:bg-{{ color }}-600 transition">
        {{ text }}
    </button>
{% endmacro %}

{# Card macro #}
{% macro card(title, content, footer='') %}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {% if title %}
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">{{ title }}</h3>
            </div>
        {% endif %}
        <div class="p-6">
            {{ content|raw }}
        </div>
        {% if footer %}
            <div class="bg-gray-50 px-6 py-4 border-t">
                {{ footer|raw }}
            </div>
        {% endif %}
    </div>
{% endmacro %}
```

#### Using Macros

```twig
{% import 'macros/forms.twig.html' as forms %}

<form method="POST" action="{{ url('products/store') }}">
    {{ csrf_field|raw }}
    
    {# Use input macro #}
    {{ forms.input('name', 'Product Name', old.name, 'text', true) }}
    {{ forms.input('price', 'Price', old.price, 'number', true) }}
    
    {# Use select macro #}
    {{ forms.select('category_id', 'Category', {
        '1': 'Electronics',
        '2': 'Clothing',
        '3': 'Books'
    }, old.category_id, true) }}
    
    {# Use button macro #}
    {{ forms.button('Create Product') }}
</form>
```

### 14.2 Custom Filters

While Twig has many built-in filters, you can create custom ones in PHP.

#### Creating Custom Filter

**In `app/Core/View.php`:**

```php
// Add custom filter
$this->twig->addFilter(new \Twig\TwigFilter('currency', function ($value) {
    return '$' . number_format($value, 2);
}));

$this->twig->addFilter(new \Twig\TwigFilter('excerpt', function ($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}));

$this->twig->addFilter(new \Twig\TwigFilter('time_ago', function ($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}));
```

#### Using Custom Filters

```twig
{# Currency filter #}
<p>Price: {{ product.price|currency }}</p>
{# Output: Price: $99.99 #}

{# Excerpt filter #}
<p>{{ product.description|excerpt(150) }}</p>
{# Output: First 150 characters... #}

{# Time ago filter #}
<p>Posted {{ post.created_at|time_ago }}</p>
{# Output: Posted 2 hours ago #}
```

### 14.3 Template Variables and Logic

#### Setting Variables

```twig
{# Simple variable #}
{% set total = price * quantity %}

{# Array #}
{% set colors = ['red', 'green', 'blue'] %}

{# Object #}
{% set user = {
    name: 'John',
    email: 'john@example.com',
    age: 30
} %}

{# Conditional assignment #}
{% set discount = price > 100 ? 10 : 5 %}

{# Block assignment #}
{% set content %}
    <div class="alert">
        This is some content
    </div>
{% endset %}
```

#### Math Operations

```twig
{# Addition #}
{{ price + tax }}

{# Subtraction #}
{{ price - discount }}

{# Multiplication #}
{{ price * quantity }}

{# Division #}
{{ total / count }}

{# Modulo #}
{{ number % 2 }}

{# Power #}
{{ base ** exponent }}
```

#### String Operations

```twig
{# Concatenation #}
{{ first_name ~ ' ' ~ last_name }}

{# String in variable #}
{% set full_name = first_name ~ ' ' ~ last_name %}

{# Multi-line concatenation #}
{% set message = 
    'Hello ' ~ name ~ '! ' ~
    'Welcome to ' ~ APP_NAME
%}
```

### 14.4 Conditional Classes

```twig
{# Dynamic classes based on conditions #}
<div class="product-card 
            {% if product.is_featured %}featured{% endif %}
            {% if product.stock == 0 %}out-of-stock{% endif %}
            {% if product.discount > 0 %}on-sale{% endif %}">
    ...
</div>

{# Ternary operator for classes #}
<button class="{{ is_active ? 'bg-blue-500' : 'bg-gray-500' }}">
    {{ is_active ? 'Active' : 'Inactive' }}
</button>

{# Multiple conditions #}
<div class="alert {{ 
    type == 'success' ? 'bg-green-100 text-green-700' :
    type == 'error' ? 'bg-red-100 text-red-700' :
    type == 'warning' ? 'bg-yellow-100 text-yellow-700' :
    'bg-blue-100 text-blue-700'
}}">
    {{ message }}
</div>
```

### 14.5 Loops with Conditions

```twig
{# Filter items in loop #}
{% for product in products if product.stock > 0 %}
    <div class="product">{{ product.name }}</div>
{% endfor %}

{# Loop with multiple conditions #}
{% for user in users if user.is_active and user.role == 'admin' %}
    <li>{{ user.name }} (Admin)</li>
{% endfor %}

{# Nested loops #}
{% for category in categories %}
    <h2>{{ category.name }}</h2>
    <ul>
        {% for product in category.products %}
            <li>{{ product.name }} - ${{ product.price }}</li>
        {% endfor %}
    </ul>
{% endfor %}
```

### 14.6 Spaceless Output

```twig
{# Remove whitespace between HTML tags #}
{% spaceless %}
    <div>
        <strong>Hello</strong>
        <em>World</em>
    </div>
{% endspaceless %}

{# Output: <div><strong>Hello</strong><em>World</em></div> #}
```

### 14.7 Verbatim (Raw Twig)

```twig
{# Display Twig syntax without processing #}
{% verbatim %}
    {{ variable }}
    {% for item in items %}
        {{ item }}
    {% endfor %}
{% endverbatim %}

{# Useful for documentation or when using Vue.js/Angular #}
```

### 14.8 Embed (Advanced Inheritance)

```twig
{# Embed allows you to include a template and override its blocks #}
{% embed 'components/modal.twig.html' %}
    {% block title %}Confirm Delete{% endblock %}
    {% block content %}
        <p>Are you sure you want to delete this item?</p>
    {% endblock %}
    {% block footer %}
        <button class="btn-danger">Delete</button>
        <button class="btn-secondary">Cancel</button>
    {% endblock %}
{% endembed %}
```

---

## 15. Best Practices {#best-practices}

### 15.1 File Organization

```
resources/views/
├── layouts/              # Master layouts
│   ├── app.twig.html
│   ├── admin.twig.html
│   └── auth.twig.html
├── components/           # Reusable components
│   ├── navbar.twig.html
│   ├── footer.twig.html
│   ├── sidebar.twig.html
│   └── product-card.twig.html
├── macros/              # Macro libraries
│   ├── forms.twig.html
│   └── ui.twig.html
├── pages/               # Static pages
│   ├── home.twig.html
│   ├── about.twig.html
│   └── contact.twig.html
└── [resources]/         # Resource-specific views
    ├── products/
    │   ├── index.twig.html
    │   ├── create.twig.html
    │   ├── edit.twig.html
    │   └── show.twig.html
    └── users/
        ├── index.twig.html
        └── profile.twig.html
```

### 15.2 Naming Conventions

```twig
{# ✅ Good naming #}
{% extends 'layouts/app.twig.html' %}
{% include 'components/navbar.twig.html' %}
{% import 'macros/forms.twig.html' as forms %}

{# ❌ Bad naming #}
{% extends 'layout.twig' %}
{% include 'nav.twig' %}
{% import 'f.twig' as f %}
```

### 15.3 Security Best Practices

#### Always Escape Output

```twig
{# ✅ Good - auto-escaped #}
{{ user.name }}
{{ product.description }}

{# ⚠️ Use |raw only for trusted content #}
{{ flash_display()|raw }}
{{ trusted_html_content|raw }}

{# ❌ Never use |raw for user input #}
{{ user_comment|raw }}  {# XSS vulnerability! #}
```

#### CSRF Protection

```twig
{# ✅ Always include CSRF token in forms #}
<form method="POST">
    {{ csrf_field|raw }}
    ...
</form>

{# ❌ Never omit CSRF protection #}
<form method="POST">
    {# Missing CSRF token! #}
</form>
```

#### Validate File Uploads

```twig
{# ✅ Specify accepted file types #}
<input type="file" name="avatar" accept="image/png, image/jpeg">

{# ✅ Show file size limit #}
<input type="file" name="document" accept=".pdf">
<small>Maximum file size: 5MB</small>
```

### 15.4 Performance Best Practices

#### Minimize Database Queries

```twig
{# ❌ Bad - N+1 query problem #}
{% for post in posts %}
    {{ post.title }} by {{ post.user().name }}
{% endfor %}

{# ✅ Good - eager load relationships in controller #}
{# Controller: $posts = Post::with('user')->all(); #}
{% for post in posts %}
    {{ post.title }} by {{ post.user.name }}
{% endfor %}
```

#### Use Pagination

```twig
{# ❌ Bad - load all records #}
{% for product in Product.all() %}

{# ✅ Good - use pagination #}
{% for product in products.items() %}
```

#### Cache Static Content

```twig
{# Enable Twig cache in production #}
{# In View.php: 'cache' => '/path/to/cache' #}
```

### 15.5 Accessibility Best Practices

```twig
{# ✅ Always use alt text for images #}
<img src="{{ storage(product.image) }}" alt="{{ product.name }}">

{# ✅ Use semantic HTML #}
<nav>...</nav>
<main>...</main>
<footer>...</footer>

{# ✅ Use proper form labels #}
<label for="email">Email</label>
<input type="email" id="email" name="email">

{# ✅ Use ARIA attributes when needed #}
<button aria-label="Close menu">×</button>

{# ✅ Ensure sufficient color contrast #}
<div class="bg-blue-600 text-white">  {# Good contrast #}
```

### 15.6 SEO Best Practices

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    {# ✅ Unique, descriptive title #}
    <title>{% block title %}{{ APP_NAME }}{% endblock %}</title>
    
    {# ✅ Meta description #}
    <meta name="description" content="{% block meta_description %}Default description{% endblock %}">
    
    {# ✅ Canonical URL #}
    <link rel="canonical" href="{{ APP_URL }}{{ _SERVER.REQUEST_URI }}">
    
    {# ✅ Open Graph tags #}
    <meta property="og:title" content="{% block og_title %}{{ APP_NAME }}{% endblock %}">
    <meta property="og:description" content="{% block og_description %}...{% endblock %}">
    <meta property="og:image" content="{% block og_image %}{{ asset('images/og-image.jpg') }}{% endblock %}">
    
    {# ✅ Structured data #}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ APP_NAME }}",
        "url": "{{ APP_URL }}"
    }
    </script>
</head>
```

---

## 16. Troubleshooting {#troubleshooting}

### 16.1 Common Errors

#### Error: "Unable to find template"

**Problem:**
```
Twig\Error\LoaderError: Unable to find template "products/index.twig.html"
```

**Solutions:**
1. Check file exists at correct path
2. Verify file extension (`.twig.html` or `.twig`)
3. Check file permissions
4. Clear Twig cache

```php
// Check view paths in View.php
$templatePaths = [
    'resources/views',
    'resources/views/admin'
];
```

#### Error: "Unknown function"

**Problem:**
```
Twig\Error\SyntaxError: Unknown "storage_url" function
```

**Solution:**
Ensure function is registered in `View.php`:

```php
$this->twig->addFunction(new TwigFunction('storage_url', function ($path) {
    // ...
}));
```

#### Error: "Variable does not exist"

**Problem:**
```
Twig\Error\RuntimeError: Variable "product" does not exist
```

**Solutions:**
1. Pass variable from controller:
```php
return $this->view('products/show', ['product' => $product]);
```

2. Use default value:
```twig
{{ product.name|default('No name') }}
```

3. Check if defined:
```twig
{% if product is defined %}
    {{ product.name }}
{% endif %}
```

### 16.2 Debugging

#### Enable Debug Mode

```php
// In View.php
$this->twig = new Environment($loader, [
    'debug' => true,  // Enable debug
]);
```

#### Dump Variables

```twig
{# Dump variable #}
{{ dump(product) }}

{# Dump all variables #}
{{ dump() }}
```

#### Display Errors

```twig
{# Show validation errors #}
{% if errors %}
    <div class="alert alert-danger">
        <ul>
            {% for field, messages in errors %}
                {% for message in messages %}
                    <li>{{ field }}: {{ message }}</li>
                {% endfor %}
            {% endfor %}
        </ul>
    </div>
{% endif %}
```

---

## 17. Complete Real-World Examples {#complete-examples}

### 17.1 E-Commerce Product Page

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}{{ product.name }} - {{ APP_NAME }}{% endblock %}

{% block meta_description %}{{ product.description|slice(0, 160) }}{% endblock %}

{% block content %}
<div class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        {# Product Images #}
        <div>
            <div class="mb-4">
                <img id="mainImage" 
                     src="{{ storage(product.image) }}" 
                     alt="{{ product.name }}" 
                     class="w-full rounded-lg shadow-lg">
            </div>
            
            {# Thumbnail gallery #}
            {% if product.gallery %}
                <div class="grid grid-cols-4 gap-2">
                    {% for image in product.gallery %}
                        <img src="{{ storage(image) }}" 
                             alt="{{ product.name }}"
                             class="cursor-pointer rounded hover:opacity-75 transition"
                             onclick="document.getElementById('mainImage').src = this.src">
                    {% endfor %}
                </div>
            {% endif %}
        </div>
        
        {# Product Info #}
        <div>
            <h1 class="text-4xl font-bold mb-4">{{ product.name }}</h1>
            
            {# Rating #}
            <div class="flex items-center mb-4">
                <div class="flex">
                    {% for i in 1..5 %}
                        <svg class="w-5 h-5 {% if i <= product.rating %}text-yellow-400{% else %}text-gray-300{% endif %}" 
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    {% endfor %}
                </div>
                <span class="ml-2 text-gray-600">({{ product.reviews_count }} reviews)</span>
            </div>
            
            {# Price #}
            <div class="mb-6">
                {% if product.discount > 0 %}
                    <div class="flex items-center space-x-3">
                        <span class="text-4xl font-bold text-red-600">${{ product.price }}</span>
                        <span class="text-2xl text-gray-400 line-through">${{ product.original_price }}</span>
                        <span class="bg-red-500 text-white px-3 py-1 rounded text-sm font-semibold">
                            Save {{ product.discount }}%
                        </span>
                    </div>
                {% else %}
                    <span class="text-4xl font-bold text-gray-900">${{ product.price }}</span>
                {% endif %}
            </div>
            
            {# Description #}
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Description</h2>
                <p class="text-gray-700">{{ product.description }}</p>
            </div>
            
            {# Features #}
            {% if product.features %}
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Features</h2>
                    <ul class="list-disc list-inside space-y-1 text-gray-700">
                        {% for feature in product.features %}
                            <li>{{ feature }}</li>
                        {% endfor %}
                    </ul>
                </div>
            {% endif %}
            
            {# Add to Cart Form #}
            <form method="POST" action="{{ url('cart/add') }}" class="mb-6">
                {{ csrf_field|raw }}
                <input type="hidden" name="product_id" value="{{ product.id }}">
                
                <div class="flex items-center space-x-4 mb-4">
                    <label class="font-semibold">Quantity:</label>
                    <input type="number" 
                           name="quantity" 
                           value="1" 
                           min="1" 
                           max="{{ product.stock }}"
                           class="border rounded px-3 py-2 w-20">
                    <span class="text-gray-600">{{ product.stock }} available</span>
                </div>
                
                {% if product.stock > 0 %}
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Add to Cart
                    </button>
                {% else %}
                    <button disabled 
                            class="w-full bg-gray-400 text-white py-3 rounded-lg font-semibold cursor-not-allowed">
                        Out of Stock
                    </button>
                {% endif %}
            </form>
            
            {# Additional Info #}
            <div class="border-t pt-4 space-y-2 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                    </svg>
                    Free shipping on orders over $50
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Estimated delivery: 3-5 business days
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                    </svg>
                    30-day return policy
                </div>
            </div>
        </div>
    </div>
    
    {# Reviews Section #}
    <div class="border-t pt-8">
        <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
        
        {% if product.reviews %}
            <div class="space-y-6">
                {% for review in product.reviews %}
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <img src="{{ storage_url(review.user.avatar|default('avatars/default.png')) }}" 
                                     alt="{{ review.user.name }}" 
                                     class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <div class="font-semibold">{{ review.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ review.created_at|time_ago }}</div>
                                </div>
                            </div>
                            <div class="flex">
                                {% for i in 1..5 %}
                                    <svg class="w-4 h-4 {% if i <= review.rating %}text-yellow-400{% else %}text-gray-300{% endif %}" 
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                {% endfor %}
                            </div>
                        </div>
                        <p class="text-gray-700">{{ review.comment }}</p>
                    </div>
                {% endfor %}
            </div>
        {% else %}
            <p class="text-gray-500">No reviews yet. Be the first to review this product!</p>
        {% endif %}
    </div>
</div>
{% endblock %}
```

### 17.2 Dashboard with Charts

```twig
{% extends 'layouts/admin.twig.html' %}

{% block title %}Dashboard - {{ APP_NAME }}{% endblock %}

{% block content %}
<div class="mb-8">
    <h1 class="text-3xl font-bold">Dashboard</h1>
    <p class="text-gray-600">Welcome back, {{ auth.user.name }}!</p>
</div>

{# Stats Cards #}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Sales</p>
                <p class="text-3xl font-bold">${{ stats.total_sales|number_format(2) }}</p>
                <p class="text-green-600 text-sm mt-1">
                    +{{ stats.sales_growth }}% from last month
                </p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Orders</p>
                <p class="text-3xl font-bold">{{ stats.total_orders }}</p>
                <p class="text-green-600 text-sm mt-1">
                    +{{ stats.orders_growth }}% from last month
                </p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Customers</p>
                <p class="text-3xl font-bold">{{ stats.total_customers }}</p>
                <p class="text-green-600 text-sm mt-1">
                    +{{ stats.customers_growth }}% from last month
                </p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Products</p>
                <p class="text-3xl font-bold">{{ stats.total_products }}</p>
                <p class="text-yellow-600 text-sm mt-1">
                    {{ stats.low_stock_count }} low stock
                </p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{# Recent Orders #}
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold">Recent Orders</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                {% for order in recent_orders %}
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ url('admin/orders/' ~ order.id) }}" class="text-blue-600 hover:underline">
                                #{{ order.id }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ order.customer.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-semibold">${{ order.total }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full
                                {% if order.status == 'completed' %}bg-green-100 text-green-800{% endif %}
                                {% if order.status == 'pending' %}bg-yellow-100 text-yellow-800{% endif %}
                                {% if order.status == 'cancelled' %}bg-red-100 text-red-800{% endif %}">
                                {{ order.status|capitalize }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ order.created_at|date('M d, Y') }}
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
</div>
{% endblock %}
```

---

## 18. Summary & Quick Reference

### 18.1 Essential Twig Syntax

```twig
{# Output #}
{{ variable }}

{# Logic #}
{% if condition %}...{% endif %}
{% for item in items %}...{% endfor %}

{# Comments #}
{# This is a comment #}

{# Filters #}
{{ text|upper }}
{{ price|number_format(2) }}

{# Functions #}
{{ storage('path/to/file.jpg') }}
{{ url('products') }}
```

### 18.2 OxygenFramework Helpers

```twig
{# Storage #}
{{ storage('images/logo.png') }}
{{ storage_url('videos/intro.mp4') }}

{# Assets #}
{{ asset('css/app.css') }}
{{ url('about') }}

{# CSRF #}
{{ csrf_field|raw }}
{{ csrf_token }}

{# Flash #}
{{ flash_display()|raw }}

{# Auth #}
{% if auth.check %}
    {{ auth.user.name }}
{% endif %}
```

### 18.3 Common Patterns

```twig
{# Form with validation #}
<form method="POST" action="{{ url('products/store') }}">
    {{ csrf_field|raw }}
    <input type="text" name="name" value="{{ old.name }}">
    {% if errors.name %}
        <p class="error">{{ errors.name[0] }}</p>
    {% endif %}
    <button type="submit">Submit</button>
</form>

{# Loop with empty state #}
{% for product in products %}
    {{ product.name }}
{% else %}
    <p>No products found</p>
{% endfor %}

{# Conditional classes #}
<div class="{{ active ? 'bg-blue-500' : 'bg-gray-500' }}">
```

---

**This concludes the comprehensive OxygenFramework Views & Templating Guide!**

**Total Documentation:**
- Part 1: Twig Basics, View System, Global Variables, Helpers, CSRF, Assets, Storage
- Part 2: Layouts, Inheritance, Components, Partials
- Part 3: Forms, Authentication, Flash Messages
- Part 4: Advanced Techniques, Best Practices, Complete Examples, Troubleshooting

**Estimated Total Lines: 18,000+**

Made with ❤️ for the Oxygen Community
