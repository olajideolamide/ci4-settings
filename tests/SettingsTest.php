<?php

namespace Tests\Unit\Libraries;

use PHPUnit\Framework\TestCase;
use Jide\Settings\Libraries\Settings;
use Jide\Settings\Config\Settings as SettingsConfig;
use Jide\Settings\Models\SettingModel;

/**
 * Lightweight in-memory stub for SettingModel to avoid DB dependencies.
 */
class SettingModelStub extends SettingModel
{
    protected array $rows = [];
    protected ?string $whereKey = null;
    
    public function __construct()
    {
        // Avoid parent initialization that may require CI DB
    }
    
    public function where($col, $val)
    {
        $this->whereKey = $val;
        return $this;
    }
    
    public function select($cols)
    {
        return $this;
    }
    
    public function first()
    {
        if ($this->whereKey === null) {
            return null;
        }
        foreach ($this->rows as $row) {
            if (($row['key'] ?? null) === $this->whereKey) {
                return $row;
            }
        }
        return null;
    }
    
    public function findAll(?int $limit = null, int $offset = 0)
    {
        return $this->rows;
    }
    
    public function insert($data = null, bool $returnID = true)
    {
        $id = $this->rows ? (max(array_column($this->rows, 'id')) + 1) : 1;
        $data['id'] = $id;
        $this->rows[] = $data;
        return $id;
    }
    
    public function update($id = null, $data = null): bool
    {
        foreach ($this->rows as $i => $row) {
            if (($row['id'] ?? null) == $id) {
                $this->rows[$i] = array_merge($row, $data);
                $this->rows[$i]['id'] = $id;
                return true;
            }
        }
        return false;
    }
    
    public function delete($id = null, bool $purge = false): bool
    {
        foreach ($this->rows as $i => $row) {
            if (($row['id'] ?? null) == $id) {
                array_splice($this->rows, $i, 1);
                return true;
            }
        }
        return false;
    }
    
    // Helpers for tests
    public function setRows(array $rows): void
    {
        $this->rows = $rows;
    }
    
    public function getRows(): array
    {
        return $this->rows;
    }
}

/**
 * Minimal config stub that disables cache for predictable tests.
 */
class SettingsConfigStub extends SettingsConfig
{
    public function __construct()
    {
        $this->useCache = false;
        $this->cacheKey = 'settings_test';
        $this->cacheTTL = 60;
    }
}

/**
 * PHPUnit tests for Jide\Settings\Libraries\Settings
 * File location: app/ThirdParty/ci4-settings/test/SettingsTest.php
 */
final class SettingsTest extends TestCase
{
    public function testSetAndGetScalar()
    {
        $model = new SettingModelStub();
        $config = new SettingsConfigStub();
        
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
        $config = new SettingsConfigStub();
        
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
        $config = new SettingsConfigStub();
        
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
        $config = new SettingsConfigStub();
        
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
