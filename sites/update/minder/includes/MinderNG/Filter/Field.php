<?php

namespace MinderNG\Filter;

class Field implements FilterInterface {
    private $name;
    /**
     * @var FilterInterface
     */
    private $filter;

    function __construct($name, FilterInterface $filter)
    {
        $this->name = $name;
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    public function filter($value)
    {
        return $this->filter->filter($value[$this->getName()]);
    }
}