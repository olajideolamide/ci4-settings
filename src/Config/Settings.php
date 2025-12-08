<?php

namespace Jide\Settings\Config;

use CodeIgniter\Config\BaseConfig;

class Settings extends BaseConfig
{
    /**
     * Database table name.
     */
    public string $table = 'settings';

    /**
     * Turn caching of settings on/off.
     */
    public bool $useCache = true;

    /**
     * Cache TTL in seconds.
     */
    public int $cacheTTL = 600;

    /**
     * Cache key to store all settings in.
     */
    public string $cacheKey = 'ci4_settings';
}
