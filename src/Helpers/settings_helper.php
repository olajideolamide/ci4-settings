<?php

use Olajideolamide\CI4Settings\Services\SettingsService;

if (!function_exists('settings')) {
    /**
     * Get or set a setting value
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed|SettingsService
     */
    function settings(?string $key = null, $default = null)
    {
        $service = new SettingsService();
        
        if ($key === null) {
            return $service;
        }
        
        return $service->get($key, $default);
    }
}

if (!function_exists('feature')) {
    /**
     * Check if a feature flag is enabled
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    function feature(string $key, bool $default = false): bool
    {
        $service = new SettingsService();
        return $service->feature($key, $default);
    }
}
