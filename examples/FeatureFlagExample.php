<?php

/**
 * Feature Flag Example for CI4 Settings Library
 * 
 * This example demonstrates how to use feature flags
 * for gradual rollouts and A/B testing.
 */

helper('settings');

// ==============================================
// FEATURE FLAG PATTERNS
// ==============================================

/**
 * Example 1: Simple Feature Toggle
 */
function checkoutProcess()
{
    if (feature('new_checkout')) {
        return newCheckoutFlow();
    }
    
    return legacyCheckoutFlow();
}

function newCheckoutFlow()
{
    return "Using new checkout with one-click payment";
}

function legacyCheckoutFlow()
{
    return "Using traditional checkout process";
}

echo "Checkout: " . checkoutProcess() . "\n\n";

/**
 * Example 2: Gradual Feature Rollout
 */
function showDashboard($userId)
{
    // Enable new dashboard for specific users or percentages
    if (feature('new_dashboard_beta')) {
        // Check if user is in beta group
        if (in_array($userId, [1, 2, 3, 10, 15])) {
            return "Showing BETA dashboard (new features)";
        }
    }
    
    if (feature('new_dashboard')) {
        return "Showing NEW dashboard (stable)";
    }
    
    return "Showing OLD dashboard";
}

// Enable features
settings()->enableFeature('new_dashboard');

echo "User 1: " . showDashboard(1) . "\n";
echo "User 5: " . showDashboard(5) . "\n\n";

/**
 * Example 3: A/B Testing
 */
function getSearchAlgorithm()
{
    if (feature('search_algorithm_v2')) {
        return "Using Search Algorithm V2 (ML-based)";
    }
    
    return "Using Search Algorithm V1 (keyword-based)";
}

settings()->enableFeature('search_algorithm_v2');
echo "Search: " . getSearchAlgorithm() . "\n\n";

/**
 * Example 4: Access Control / Premium Features
 */
function canAccessPremiumFeature($userIsPremium)
{
    if (!feature('premium_features')) {
        return false;
    }
    
    return $userIsPremium;
}

settings()->enableFeature('premium_features');
echo "Premium User Access: " . (canAccessPremiumFeature(true) ? 'Granted' : 'Denied') . "\n";
echo "Free User Access: " . (canAccessPremiumFeature(false) ? 'Granted' : 'Denied') . "\n\n";

/**
 * Example 5: Maintenance Mode
 */
function isApplicationAccessible()
{
    if (feature('maintenance_mode')) {
        return "Application is in maintenance mode";
    }
    
    return "Application is accessible";
}

settings()->disableFeature('maintenance_mode');
echo "Status: " . isApplicationAccessible() . "\n\n";

/**
 * Example 6: Multi-Feature Check
 */
function getAvailableFeatures()
{
    $features = [];
    
    if (feature('dark_mode')) {
        $features[] = 'Dark Mode';
    }
    
    if (feature('notifications')) {
        $features[] = 'Push Notifications';
    }
    
    if (feature('export_data')) {
        $features[] = 'Data Export';
    }
    
    if (feature('advanced_analytics')) {
        $features[] = 'Advanced Analytics';
    }
    
    return $features;
}

// Enable some features
settings()->enableFeature('dark_mode');
settings()->enableFeature('notifications');
settings()->disableFeature('export_data');
settings()->disableFeature('advanced_analytics');

$availableFeatures = getAvailableFeatures();
echo "Available Features:\n";
foreach ($availableFeatures as $feature) {
    echo "  - {$feature}\n";
}
echo "\n";

/**
 * Example 7: Conditional View Rendering
 */
function renderHeader()
{
    $html = '<header>';
    
    if (feature('new_navigation')) {
        $html .= '<nav class="new-nav">New Navigation</nav>';
    } else {
        $html .= '<nav class="old-nav">Old Navigation</nav>';
    }
    
    if (feature('search_bar_in_header')) {
        $html .= '<div class="search">Search Bar</div>';
    }
    
    $html .= '</header>';
    
    return $html;
}

settings()->enableFeature('new_navigation');
settings()->enableFeature('search_bar_in_header');

echo "Header HTML:\n" . renderHeader() . "\n\n";

/**
 * Example 8: Feature Dependencies
 */
function canUseAdvancedEditor()
{
    // Advanced editor requires both features to be enabled
    if (feature('rich_text_editor') && feature('media_upload')) {
        return "Using Advanced Editor (Rich Text + Media)";
    }
    
    if (feature('rich_text_editor')) {
        return "Using Rich Text Editor (Basic)";
    }
    
    return "Using Plain Text Editor";
}

settings()->enableFeature('rich_text_editor');
settings()->enableFeature('media_upload');

echo "Editor: " . canUseAdvancedEditor() . "\n\n";

echo "All feature flag examples completed!\n";
