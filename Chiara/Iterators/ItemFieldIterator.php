<?php
namespace Chiara\Iterators;
use Chiara\PodioItem, Chiara\PodioItem\Field;

class ItemFieldIterator extends \ArrayIterator
{
    protected $item;
    protected $map = array();
    protected $reverse_map = array();
    function __construct(PodioItem $item)
    {
        $this->item = $item;
        parent::__construct($f = $item->info['fields']);
        foreach ($f as $i => $field) {
            $this->map[$field['field_id']] = $this->map[$field['external_id']] = $i;
            $this->reverse_map[$i] = $field['external_id'];
        }
    }

    function current()
    {
        $info = parent::current();
        return Field::newField($info);
    }

    function offsetGet($index)
    {
        if (is_int($index) && $index < 30) {
            $info = parent::offsetGet($index);
        } else {
            $info = parent::offsetGet($this->map[$index]);
        }
        return Field::newField($this->item, $info);
    }

    function offsetSet($index, $value)
    {
        if (is_int($index) && isset($this->reverse_map[$index])) {
            $index = $this->reverse_map[$index];
        }
        $this->item->setFieldValue($index, $value);
    }
}