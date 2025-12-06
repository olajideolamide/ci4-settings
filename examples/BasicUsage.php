<?php

/**
 * Basic Usage Example for CI4 Settings Library
 * 
 * This example demonstrates how to use the settings library
 * in a CodeIgniter 4 application.
 */

// Load the helper
helper('settings');

// ==============================================
// BASIC SETTINGS OPERATIONS
// ==============================================

// Get a setting with a default value
$siteName = settings('site.name', 'My Default Site');
echo "Site Name: {$siteName}\n";

// Set a setting value
settings()->set('site.name', 'My Awesome Website');
settings()->set('site.email', 'admin@example.com');
settings()->set('site.tagline', 'Building something great');

// Get the updated setting
$siteName = settings('site.name');
echo "Updated Site Name: {$siteName}\n";

// Check if a setting exists
if (settings()->has('site.name')) {
    echo "Setting 'site.name' exists!\n";
}

// ==============================================
// DIFFERENT DATA TYPES
// ==============================================

// Integer
settings()->set('max_upload_size', 1024);
$maxSize = settings('max_upload_size');
echo "Max Upload Size: {$maxSize} MB\n";

// Boolean
settings()->set('maintenance_mode', false);
$inMaintenance = settings('maintenance_mode');
echo "Maintenance Mode: " . ($inMaintenance ? 'Yes' : 'No') . "\n";

// JSON/Array
settings()->set('social_links', [
    'facebook' => 'https://facebook.com/mysite',
    'twitter' => 'https://twitter.com/mysite',
    'instagram' => 'https://instagram.com/mysite',
]);
$socialLinks = settings('social_links');
echo "Facebook: {$socialLinks['facebook']}\n";

// ==============================================
// FEATURE FLAGS
// ==============================================

// Enable a feature
settings()->enableFeature('new_dashboard');
settings()->enableFeature('dark_mode');
settings()->enableFeature('beta_features');

// Check if a feature is enabled
if (feature('new_dashboard')) {
    echo "New dashboard is enabled!\n";
    // Show new dashboard
} else {
    echo "Using old dashboard\n";
    // Show old dashboard
}

// Disable a feature
settings()->disableFeature('beta_features');

if (!feature('beta_features')) {
    echo "Beta features are disabled\n";
}

// Check with default value
if (feature('experimental_feature', false)) {
    echo "Experimental feature is enabled\n";
} else {
    echo "Experimental feature is not enabled (default: false)\n";
}

// ==============================================
// CONTEXT-BASED SETTINGS
// ==============================================

// Store user-specific settings
settings()->set('theme', 'dark', 'user');
settings()->set('language', 'en', 'user');
settings()->set('notifications', true, 'user');

// Get user-specific settings
$theme = settings()->get('theme', 'light', 'user');
$language = settings()->get('language', 'en', 'user');

echo "User Theme: {$theme}\n";
echo "User Language: {$language}\n";

// Store application-level settings
settings()->set('app_version', '1.0.0', 'app');
settings()->set('api_key', 'secret-key-123', 'app');

$version = settings()->get('app_version', '0.0.0', 'app');
echo "App Version: {$version}\n";

// ==============================================
// DELETE SETTINGS
// ==============================================

// Delete a setting
settings()->forget('old_setting');

// Delete a setting with context
settings()->forget('theme', 'user');

echo "\nAll operations completed successfully!\n";
