<?php

namespace Jide\Settings\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'key',
        'value',
        'type',
        'group',
        'is_feature',
    ];
    
}
