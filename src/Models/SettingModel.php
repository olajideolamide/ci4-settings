<?php

namespace Olajideolamide\CI4Settings\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['key', 'value', 'type', 'context'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'key'   => 'required|max_length[255]',
        'value' => 'required',
        'type'  => 'in_list[string,integer,boolean,json]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Apply context filter to builder
     *
     * @param string|null $context
     * @return $this
     */
    protected function applyContextFilter(?string $context = null)
    {
        if ($context !== null) {
            $this->where('context', $context);
        }
        
        return $this;
    }

    /**
     * Get a setting by key
     *
     * @param string $key
     * @param string|null $context
     * @return array|null
     */
    public function getSetting(string $key, ?string $context = null): ?array
    {
        return $this->where('key', $key)
            ->applyContextFilter($context)
            ->first();
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $context
     * @return bool
     */
    public function setSetting(string $key, $value, string $type = 'string', ?string $context = null): bool
    {
        $existing = $this->getSetting($key, $context);
        
        $data = [
            'key'     => $key,
            'value'   => $value,
            'type'    => $type,
            'context' => $context,
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        
        return $this->insert($data) !== false;
    }

    /**
     * Delete a setting by key
     *
     * @param string $key
     * @param string|null $context
     * @return bool
     */
    public function deleteSetting(string $key, ?string $context = null): bool
    {
        return $this->where('key', $key)
            ->applyContextFilter($context)
            ->delete();
    }
}
