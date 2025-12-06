<?php

namespace Olajideolamide\CI4Settings\Config;

use CodeIgniter\Config\BaseConfig;

class Settings extends BaseConfig
{
    /**
     * Default cache time for settings (in seconds)
     * Set to 0 to disable caching
     *
     * @var int
     */
    public $cacheTime = 3600;

    /**
     * Database table name for settings
     *
     * @var string
     */
    public $tableName = 'settings';

    /**
     * Enable automatic loading of helper
     *
     * @var bool
     */
    public $autoloadHelper = true;
}
