<?php

namespace Jide\Settings\Libraries;

use Jide\Settings\Config\Settings as SettingsConfig;
use Jide\Settings\Models\SettingModel;

class Settings
{
    protected SettingsConfig $config;
    protected SettingModel $model;

    /**
     * Cached array of all settings.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $settings = null;

    public function __construct(?SettingsConfig $config = null, ?SettingModel $model = null)
    {
        $this->config = $config ?? new SettingsConfig();
        $this->model  = $model  ?? new SettingModel();
    }

    /**
     * Get a setting by key.
     */
    public function get(string $key, $default = null):mixed
    {
        $all = $this->all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Set a setting (create or update).
     */
    public function set(string $key, $value, ?string $group = null, bool $isFeature = false): bool
    
    {
        $type  = $this->detectType($value);
        $value = $this->prepareValue($value, $type);

        $data = [
            'key'        => $key,
            'value'      => $value,
            'type'       => $type,
            'group'      => $group,
            'is_feature' => $isFeature ? 1 : 0,
        ];

        $existing = $this->model->where('key', $key)->first();

        if ($existing) {
            $this->model->update($existing['id'], $data);
        } else {
            $this->model->insert($data);
        }

        $this->clearCache();

        return true;
    }

    /**
     * Delete a setting by key.
     */
    public function delete(string $key): bool
    {
        $this->model->where('key', $key)->delete();
        $this->clearCache();

        return true;
    }

    /**
     * Returns true if a setting exists.
     */
    public function has(string $key): bool
    {
        $all = $this->all();

        return array_key_exists($key, $all);
    }

    /**
     * Check if a feature flag is enabled.
     *
     * Convention: feature flags are stored as keys like "feature.xyz".
     */
    public function featureEnabled(string $flag, bool $default = false): bool
    {
        $key   = str_starts_with($flag, 'feature.') ? $flag : 'feature.' . $flag;
        $value = $this->get($key, $default);

        return $this->toBool($value);
    }

    /**
     * Get all settings as key => value.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        // Try cache
        if ($this->config->useCache && function_exists('cache')) {
            $cached = cache($this->config->cacheKey);
            if (is_array($cached)) {
                $this->settings = $cached;
                return $this->settings;
            }
        }

        $rows = $this->model->select(['key', 'value', 'type'])->findAll();
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['key']] = $this->castValue($row['value'], $row['type'] ?? 'string');
        }

        $this->settings = $settings;

        if ($this->config->useCache && function_exists('cache')) {
            cache()->save($this->config->cacheKey, $settings, $this->config->cacheTTL);
        }

        return $this->settings;
    }

    /**
     * Clear cache and in-memory store.
     */
    protected function clearCache(): void
    {
        $this->settings = null;

        if ($this->config->useCache && function_exists('cache')) {
            cache()->delete($this->config->cacheKey);
        }
    }

    protected function detectType($value): string
    {
        if (is_bool($value)) {
            return 'bool';
        }

        if (is_int($value)) {
            return 'int';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }

    protected function prepareValue($value, string $type): string
    {
        return match ($type) {
            'bool' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string)$value,
        };
    }

    protected function castValue(?string $value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'bool':
                return $this->toBool($value);

            case 'int':
                return (int) $value;

            case 'float':
                return (float) $value;

            case 'json':
                $decoded = json_decode($value, true);
                return $decoded === null ? $value : $decoded;

            default:
                return $value;
        }
    }

    protected function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
