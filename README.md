# CI4 Settings

Application settings and feature flags library for CodeIgniter 4

## Features

- 📦 Store key/value settings in database
- 🎯 Simple helper functions for easy access
- 🚩 Feature flags support
- 🔄 Multiple data types (string, integer, boolean, json)
- 📂 Context-based settings
- ⚡ Built-in caching

## Installation

Install via Composer:

```bash
composer require olajideolamide/ci4-settings
```

## Database Setup

Run the migration to create the `settings` table:

```bash
php spark migrate -n Olajideolamide\\CI4Settings
```

Or manually create the table with this structure:

```sql
CREATE TABLE `settings` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT,
  `type` VARCHAR(50) DEFAULT 'string',
  `context` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `key_context` (`key`, `context`),
  KEY `context` (`context`)
);
```

## Usage

### Basic Settings

Load the helper in your controller or anywhere in your application:

```php
helper('settings');
```

Or add it to your `BaseController.php`:

```php
protected $helpers = ['settings'];
```

#### Get a setting

```php
// Get a setting with default value
$siteName = settings('site.name', 'My Website');

// Get without default
$siteEmail = settings('site.email');
```

#### Set a setting

```php
// Using the helper to get service instance
settings()->set('site.name', 'My Awesome Site');
settings()->set('max_users', 100);
settings()->set('maintenance_mode', true);
settings()->set('config_data', ['key' => 'value']);
```

#### Check if a setting exists

```php
if (settings()->has('site.name')) {
    // Setting exists
}
```

#### Delete a setting

```php
settings()->forget('site.name');
```

### Feature Flags

Feature flags allow you to enable/disable features in your application:

```php
// Check if a feature is enabled
if (feature('new_checkout')) {
    // Show new checkout process
} else {
    // Show old checkout process
}

// Enable a feature
settings()->enableFeature('new_checkout');

// Disable a feature
settings()->disableFeature('new_checkout');

// Check with default value
if (feature('beta_feature', false)) {
    // Feature is enabled
}
```

### Context-Based Settings

You can organize settings by context:

```php
// Set setting with context
settings()->set('theme', 'dark', 'user');
settings()->set('language', 'en', 'user');

// Get setting with context
$theme = settings()->get('theme', 'light', 'user');
```

### Data Types

The library automatically detects and stores the correct data type:

```php
// String
settings()->set('site.name', 'My Site');

// Integer
settings()->set('max_items', 50);

// Boolean
settings()->set('maintenance', true);

// JSON (arrays and objects)
settings()->set('options', ['color' => 'blue', 'size' => 'large']);
```

### Direct Service Usage

You can also use the service directly:

```php
use Olajideolamide\CI4Settings\Services\SettingsService;

$settings = new SettingsService();
$settings->set('key', 'value');
$value = $settings->get('key');
```

## API Reference

### Helper Functions

#### `settings(?string $key = null, $default = null)`

Get a setting value or return the service instance.

- **$key**: Setting key (optional)
- **$default**: Default value if setting doesn't exist
- **Returns**: Setting value or SettingsService instance

#### `feature(string $key, bool $default = false): bool`

Check if a feature flag is enabled.

- **$key**: Feature key
- **$default**: Default value if feature doesn't exist
- **Returns**: Boolean

### Service Methods

#### `get(string $key, $default = null, ?string $context = null)`

Get a setting value.

#### `set(string $key, $value, ?string $context = null): bool`

Set a setting value.

#### `has(string $key, ?string $context = null): bool`

Check if a setting exists.

#### `forget(string $key, ?string $context = null): bool`

Delete a setting.

#### `feature(string $key, bool $default = false): bool`

Check if a feature is enabled.

#### `enableFeature(string $key): bool`

Enable a feature flag.

#### `disableFeature(string $key): bool`

Disable a feature flag.

## Examples

### Using in Controllers

```php
<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected $helpers = ['settings'];
    
    public function index()
    {
        $data['siteName'] = settings('site.name', 'Default Site');
        $data['showNewFeature'] = feature('new_dashboard');
        
        return view('welcome_message', $data);
    }
    
    public function updateSettings()
    {
        settings()->set('site.name', $this->request->getPost('name'));
        settings()->set('site.email', $this->request->getPost('email'));
        
        return redirect()->back()->with('message', 'Settings updated!');
    }
}
```

### Using in Views

```php
<h1><?= settings('site.name', 'Welcome') ?></h1>

<?php if (feature('dark_mode')): ?>
    <link rel="stylesheet" href="/css/dark-theme.css">
<?php endif; ?>
```

### Feature Toggle Pattern

```php
<?php

namespace App\Controllers;

class Checkout extends BaseController
{
    public function index()
    {
        if (feature('new_checkout_flow')) {
            return $this->newCheckout();
        }
        
        return $this->legacyCheckout();
    }
    
    private function newCheckout()
    {
        // New checkout implementation
        return view('checkout/new');
    }
    
    private function legacyCheckout()
    {
        // Old checkout implementation
        return view('checkout/legacy');
    }
}
```

## Requirements

- PHP 7.4 or higher
- CodeIgniter 4.0 or higher

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

Created by Olanrewaju Olajide
