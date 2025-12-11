<?php

namespace Jide\Settings\Tests\Stubs;

use Jide\Settings\Models\SettingModel;

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
