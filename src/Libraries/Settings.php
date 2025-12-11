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
     * Get a setting by key (supports dot notation).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        return $this->arrayGet($all, $key, $default);
    }

    /**
     * Set a setting (create or update).
     */
    public function set(string $key, mixed $value, ?string $group = null, bool $isFeature = false): bool
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
     * Delete a setting by key (supports dot notation removal inside parent JSON entries).
     */
    public function delete(string $key): bool
    {
        // Delete exact key if present
        $deleted = false;
        $existing = $this->model->where('key', $key)->first();
        if ($existing) {
            $this->model->delete($existing['id']);
            $deleted = true;
        }

        // Also try to remove nested key from any parent JSON entries (e.g. deleting "app.mail.host"
        // from a stored JSON key "app.mail")
        $parts = explode('.', $key);
        while (count($parts) > 1) {
            array_pop($parts);
            $parentKey = implode('.', $parts);
            $parent = $this->model->where('key', $parentKey)->first();
            if ($parent && ($parent['type'] ?? '') === 'json') {
                $decoded = json_decode($parent['value'], true);
                if (is_array($decoded) && $this->arrayHas($decoded, $this->lastSegment($key))) {
                    $this->arrayUnset($decoded, $this->lastSegment($key));
                    if (empty($decoded)) {
                        // remove parent if empty
                        $this->model->delete($parent['id']);
                    } else {
                        $this->model->update($parent['id'], [
                            'value' => json_encode($decoded),
                            'type'  => 'json',
                        ]);
                    }
                    $deleted = true;
                    break;
                }
            }
        }

        $this->clearCache();

        return $deleted;
    }

    /**
     * Returns true if a setting exists (supports dot notation).
     */
    public function has(string $key): bool
    {
        $all = $this->all();

        return $this->arrayHas($all, $key);
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
     * Get all settings as key => value (builds nested arrays from dotted keys).
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
            $casted = $this->castValue($row['value'], $row['type'] ?? 'string');

            // If casted value is an array (from json), merge it into the nested path.
            if (is_array($casted)) {
                $this->arraySet($settings, $row['key'], $casted, true); // merge arrays
            } else {
                $this->arraySet($settings, $row['key'], $casted, false); // set/override scalar
            }
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
            default => (string) $value,
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

    // ----- Dot-notation array helpers -----

    protected function arrayGet(array $array, string $path, $default = null)
    {
        if ($path === '') {
            return $array;
        }

        if (array_key_exists($path, $array)) {
            return $array[$path];
        }

        $segments = explode('.', $path);
        $current = $array;

        foreach ($segments as $seg) {
            if (!is_array($current) || !array_key_exists($seg, $current)) {
                return $default;
            }
            $current = $current[$seg];
        }

        return $current;
    }

    protected function arrayHas(array $array, string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (array_key_exists($path, $array)) {
            return true;
        }

        $segments = explode('.', $path);
        $current = $array;

        foreach ($segments as $seg) {
            if (!is_array($current) || !array_key_exists($seg, $current)) {
                return false;
            }
            $current = $current[$seg];
        }

        return true;
    }

    /**
     * Set a value into an array using dot path.
     * If \$mergeArrays is true and both existing and new values are arrays, they will be merged.
     */
    protected function arraySet(array &$array, string $path, $value, bool $mergeArrays = false): void
    {
        if ($path === '') {
            return;
        }

        // If no dots just set top-level key
        if (! str_contains($path, '.')) {
            if ($mergeArrays && isset($array[$path]) && is_array($array[$path]) && is_array($value)) {
                $array[$path] = $this->arrayMergeRecursiveDistinct($array[$path], $value);
            } else {
                $array[$path] = $value;
            }
            return;
        }

        $segments = explode('.', $path);
        $current = & $array;

        foreach ($segments as $i => $seg) {
            if ($i === count($segments) - 1) {
                // last segment
                if ($mergeArrays && isset($current[$seg]) && is_array($current[$seg]) && is_array($value)) {
                    $current[$seg] = $this->arrayMergeRecursiveDistinct($current[$seg], $value);
                } else {
                    $current[$seg] = $value;
                }
                return;
            }

            if (!isset($current[$seg]) || !is_array($current[$seg])) {
                $current[$seg] = [];
            }

            $current = & $current[$seg];
        }
    }

    protected function arrayUnset(array &$array, string $path): void
    {
        if ($path === '') {
            return;
        }

        if (! str_contains($path, '.')) {
            if (array_key_exists($path, $array)) {
                unset($array[$path]);
            }
            return;
        }

        $segments = explode('.', $path);
        $stack = [];
        $current = & $array;

        foreach ($segments as $seg) {
            if (!is_array($current) || !array_key_exists($seg, $current)) {
                return; // nothing to unset
            }
            $stack[] = [&$current, $seg];
            $current = & $current[$seg];
        }

        // unset last
        $last = array_pop($stack);
        unset($last[0][$last[1]]);

        // cleanup empty arrays up the chain
        while (!empty($stack)) {
            $entry = array_pop($stack);
            [$parentRef, $key] = $entry;
            if (is_array($parentRef[$key]) && empty($parentRef[$key])) {
                unset($parentRef[$key]);
            } else {
                break;
            }
        }
    }

    /**
     * Merge two arrays recursively without converting numeric keys into arrays of values.
     */
    protected function arrayMergeRecursiveDistinct(array $a, array $b): array
    {
        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a) && is_array($a[$key]) && is_array($value)) {
                $a[$key] = $this->arrayMergeRecursiveDistinct($a[$key], $value);
            } else {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    protected function lastSegment(string $path): string
    {
        $parts = explode('.', $path);
        return end($parts);
    }
}
