<?php

use Jide\Settings\Libraries\Settings as SettingsLib;

if (! function_exists('settings')) {
    /**
     * Usage:
     *  settings('site.name'); // get
     *  settings()->set('site.name', 'My App'); // set
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed|SettingsLib
     */
    function settings(?string $key = null, mixed $default = null)
    {
        static $instance;

        if ($instance === null) {
            $instance = new SettingsLib();
        }

        if ($key === null) {
            return $instance;
        }

        return $instance->get($key, $default);
    }
}

if (! function_exists('feature')) {
    /**
     * Check if a feature flag is enabled.
     *
     * @param string $flag
     * @param bool $default
     * @return bool
     */
    function feature(string $flag, bool $default = false): bool
    {
        /** @var SettingsLib $settings */
        $settings = settings();

        return $settings->featureEnabled($flag, $default);
    }
}
