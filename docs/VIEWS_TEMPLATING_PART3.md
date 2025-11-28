# Views & Templating - Complete Guide (Part 3)
## Forms, Authentication, Flash Messages & Advanced Techniques

---

## 11. Forms {#forms}

### 11.1 Basic Form Structure

```twig
<form method="POST" action="{{ url('products/store') }}" enctype="multipart/form-data">
    {# CSRF Protection #}
    {{ csrf_field|raw }}
    
    {# Form fields go here #}
    
    <button type="submit">Submit</button>
</form>
```

### 11.2 Text Inputs

```twig
{# Text input #}
<div class="form-group mb-4">
    <label for="name" class="block text-gray-700 font-semibold mb-2">
        Product Name <span class="text-red-500">*</span>
    </label>
    <input type="text" 
           id="name" 
           name="name" 
           value="{{ old.name|default(product.name|default('')) }}" 
           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
           required>
    
    {# Validation error #}
    {% if errors.name %}
        <p class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
    {% endif %}
</div>

{# Email input #}
<div class="form-group mb-4">
    <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
    <input type="email" 
           id="email" 
           name="email" 
           value="{{ old.email }}"
           placeholder="user@example.com"
           class="w-full border rounded px-3 py-2"
           required>
</div>

{# Password input #}
<div class="form-group mb-4">
    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
    <input type="password" 
           id="password" 
           name="password"
           class="w-full border rounded px-3 py-2"
           minlength="8"
           required>
    <small class="text-gray-500">Minimum 8 characters</small>
</div>

{# Number input #}
<div class="form-group mb-4">
    <label for="price" class="block text-gray-700 font-semibold mb-2">Price</label>
    <input type="number" 
           id="price" 
           name="price" 
           value="{{ old.price|default(product.price) }}"
           step="0.01"
           min="0"
           class="w-full border rounded px-3 py-2"
           required>
</div>

{# URL input #}
<div class="form-group mb-4">
    <label for="website" class="block text-gray-700 font-semibold mb-2">Website</label>
    <input type="url" 
           id="website" 
           name="website" 
           value="{{ old.website }}"
           placeholder="https://example.com"
           class="w-full border rounded px-3 py-2">
</div>

{# Date input #}
<div class="form-group mb-4">
    <label for="published_date" class="block text-gray-700 font-semibold mb-2">Published Date</label>
    <input type="date" 
           id="published_date" 
           name="published_date" 
           value="{{ old.published_date|default(product.published_date) }}"
           class="w-full border rounded px-3 py-2">
</div>
```

### 11.3 Textarea

```twig
<div class="form-group mb-4">
    <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
    <textarea id="description" 
              name="description" 
              rows="5"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              required>{{ old.description|default(product.description|default('')) }}</textarea>
    
    {# Character counter #}
    <div class="flex justify-between mt-1">
        <small class="text-gray-500">Describe your product in detail</small>
        <small class="text-gray-500" id="charCount">0 / 500</small>
    </div>
</div>

<script>
const textarea = document.getElementById('description');
const charCount = document.getElementById('charCount');

textarea.addEventListener('input', function() {
    const length = this.value.length;
    charCount.textContent = `${length} / 500`;
    
    if (length > 500) {
        charCount.classList.add('text-red-500');
    } else {
        charCount.classList.remove('text-red-500');
    }
});
</script>
```

### 11.4 Select Dropdowns

#### Simple Select

```twig
<div class="form-group mb-4">
    <label for="category" class="block text-gray-700 font-semibold mb-2">Category</label>
    <select id="category" 
            name="category_id" 
            class="w-full border rounded px-3 py-2"
            required>
        <option value="">Select a category</option>
        {% for category in categories %}
            <option value="{{ category.id }}" 
                    {% if old.category_id == category.id or product.category_id == category.id %}selected{% endif %}>
                {{ category.name }}
            </option>
        {% endfor %}
    </select>
</div>
```

#### Grouped Select

```twig
<div class="form-group mb-4">
    <label for="product" class="block text-gray-700 font-semibold mb-2">Product</label>
    <select id="product" name="product_id" class="w-full border rounded px-3 py-2">
        <option value="">Select a product</option>
        {% for category in categories %}
            <optgroup label="{{ category.name }}">
                {% for product in category.products %}
                    <option value="{{ product.id }}">{{ product.name }}</option>
                {% endfor %}
            </optgroup>
        {% endfor %}
    </select>
</div>
```

#### Multiple Select

```twig
<div class="form-group mb-4">
    <label for="tags" class="block text-gray-700 font-semibold mb-2">Tags</label>
    <select id="tags" 
            name="tags[]" 
            multiple 
            size="5"
            class="w-full border rounded px-3 py-2">
        {% for tag in tags %}
            <option value="{{ tag.id }}" 
                    {% if tag.id in product.tag_ids %}selected{% endif %}>
                {{ tag.name }}
            </option>
        {% endfor %}
    </select>
    <small class="text-gray-500">Hold Ctrl/Cmd to select multiple</small>
</div>
```

### 11.5 Checkboxes

#### Single Checkbox

```twig
<div class="form-group mb-4">
    <label class="flex items-center">
        <input type="checkbox" 
               name="is_featured" 
               value="1"
               {% if old.is_featured or product.is_featured %}checked{% endif %}
               class="mr-2">
        <span class="text-gray-700">Featured Product</span>
    </label>
</div>

<div class="form-group mb-4">
    <label class="flex items-center">
        <input type="checkbox" 
               name="terms" 
               value="1"
               required
               class="mr-2">
        <span class="text-gray-700">
            I agree to the <a href="{{ url('terms') }}" class="text-blue-600 hover:underline">Terms and Conditions</a>
        </span>
    </label>
</div>
```

#### Multiple Checkboxes

```twig
<div class="form-group mb-4">
    <label class="block text-gray-700 font-semibold mb-2">Features</label>
    
    <div class="space-y-2">
        {% for feature in features %}
            <label class="flex items-center">
                <input type="checkbox" 
                       name="features[]" 
                       value="{{ feature.id }}"
                       {% if feature.id in product.feature_ids %}checked{% endif %}
                       class="mr-2">
                <span class="text-gray-700">{{ feature.name }}</span>
            </label>
        {% endfor %}
    </div>
</div>
```

### 11.6 Radio Buttons

```twig
<div class="form-group mb-4">
    <label class="block text-gray-700 font-semibold mb-2">Status</label>
    
    <div class="space-y-2">
        <label class="flex items-center">
            <input type="radio" 
                   name="status" 
                   value="draft"
                   {% if old.status == 'draft' or product.status == 'draft' %}checked{% endif %}
                   class="mr-2">
            <span class="text-gray-700">Draft</span>
        </label>
        
        <label class="flex items-center">
            <input type="radio" 
                   name="status" 
                   value="published"
                   {% if old.status == 'published' or product.status == 'published' %}checked{% endif %}
                   class="mr-2">
            <span class="text-gray-700">Published</span>
        </label>
        
        <label class="flex items-center">
            <input type="radio" 
                   name="status" 
                   value="archived"
                   {% if old.status == 'archived' or product.status == 'archived' %}checked{% endif %}
                   class="mr-2">
            <span class="text-gray-700">Archived</span>
        </label>
    </div>
</div>
```

### 11.7 File Uploads

```twig
{# Single file upload #}
<div class="form-group mb-4">
    <label for="image" class="block text-gray-700 font-semibold mb-2">Product Image</label>
    <input type="file" 
           id="image" 
           name="image" 
           accept="image/*"
           class="w-full border rounded px-3 py-2">
    
    {# Show current image if editing #}
    {% if product.image %}
        <div class="mt-2">
            <p class="text-sm text-gray-600 mb-1">Current image:</p>
            <img src="{{ storage(product.image) }}" alt="Current" class="max-w-xs rounded">
        </div>
    {% endif %}
</div>

{# Multiple file upload #}
<div class="form-group mb-4">
    <label for="gallery" class="block text-gray-700 font-semibold mb-2">Gallery Images</label>
    <input type="file" 
           id="gallery" 
           name="gallery[]" 
           multiple
           accept="image/*"
           class="w-full border rounded px-3 py-2">
    <small class="text-gray-500">You can select multiple images</small>
</div>
```

### 11.8 Complete Form Example

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}Create Product - {{ APP_NAME }}{% endblock %}

{% block content %}
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Create New Product</h1>
    
    {# Flash messages #}
    {{ flash_display()|raw }}
    
    {# Form #}
    <form method="POST" action="{{ url('products/store') }}" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 py-6">
        {# CSRF Protection #}
        {{ csrf_field|raw }}
        
        {# Product Name #}
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold mb-2">
                Product Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ old.name }}"
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
            {% if errors.name %}
                <p class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
            {% endif %}
        </div>
        
        {# Description #}
        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
            <textarea id="description" 
                      name="description" 
                      rows="4"
                      class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old.description }}</textarea>
        </div>
        
        {# Price and Stock (Grid) #}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price" class="block text-gray-700 font-semibold mb-2">
                    Price ($) <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       id="price" 
                       name="price" 
                       value="{{ old.price }}"
                       step="0.01"
                       min="0"
                       class="w-full border rounded px-3 py-2"
                       required>
            </div>
            
            <div>
                <label for="stock" class="block text-gray-700 font-semibold mb-2">
                    Stock <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       id="stock" 
                       name="stock" 
                       value="{{ old.stock|default(0) }}"
                       min="0"
                       class="w-full border rounded px-3 py-2"
                       required>
            </div>
        </div>
        
        {# Category #}
        <div class="mb-4">
            <label for="category_id" class="block text-gray-700 font-semibold mb-2">
                Category <span class="text-red-500">*</span>
            </label>
            <select id="category_id" 
                    name="category_id" 
                    class="w-full border rounded px-3 py-2"
                    required>
                <option value="">Select a category</option>
                {% for category in categories %}
                    <option value="{{ category.id }}" 
                            {% if old.category_id == category.id %}selected{% endif %}>
                        {{ category.name }}
                    </option>
                {% endfor %}
            </select>
        </div>
        
        {# Image Upload #}
        <div class="mb-4">
            <label for="image" class="block text-gray-700 font-semibold mb-2">Product Image</label>
            <input type="file" 
                   id="image" 
                   name="image" 
                   accept="image/*"
                   class="w-full border rounded px-3 py-2">
        </div>
        
        {# Status #}
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Status</label>
            <div class="flex space-x-4">
                <label class="flex items-center">
                    <input type="radio" name="status" value="draft" checked class="mr-2">
                    <span>Draft</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="status" value="published" class="mr-2">
                    <span>Published</span>
                </label>
            </div>
        </div>
        
        {# Featured Checkbox #}
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_featured" value="1" class="mr-2">
                <span class="text-gray-700">Mark as featured product</span>
            </label>
        </div>
        
        {# Submit Buttons #}
        <div class="flex justify-between items-center">
            <a href="{{ url('products') }}" class="text-gray-600 hover:text-gray-800">
                Cancel
            </a>
            <div class="space-x-2">
                <button type="submit" 
                        name="action" 
                        value="save"
                        class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                    Create Product
                </button>
                <button type="submit" 
                        name="action" 
                        value="save_and_new"
                        class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition">
                    Save & Create Another
                </button>
            </div>
        </div>
    </form>
</div>
{% endblock %}
```

---

## 12. Authentication {#authentication}

### 12.1 Login Form

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}Login - {{ APP_NAME }}{% endblock %}

{% block content %}
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Login to {{ APP_NAME }}</h2>
        
        {{ flash_display()|raw }}
        
        <form method="POST" action="{{ url('login') }}">
            {{ csrf_field|raw }}
            
            {# Email #}
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old.email }}"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required
                       autofocus>
                {% if errors.email %}
                    <p class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</p>
                {% endif %}
            </div>
            
            {# Password #}
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" 
                       id="password" 
                       name="password"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                {% if errors.password %}
                    <p class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</p>
                {% endif %}
            </div>
            
            {# Remember Me #}
            <div class="mb-4 flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" value="1" class="mr-2">
                    <span class="text-gray-700">Remember me</span>
                </label>
                <a href="{{ url('forgot-password') }}" class="text-blue-600 hover:underline text-sm">
                    Forgot password?
                </a>
            </div>
            
            {# Submit #}
            <button type="submit" 
                    class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition">
                Login
            </button>
        </form>
        
        {# Register Link #}
        <p class="text-center mt-4 text-gray-600">
            Don't have an account? 
            <a href="{{ url('register') }}" class="text-blue-600 hover:underline">Register</a>
        </p>
    </div>
</div>
{% endblock %}
```

### 12.2 Register Form

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}Register - {{ APP_NAME }}{% endblock %}

{% block content %}
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>
        
        {{ flash_display()|raw }}
        
        <form method="POST" action="{{ url('register') }}">
            {{ csrf_field|raw }}
            
            {# Name #}
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old.name }}"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                {% if errors.name %}
                    <p class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
                {% endif %}
            </div>
            
            {# Email #}
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old.email }}"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
                {% if errors.email %}
                    <p class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</p>
                {% endif %}
            </div>
            
            {# Password #}
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" 
                       id="password" 
                       name="password"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       minlength="8"
                       required>
                <small class="text-gray-500">Minimum 8 characters</small>
                {% if errors.password %}
                    <p class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</p>
                {% endif %}
            </div>
            
            {# Confirm Password #}
            <div class="mb-4">
                <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       minlength="8"
                       required>
            </div>
            
            {# Terms #}
            <div class="mb-4">
                <label class="flex items-start">
                    <input type="checkbox" name="terms" value="1" required class="mr-2 mt-1">
                    <span class="text-gray-700 text-sm">
                        I agree to the <a href="{{ url('terms') }}" class="text-blue-600 hover:underline">Terms of Service</a> 
                        and <a href="{{ url('privacy') }}" class="text-blue-600 hover:underline">Privacy Policy</a>
                    </span>
                </label>
            </div>
            
            {# Submit #}
            <button type="submit" 
                    class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition">
                Create Account
            </button>
        </form>
        
        {# Login Link #}
        <p class="text-center mt-4 text-gray-600">
            Already have an account? 
            <a href="{{ url('login') }}" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</div>
{% endblock %}
```

### 12.3 User Profile Display

```twig
{% extends 'layouts/app.twig.html' %}

{% block title %}Profile - {{ APP_NAME }}{% endblock %}

{% block content %}
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {# Cover Image #}
        <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600"></div>
        
        {# Profile Info #}
        <div class="px-6 pb-6">
            {# Avatar #}
            <div class="flex items-end -mt-16 mb-4">
                <img src="{{ storage_url(auth.user.avatar|default('avatars/default.png')) }}" 
                     alt="{{ auth.user.name }}" 
                     class="w-32 h-32 rounded-full border-4 border-white shadow-lg">
                
                <div class="ml-4 mb-2">
                    <h1 class="text-3xl font-bold">{{ auth.user.name }}</h1>
                    <p class="text-gray-600">{{ auth.user.email }}</p>
                </div>
                
                <div class="ml-auto mb-2">
                    <a href="{{ url('profile/edit') }}" 
                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Edit Profile
                    </a>
                </div>
            </div>
            
            {# Stats #}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-50 rounded">
                    <div class="text-3xl font-bold text-blue-600">{{ auth.user.posts_count }}</div>
                    <div class="text-gray-600">Posts</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded">
                    <div class="text-3xl font-bold text-green-600">{{ auth.user.followers_count }}</div>
                    <div class="text-gray-600">Followers</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded">
                    <div class="text-3xl font-bold text-purple-600">{{ auth.user.following_count }}</div>
                    <div class="text-gray-600">Following</div>
                </div>
            </div>
            
            {# Bio #}
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">About</h2>
                <p class="text-gray-700">{{ auth.user.bio|default('No bio yet') }}</p>
            </div>
            
            {# Additional Info #}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Location</h3>
                    <p class="text-gray-600">{{ auth.user.location|default('Not specified') }}</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Website</h3>
                    {% if auth.user.website %}
                        <a href="{{ auth.user.website }}" class="text-blue-600 hover:underline" target="_blank">
                            {{ auth.user.website }}
                        </a>
                    {% else %}
                        <p class="text-gray-600">Not specified</p>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

---

## 13. Flash Messages {#flash-messages}

### 13.1 Displaying Flash Messages

```twig
{# Automatic display (recommended) #}
{{ flash_display()|raw }}
```

### 13.2 Custom Flash Message Display

```twig
{# Custom styled flash messages #}
{% if _SESSION.flash %}
    <div class="fixed top-4 right-4 z-50 space-y-2">
        {% for type, messages in _SESSION.flash %}
            {% for message in messages %}
                <div class="flash-message flash-{{ type }} 
                            bg-white border-l-4 rounded shadow-lg p-4 max-w-md
                            {% if type == 'success' %}border-green-500{% endif %}
                            {% if type == 'error' %}border-red-500{% endif %}
                            {% if type == 'warning' %}border-yellow-500{% endif %}
                            {% if type == 'info' %}border-blue-500{% endif %}">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            {% if type == 'success' %}
                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            {% elseif type == 'error' %}
                                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            {% elseif type == 'warning' %}
                                <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            {% else %}
                                <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            {% endif %}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ message }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" 
                                class="ml-auto flex-shrink-0 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            {% endfor %}
        {% endfor %}
    </div>
    
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.flash-message').forEach(msg => {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    </script>
{% endif %}
```

---

*This documentation continues with Part 4 covering Advanced Techniques, Best Practices, Complete Examples, and Troubleshooting...*
