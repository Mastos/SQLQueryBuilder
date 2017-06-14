<?php

namespace Koenig\SQLQueryBuilder\Parts;

use Koenig\SQLQueryBuilder\System\Helper;
use Koenig\SQLQueryBuilder\System\Traits\Caller;
use Koenig\SQLQueryBuilder\System\Placeholders;

class Insert
{
    use Caller;

    private $fields = [];

    private $values = [];

    public function __construct(array $args = [])
    {
        if (count($args) > 0) {
            if (preg_match('/[a-z]+/i', key($args))) {
                $this->fields(array_keys($args));
            }
            $this->values(array_values($args));
        }
    }

    public function fields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function values($values)
    {
        $this->values[] = $this->set($values);
        return $this;
    }

    private function set($values)
    {
        $result = [];
        foreach ($values as $value) {
            $result[] = ':i' . Placeholders::$counter;
            Placeholders::add('i', $value);
        }
        return '(' . implode(',', $result) . ')';
    }

    public function get()
    {
        return 'INSERT INTO ' . $this->table() . ' '
            . (count($this->fields) ? '(' . implode(',', Helper::escapeField($this->fields)) . ')' : '')
            . ' VALUES '
            . (count($this->values)
                ? implode(',', $this->values)
                : (count($this->fields) & !count($this->values)
                    ? '(' . implode(',', array_fill(0, count($this->fields), '?')) . ')'
                    : '')
            );
    }
}
