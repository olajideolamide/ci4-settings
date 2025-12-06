<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Example Controller showing how to use CI4 Settings
 * in a CodeIgniter 4 Controller
 */
class SettingsExample extends Controller
{
    /**
     * Load settings helper
     */
    protected $helpers = ['settings'];

    /**
     * Display site settings
     */
    public function index()
    {
        $data = [
            'siteName'    => settings('site.name', 'My Site'),
            'siteEmail'   => settings('site.email', 'admin@example.com'),
            'siteTagline' => settings('site.tagline', 'Welcome'),
            'maintenance' => settings('maintenance_mode', false),
        ];

        return view('settings/index', $data);
    }

    /**
     * Update site settings
     */
    public function update()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $siteName = $this->request->getPost('site_name');
        $siteEmail = $this->request->getPost('site_email');
        $maintenance = $this->request->getPost('maintenance_mode') === '1';

        settings()->set('site.name', $siteName);
        settings()->set('site.email', $siteEmail);
        settings()->set('maintenance_mode', $maintenance);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Feature flags management
     */
    public function features()
    {
        $data = [
            'newDashboard'   => feature('new_dashboard'),
            'darkMode'       => feature('dark_mode'),
            'betaFeatures'   => feature('beta_features'),
            'newCheckout'    => feature('new_checkout'),
        ];

        return view('settings/features', $data);
    }

    /**
     * Toggle a feature flag
     */
    public function toggleFeature($featureName)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $currentState = feature($featureName);
        
        if ($currentState) {
            settings()->disableFeature($featureName);
            $newState = false;
        } else {
            settings()->enableFeature($featureName);
            $newState = true;
        }

        return $this->response->setJSON([
            'success' => true,
            'feature' => $featureName,
            'enabled' => $newState,
        ]);
    }

    /**
     * Example of using settings in different contexts
     */
    public function userSettings($userId)
    {
        // Get user-specific settings
        $userContext = "user_{$userId}";
        
        $data = [
            'theme'         => settings()->get('theme', 'light', $userContext),
            'language'      => settings()->get('language', 'en', $userContext),
            'notifications' => settings()->get('notifications', true, $userContext),
            'timezone'      => settings()->get('timezone', 'UTC', $userContext),
        ];

        return view('settings/user', $data);
    }

    /**
     * Update user-specific settings
     */
    public function updateUserSettings($userId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userContext = "user_{$userId}";
        
        $theme = $this->request->getPost('theme');
        $language = $this->request->getPost('language');
        $notifications = $this->request->getPost('notifications') === '1';

        settings()->set('theme', $theme, $userContext);
        settings()->set('language', $language, $userContext);
        settings()->set('notifications', $notifications, $userContext);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'User settings updated successfully',
        ]);
    }
}
