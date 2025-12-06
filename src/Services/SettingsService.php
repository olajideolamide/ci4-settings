<?php

namespace Olajideolamide\CI4Settings\Services;

use Olajideolamide\CI4Settings\Models\SettingModel;

class SettingsService
{
    /**
     * @var SettingModel
     */
    protected $model;

    /**
     * Cache for loaded settings
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = new SettingModel();
    }

    /**
     * Get a setting value
     *
     * @param string $key
     * @param mixed $default
     * @param string|null $context
     * @return mixed
     */
    public function get(string $key, $default = null, ?string $context = null)
    {
        $cacheKey = $this->getCacheKey($key, $context);
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $setting = $this->model->getSetting($key, $context);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $this->castValue($setting['value'], $setting['type']);
        $this->cache[$cacheKey] = $value;
        
        return $value;
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $context
     * @return bool
     */
    public function set(string $key, $value, ?string $context = null): bool
    {
        $type = $this->detectType($value);
        $storedValue = $this->prepareValue($value, $type);
        
        $result = $this->model->setSetting($key, $storedValue, $type, $context);
        
        if ($result) {
            $cacheKey = $this->getCacheKey($key, $context);
            $this->cache[$cacheKey] = $value;
        }
        
        return $result;
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @param string|null $context
     * @return bool
     */
    public function has(string $key, ?string $context = null): bool
    {
        return $this->model->getSetting($key, $context) !== null;
    }

    /**
     * Delete a setting
     *
     * @param string $key
     * @param string|null $context
     * @return bool
     */
    public function forget(string $key, ?string $context = null): bool
    {
        $cacheKey = $this->getCacheKey($key, $context);
        unset($this->cache[$cacheKey]);
        
        return $this->model->deleteSetting($key, $context);
    }

    /**
     * Check if a feature flag is enabled
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function feature(string $key, bool $default = false): bool
    {
        $value = $this->get("feature.{$key}", $default, 'feature');
        
        return (bool) $value;
    }

    /**
     * Enable a feature flag
     *
     * @param string $key
     * @return bool
     */
    public function enableFeature(string $key): bool
    {
        return $this->set("feature.{$key}", true, 'feature');
    }

    /**
     * Disable a feature flag
     *
     * @param string $key
     * @return bool
     */
    public function disableFeature(string $key): bool
    {
        return $this->set("feature.{$key}", false, 'feature');
    }

    /**
     * Cast value to appropriate type
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Detect the type of a value
     *
     * @param mixed $value
     * @return string
     */
    protected function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        
        if (is_int($value)) {
            return 'integer';
        }
        
        if (is_array($value) || is_object($value)) {
            return 'json';
        }
        
        return 'string';
    }

    /**
     * Prepare value for storage
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function prepareValue($value, string $type): string
    {
        if ($type === 'json') {
            return json_encode($value);
        }
        
        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }
        
        return (string) $value;
    }

    /**
     * Generate cache key
     *
     * @param string $key
     * @param string|null $context
     * @return string
     */
    protected function getCacheKey(string $key, ?string $context = null): string
    {
        return $context ? "{$context}.{$key}" : $key;
    }
}
