<?php

namespace Compolomus\LSQLQueryBuilder\Parts;

use Compolomus\LSQLQueryBuilder\System\Traits\{
    Helper,
    Caller,
    Placeholders
};

/**
 * @method string table()
 * @method void addPlaceholders($placeholders)
 */
class Insert
{
    use Caller, Placeholders, Helper;

    protected $fields = [];

    protected $values = [];

    public function __construct(array $args = [])
    {
        if (\count($args) > 0) {
            if (\is_string(key($args))) {
                $this->fields(array_keys($args));
            }
            $this->values(array_values($args));
        }
    }

    public function fields(array $fields): Insert
    {
        $this->fields = $fields;
        return $this;
    }

    public function values(array $values): Insert
    {
        $this->values[] = $this->set($values);
        return $this;
    }

    protected function preSet(array $values, string $flag): array
    {
        $result = [];
        foreach ($values as $value) {
            $key = $this->uid($flag);
            $result[] = ':' . $key;
            $this->placeholders()->set($key, $value);
        }
        return $result;
    }

    protected function set(array $values): string
    {
        return '(' . $this->concat($this->preSet($values, 'i')) . ')';
    }

    protected function get(): string
    {
        $this->addPlaceholders($this->placeholders()->get());
        return 'INSERT INTO ' . $this->table() . ' '
            . '(' . $this->concat($this->escapeField($this->fields)) . ')'
            . ' VALUES '
            . (\count($this->values)
                ? $this->concat($this->values)
                : '(' . $this->concat(array_fill(0, \count($this->fields), '?')) . ')'
            );
    }
}
