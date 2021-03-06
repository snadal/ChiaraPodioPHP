<?php
namespace Chiara\PodioItem\Diff\Fields;
use Chiara\PodioItem\Diff\Field, Chiara\PodioItem\Values\Collection;
abstract class CollectionField extends Field
{
    protected $added = false, $deleted, $itemclass;
    function getValue()
    {
        return new Collection($this, $this->info['to'], $this->itemclass);
    }

    function getOldValue()
    {
        return new Collection($this, $this->info['from'], $this->itemclass);
    }

    function getSaveValue()
    {
        $value = $this->getValue();
        $ret = array();
        foreach ($value as $v) {
            $ret[] = $v->id;
        }
        return $ret;
    }

    abstract protected function getId($value);

    protected function calculateChanges()
    {
        if (false !== $this->added) return;
        $this->added = $this->deleted = array();
        $tempa = array();
        foreach ($this->info['to'] as $i => $value) {
            $tempa[$this->getId($value)] = $i;
        }
        $tempd = array();
        foreach ($this->info['from'] as $i => $value) {
            $tempd[$this->getId($value)] = $i;
        }
        foreach ($tempd as $id => $i) {
            if (!isset($tempa[$id])) {
                $this->deleted[] = $this->info['from'][$i];
            }
        }
        foreach ($tempa as $id => $i) {
            if (!isset($tempd[$id])) {
                $this->added[] = $this->info['to'][$i];
            }
        }
    }

    function __get($var)
    {
        if ($var == 'referenceable_types') return $this->to['info']['config']['settings']['referenceable_types'];
        if ($var == 'deleted') {
            $this->calculateChanges();
            return new Collection($this, $this->deleted, $this->itemclass);
        }
        if ($var == 'added') {
            $this->calculateChanges();
            return new Collection($this, $this->added, $this->itemclass);
        }
        return parent::__get($var);
    }
}