<?php

namespace Jide\Settings\Tests\Stubs;

use Jide\Settings\Config\Settings as SettingsConfig;

class SettingConfigStub extends SettingsConfig
{
    public function __construct()
    {
        $this->useCache = false;
        $this->cacheKey = 'settings_test';
        $this->cacheTTL = 60;
    }
}
