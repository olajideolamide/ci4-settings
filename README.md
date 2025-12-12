<picture>
        <source media="(prefers-color-scheme: dark)" srcset="docs/screenshots/banner.png">
        <img alt="Logo for laravel-activitylog" src="docs/screenshots/banner.png">
</picture>

<h1>CodeIgniter 4 Settings & Feature Flags Library</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/olajideolamide/ci4-settings.svg)](https://packagist.org/packages/olajideolamide/ci4-settings)
![GitHub License](https://img.shields.io/github/license/olajideolamide/ci4-settings)
![PHP UNIT](https://github.com/olajideolamide/ci4-settings/actions/workflows/phpunit.yml/badge.svg)
![PHP STAN](https://github.com/olajideolamide/ci4-settings/actions/workflows/phpstan.yml/badge.svg)
![PHP CS](https://github.com/olajideolamide/ci4-settings/actions/workflows/phpcs.yml/badge.svg)



Database-backed application settings and feature flags for CodeIgniter 4, with dot-notation support, automatic type casting, and optional caching.

This package gives you a simple `settings()` helper and a small `Settings` library so you can store configuration in the database instead of hard-coding everything in config files.

---

## Features

- Database-backed settings using a dedicated `settings` table
- Feature flags via a simple `feature('flag_name')` helper
- Dot-notation keys like `app.name`, `mail.smtp.host`, etc.
- Automatic type detection & casting
    - `bool`, `int`, `float`, `string`, `json` (arrays/objects)
- Nested settings & JSON
    - Store arrays/objects and access them via dot notation
- Optional caching via CodeIgniter’s cache system
- Convenience helpers
    - `settings()` – get/set/delete settings
    - `feature()` – check if a feature flag is enabled
- Smart delete
    - Can remove nested keys from JSON settings (e.g. remove `app.mail.host` from a stored `app.mail` JSON blob)

---

## Requirements

- PHP **8.0+**
- CodeIgniter **4.x**
- A database supported by CodeIgniter 4

---

## Installation

### 1. Install via Composer

```bash
composer require olajideolamide/ci4-settings
```

### 2. Register the namespace (if needed)

In most Composer-based CodeIgniter 4 apps, Composer’s autoloader is already wired up.  
If you are using modules and explicit namespaces, add the package namespace to `app/Config/Autoload.php`:

```php
// app/Config/Autoload.php

public $psr4 = [
    APP_NAMESPACE => APPPATH,
    'Config'      => APPPATH . 'Config',

    // Add this line (keep your existing entries)
    'Jide\Settings' => ROOTPATH . 'vendor/olajideolamide/ci4-settings/src',
];
```

### 3. Run the migration

This package ships with a migration that creates the `settings` table.

Run:

```bash
php spark migrate -n Jide\Settings
```

This will create a table named `settings` with columns:

- `id` (INT, PK, auto-increment)
- `key` (VARCHAR, unique)
- `value` (TEXT)
- `type` (VARCHAR – `string`, `bool`, `int`, `float`, `json`, etc.)
- `group` (VARCHAR, nullable) – optional grouping for your own use
- `is_feature` (TINYINT(1)) – marks a setting as a feature flag
- `created_at`, `updated_at` (DATETIME, nullable)

### 4. Load the helper

To use the `settings()` and `feature()` helpers, load the helper:

**Per controller / per use:**

```php
helper('settings');
```

**Globally (recommended):**

In `app/Config/Autoload.php`:

```php
public $helpers = ['settings'];
```

Or in a base controller:

```php
protected $helpers = ['settings'];
```

---

## Quick Start

Once installed, migrated, and the helper is loaded, you can start using it immediately.

### Get a setting

```php
// Get 'app.name', return 'My App' if it doesn't exist
$appName = settings('app.name', 'My App');
```

### Set a setting

```php
// Using the helper to get the library instance
settings()->set('app.name', 'My Awesome App');
```

### Check if a setting exists

```php
if (settings()->has('app.name')) {
    // do something
}
```

### Delete a setting

```php
settings()->delete('app.name');
```

### Access all settings

```php
$config = settings()->all(); // returns nested array of all settings
```

---

## Feature Flags

Feature flags make it easy to toggle functionality on or off without redeploying code.

### Enable a feature flag

Feature flags are stored as keys under the `feature.` namespace, for example: `feature.new_checkout`.

```php
// Create or update a feature flag
settings()->set(
    'feature.new_checkout',
    true,                // value
    'checkout',          // optional group
    true                 // is_feature = true
);
```

### Check if a feature is enabled (helper)

```php
if (feature('new_checkout')) {
    // show new checkout flow
} else {
    // fallback to old flow
}
```

You can also pass the full key:

```php
if (feature('feature.new_checkout')) {
    // also works
}
```

### Check with a default value

```php
if (feature('beta_banner', false)) {
    // only runs if explicitly enabled
}
```

Under the hood, `feature('something')`:

- Ensures the key is prefixed with `feature.` if you didn’t add it
- Reads the setting
- Casts it to a boolean using common truthy representations: `1`, `true`, `yes`, `on` (case-insensitive)

---

## Dot-Notation & Nested Settings

This library supports **dot-notation** for keys, and can store arrays/objects as JSON.

### Storing a nested array

```php
settings()->set('mail', [
    'host'       => 'smtp.example.com',
    'port'       => 587,
    'encryption' => 'tls',
    'username'   => 'user@example.com',
    'password'   => 'secret',
]);
```

Internally this is saved as:

- `key`   = `mail`
- `type`  = `json`
- `value` = JSON encoding of the array

### Reading nested values

```php
$host = settings('mail.host');       // "smtp.example.com"
$port = settings('mail.port');       // 587 (int)
$enc  = settings('mail.encryption'); // "tls"
```

### Updating part of a JSON setting

You can overwrite the whole array:

```php
settings()->set('mail', [
    'host' => 'smtp2.example.com',
] + settings('mail', []));
```

Or add nested configuration piece by piece using a more granular key:

```php
$config = settings()->all(); // or build what you need

// For example, to create a grouped "app" config
settings()->set('app', [
    'name' => 'My App',
    'mail' => [
        'host' => 'smtp.example.com',
    ],
]);

// Later, read:
$host = settings('app.mail.host');
```

When JSON values are loaded, they are decoded into arrays and merged into the full settings structure so that dot-paths like `app.mail.host` work as expected.

---

## Type Handling

The library automatically detects and casts types based on the value you set.

### Detection

When you call `set($key, $value, ...)`, the library determines:

- `bool`   → stored as `'1'` or `'0'`, returned as `bool`
- `int`    → stored as string, returned as `int`
- `float`  → stored as string, returned as `float`
- `array` / `object` → stored as JSON (`type = json`), returned as array
- anything else → stored and returned as `string`

### Casting on read

When you call `get()` or `settings('key')`:

- If `type = bool` it’s converted using a liberal boolean parser:
    - `1`, `true`, `yes`, `on` → `true`
    - Anything else → `false`
- If `type = int` → `(int)$value`
- If `type = float` → `(float)$value`
- If `type = json` → `json_decode()` to an array
- Otherwise → raw string

---

## Settings Helper & Library API

### `settings(?string $key = null, mixed $default = null)`

- If **no key** is passed (`settings()`):
    - Returns an instance of `Jide\Settings\Libraries\Settings`
- If **a key** is passed (`settings('app.name')`):
    - Returns the setting value (typed) or `$default` if not found

**Examples:**

```php
// Get instance
$settings = settings();

// Get value
$appName = settings('app.name', 'My App');

// Chain methods
settings()->set('app.name', 'My App');
```

### `feature(string $flag, bool $default = false): bool`

Convenience wrapper around `Settings::featureEnabled()`.

- Accepts either `new_checkout` or `feature.new_checkout`
- Returns `bool`

---

### `Jide\Settings\Libraries\Settings` methods

If you prefer to work with the library directly:

```php
use Jide\Settings\Libraries\Settings;

$settings = new Settings(); // uses default config and model
```

Available methods:

- `get(string $key, $default = null): mixed`  
  Get a setting by key (supports dot notation).

- `set(string $key, $value, ?string $group = null, bool $isFeature = false): bool`  
  Create or update a setting.

- `all(): array`  
  Get all settings as a nested array.

- `has(string $key): bool`  
  Check if a setting exists (supports dot notation).

- `delete(string $key): bool`  
  Delete a setting by key.  
  Also attempts to remove nested keys from JSON parent entries (e.g. `app.mail.host` inside `app.mail`).

- `featureEnabled(string $flag, bool $default = false): bool`  
  Check if a feature flag is enabled.

---

## Caching

This library uses a small config class to control caching:

```php
// Jide\Settings\Config\Settings

public string $table    = 'settings';
public bool   $useCache = true;
public int    $cacheTTL = 600;        // seconds
public string $cacheKey = 'ci4_settings';
```

By default:

- All settings are cached in memory on first request
- If CodeIgniter’s `cache()` function is available and `$useCache` is `true`:
    - The settings array is stored under `$cacheKey` for `$cacheTTL` seconds
- Any `set()` or `delete()` call automatically clears the cache

If you want to customise these values at runtime, you can instantiate the library with your own config:

```php
use Jide\Settings\Config\Settings as SettingsConfig;
use Jide\Settings\Libraries\Settings;

// Create custom config
$config = new SettingsConfig();
$config->useCache = false;
$config->cacheTTL = 0;
$config->table    = 'app_settings'; // if you also customised the migration

$settings = new Settings($config);
```

> Note: the `helper('settings')` uses `new Settings()` with the **default** config.  
> If you need a customised config, create your own `Settings` instance as above.

---

## Building an Admin UI (Optional)

The table contains extra metadata to help you build an admin/settings UI:

- `group` – group related settings (e.g. `mail`, `app`, `billing`)
- `is_feature` – mark which rows are feature flags so you can list them separately

Example: only list feature flags:

```php
use Jide\Settings\Models\SettingModel;

$model = new SettingModel();

$flags = $model
    ->where('is_feature', 1)
    ->orderBy('key', 'ASC')
    ->findAll();
```

---

## License

This project is open-source software licensed under the [MIT license](LICENSE).

---

## Author

**Olanrewaju “Jide” Olajide**

- Package: `olajideolamide/ci4-settings`
- PRs, issues and suggestions are welcome!
