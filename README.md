# ci4-settings

Application settings and feature flags library for **CodeIgniter 4**.

- Store key/value settings in the database (`settings` table).
- Access them via a simple helper: `settings('site.name')`.
- Manage feature flags with `feature('new_checkout')`.
- Includes a simple Bootstrap 5 admin UI to manage settings in the browser.

---

## Features

- Simple key/value storage with type casting (string, bool, int, float, json).
- Optional grouping (e.g. `site`, `mail`, `features`).
- Feature flags with a dedicated `feature()` helper.
- Configurable caching using CodeIgniter's cache service.
- Admin UI (controller + views) to create, edit and delete settings.

---

## Installation

### 1. Install via Composer

After you publish this package to Packagist:

```bash
composer require jide/ci4-settings
```

If you are using it directly from GitHub as a VCS repository, add this to your main app's `composer.json`:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/YOUR_GITHUB_USERNAME/ci4-settings"
  }
],
"require": {
  "jide/ci4-settings": "^1.0"
}
```

Then run:

```bash
composer update
```

### 2. Make sure classes are autoloadable

Composer will autoload everything in `src/` via the namespace `Jide\\Settings\\`.

You don't need to touch `app/Config/Autoload.php` if you rely on Composer.

### 3. Run the migration

Copy the migration file from the package into your app (simplest approach):

From:

```text
vendor/jide/ci4-settings/src/Database/Migrations/2025-12-06-000000_create_settings_table.php
```

To:

```text
app/Database/Migrations/2025-12-06-000000_create_settings_table.php
```

Then run:

```bash
php spark migrate
```

This will create the `settings` table.

> Advanced users can add the package migration path to `Config\Migrations` instead of copying.

### 4. Load the helper

Either load the helper where needed:

```php
helper('settings');
```

Or add it to `app/Config/Autoload.php`:

```php
public $helpers = ['settings'];
```

---

## Basic usage

### Get a setting

```php
helper('settings');

$siteName = settings('site.name', 'My App');
$timezone = settings('app.timezone', 'Africa/Lagos');
```

### Set / update a setting

```php
$settings = settings(); // returns the library instance

// string
$settings->set('site.name', 'Nora Exchange', 'site');

// int
$settings->set('app.max_users', 500, 'app');

// array (stored as JSON)
$settings->set('mail.smtp', [
    'host' => 'smtp.example.com',
    'port' => 587,
], 'mail');
```

### Check feature flags

```php
// Somewhere in your bootstrap or a seeder:
$settings->set('feature.new_dashboard', true, 'features', true);
$settings->set('feature.beta_checkout', false, 'features', true);

// In controllers / views:
if (feature('new_dashboard')) {
    // Load the new dashboard
} else {
    // Load the old dashboard
}

// With default value if flag not set:
if (feature('experimental_widget', false)) {
    // ...
}
```

### Delete a setting

```php
$settings->delete('feature.experimental_widget');
```

### Get all settings

```php
$all = settings()->all(); // returns [ 'site.name' => '...', ... ]
```

---

## Caching

By default, settings are cached using CodeIgniter's cache service (`cache()` helper).

You can change this behaviour by publishing and editing the config:

```php
$config = new \Jide\Settings\Config\Settings();

// config/Settings.php (copy from vendor if you want to customise)
public bool $useCache = true;
public int  $cacheTTL = 600;
```

If you don't want caching, set `$useCache = false;`.

---

## Admin UI

The package includes a small Bootstrap 5-based admin UI for managing settings in the browser.

### View namespace

In your **main app** (`app/Config/Views.php`), register the view namespace:

```php
public array $viewNamespaces = [
    // existing namespaces...

    'JideSettings' => ROOTPATH . 'vendor/jide/ci4-settings/src/Views',
];
```

(Adjust the path if your package name or install path differs.)

### Routes

In `app/Config/Routes.php`:

```php
$routes->group('admin/settings', [
    'namespace' => 'Jide\Settings\Controllers',
    // 'filter'    => 'auth', // optionally protect with your auth filter
], static function ($routes) {
    $routes->get('/', 'SettingsAdminController::index');
    $routes->get('create', 'SettingsAdminController::create');
    $routes->post('create', 'SettingsAdminController::create');
    $routes->get('edit/(:num)', 'SettingsAdminController::edit/$1');
    $routes->post('update/(:num)', 'SettingsAdminController::update/$1');
    $routes->post('delete/(:num)', 'SettingsAdminController::delete/$1');
});
```

Now visit:

- `https://your-app.test/admin/settings` – list settings
- `https://your-app.test/admin/settings/create` – create a new setting

---

## Screenshots

Here’s a quick look at the built-in admin UI for managing settings.

### Settings List

![Settings List](docs/screenshots/settings-index.png)

The main list shows all settings grouped by **Group**, with **Key**, **Value**, **Type**, and whether it is a **Feature flag**.  
From here you can quickly **edit** or **delete** any setting, or click **Add Setting** to create a new one.

---

### Create Setting

![Create Setting](docs/screenshots/create-setting.png)

The create form allows you to:

- Define a unique **key** (e.g. `site.name`, `feature.new_dashboard`)
- Optionally group settings (e.g. `site`, `mail`, `features`)
- Choose a **type**: `string`, `bool`, `int`, `float`, or `json`
- Enter the **value** with helper text for boolean and JSON fields
- Flag it as a **feature flag** for use with the `feature()` helper

---

### Edit Setting

![Edit Setting](docs/screenshots/edit-setting.png)

The edit screen lets you update existing settings safely, with validation and the same type/value hints as the create form.  
Changes take effect immediately and cached values are automatically invalidated.

---

## Manual installation (without Composer)

1. Copy the `src/` folder into your app, e.g.:

   ```text
   app/ThirdParty/Settings/
   ```

2. Update `app/Config/Autoload.php`:

   ```php
   public $psr4 = [
       APP_NAMESPACE => APPPATH,
       'Config'      => APPPATH . 'Config',
       'Jide\\Settings\\' => APPPATH . 'ThirdParty/Settings',
   ];
   ```

3. Copy the migration file into `app/Database/Migrations/` and run:

   ```bash
   php spark migrate
   ```

4. Copy the helper file to `app/Helpers/settings_helper.php` or configure the helper path, then:

   ```php
   helper('settings');
   ```

5. Use as shown above.

---

## License

MIT (or your preferred open source license).
