# Quick Start Guide

Get up and running with CI4 Settings in 5 minutes!

## 1. Installation

```bash
composer require olajideolamide/ci4-settings
```

## 2. Database Setup

Run the migration:

```bash
php spark migrate -n Olajideolamide\\CI4Settings
```

## 3. Load the Helper

In your controller or `BaseController.php`:

```php
protected $helpers = ['settings'];
```

## 4. Start Using!

### Basic Settings

```php
// Set a setting
settings()->set('site.name', 'My Awesome Website');
settings()->set('max_users', 100);
settings()->set('maintenance', false);

// Get a setting
$siteName = settings('site.name', 'Default Site');
$maxUsers = settings('max_users', 50);

// Check if exists
if (settings()->has('site.name')) {
    echo "Site name is configured!";
}

// Delete a setting
settings()->forget('old_setting');
```

### Feature Flags

```php
// Enable a feature
settings()->enableFeature('new_dashboard');
settings()->enableFeature('dark_mode');

// Check if enabled
if (feature('new_dashboard')) {
    // Show new dashboard
    return view('dashboard/new');
} else {
    // Show old dashboard
    return view('dashboard/old');
}

// Disable a feature
settings()->disableFeature('beta_feature');
```

### Real-World Example

```php
<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected $helpers = ['settings'];
    
    public function index()
    {
        // Get site settings
        $data = [
            'siteName'  => settings('site.name', 'My Site'),
            'showBanner' => feature('homepage_banner'),
            'newLayout'  => feature('new_layout'),
        ];
        
        // Conditional features
        if (feature('new_checkout')) {
            $data['checkoutUrl'] = '/checkout/v2';
        } else {
            $data['checkoutUrl'] = '/checkout';
        }
        
        return view('home', $data);
    }
    
    public function updateSettings()
    {
        settings()->set('site.name', $this->request->getPost('name'));
        settings()->set('site.email', $this->request->getPost('email'));
        
        return redirect()->back()->with('success', 'Settings updated!');
    }
}
```

## 5. Advanced Usage

### Context-Based Settings

```php
// User-specific settings
settings()->set('theme', 'dark', 'user_123');
settings()->set('language', 'en', 'user_123');

// Get user settings
$theme = settings()->get('theme', 'light', 'user_123');
```

### Different Data Types

```php
// String
settings()->set('site.title', 'My Website');

// Integer
settings()->set('posts_per_page', 10);

// Boolean
settings()->set('registration_open', true);

// Array/JSON
settings()->set('social_links', [
    'twitter' => 'https://twitter.com/mysite',
    'facebook' => 'https://facebook.com/mysite',
]);

// Retrieve
$socialLinks = settings('social_links');
echo $socialLinks['twitter'];
```

## Common Patterns

### Feature Toggle in Views

```php
<!-- app/Views/layout.php -->
<header>
    <h1><?= settings('site.name', 'My Site') ?></h1>
    
    <?php if (feature('search_bar')): ?>
        <div class="search">
            <input type="search" placeholder="Search...">
        </div>
    <?php endif; ?>
    
    <?php if (feature('dark_mode')): ?>
        <button id="theme-toggle">Toggle Dark Mode</button>
    <?php endif; ?>
</header>
```

### A/B Testing

```php
public function productPage($id)
{
    $product = $this->productModel->find($id);
    
    if (feature('new_product_layout')) {
        return view('products/show_v2', ['product' => $product]);
    }
    
    return view('products/show', ['product' => $product]);
}
```

### Gradual Rollout

```php
public function dashboard()
{
    // Enable new dashboard for beta users only
    $isBetaUser = $this->userModel->isBeta(auth()->id());
    
    if ($isBetaUser && feature('dashboard_v2_beta')) {
        return view('dashboard/v2');
    }
    
    if (feature('dashboard_v2')) {
        return view('dashboard/v2');
    }
    
    return view('dashboard/v1');
}
```

## That's It!

You're now ready to use CI4 Settings in your CodeIgniter 4 application.

For more detailed documentation, see [README.md](README.md).
For examples, check the [examples/](examples/) directory.
