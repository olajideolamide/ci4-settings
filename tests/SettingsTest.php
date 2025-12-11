<?php

namespace Jide\Settings\Tests;

use Jide\Settings\Tests\Stubs\SettingModelStub;
use Jide\Settings\Tests\Stubs\SettingConfigStub;
use PHPUnit\Framework\TestCase;
use Jide\Settings\Libraries\Settings;

final class SettingsTest extends TestCase
{
    public function testSetAndGetScalar()
    {
        $model = new SettingModelStub();
        $config = new SettingConfigStub();

        $settings = new Settings($config, $model);

        $ok = $settings->set('app.name', 'My App');
        $this->assertTrue($ok, 'set should return true');

        $value = $settings->get('app.name');
        $this->assertSame('My App', $value);
    }

    //test setting and getting a nested JSON key
    public function testSetAndGetNestedJsonKey()
    {
        $model = new SettingModelStub();
        $config = new SettingConfigStub();

        $settings = new Settings($config, $model);
        $ok = $settings->set('app.mail.host', 'smtp.example');
        $this->assertTrue($ok, 'set should return true for nested key');

        $value = $settings->get('app.mail.host');
        $this->assertSame('smtp.example', $value);

        $ok = $settings->set('mail', [
            'host'       => 'smtp.example.com',
            'port'       => 587,
            'encryption' => 'tls',
            'username'   => 'user@example.com',
            'password'   => 'secret',
        ]);

        $this->assertTrue($ok, 'set should return true for nested key');

        $value = $settings->get('mail.host');
        $this->assertSame('smtp.example.com', $value);
    }

    public function testDeleteNestedJsonKeyUpdatesParent()
    {
        $model = new SettingModelStub();
        $config = new SettingConfigStub();

        // seed a parent JSON row
        $model->setRows([
            [
                'id'    => 1,
                'key'   => 'app.mail',
                'value' => json_encode(['host' => 'smtp.example', 'user' => 'noreply']),
                'type'  => 'json',
            ],
        ]);

        $settings = new Settings($config, $model);

        // delete nested key
        $deleted = $settings->delete('app.mail.host');
        $this->assertTrue($deleted, 'delete should return true for nested removal');

        $rows = $model->getRows();
        $this->assertCount(1, $rows, 'parent row should remain (with updated JSON)');
        $parent = $rows[0];

        $decoded = json_decode($parent['value'], true);
        $this->assertArrayNotHasKey('host', $decoded);
        $this->assertSame('noreply', $decoded['user']);
        $this->assertSame('json', $parent['type']);
    }

    public function testFeatureEnabledReadsBool()
    {
        $model = new SettingModelStub();
        $config = new SettingConfigStub();

        $model->setRows([
            [
                'id'    => 1,
                'key'   => 'feature.coolFeature',
                'value' => '1',
                'type'  => 'bool',
            ],
        ]);

        $settings = new Settings($config, $model);

        $this->assertTrue($settings->featureEnabled('coolFeature'));
        $this->assertTrue($settings->featureEnabled('feature.coolFeature'));
        $this->assertFalse($settings->featureEnabled('nonexistent', false));
    }
}
