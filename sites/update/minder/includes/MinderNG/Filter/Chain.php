<?php

namespace MinderNG\Filter;

class Chain implements FilterInterface {
    const FILTER_NAME = 'Chain';

    /**
     * @var FilterInterface[]|\Traversable
     */
    private $_filters;

    /**
     * @param \Traversable|FilterInterface[] $filters
     */
    function __construct($filters)
    {
        $this->_filters = $filters;
    }

    public static function newInstance(Factory $factory, $filters) {
        return new static(array_map(function($filter, $name) use($factory) {
            return ($filter instanceof FilterInterface) ? $filter : $factory->build($name, $filter);
        }, $filters, array_keys($filters)));
    }

    public function filter($value)
    {
        foreach($this->_filters as $filter) {
            $value = $filter->filter($value);
        }

        return $value;
    }
}