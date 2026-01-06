<?php

namespace MinderNG\Filter;

class Factory {

    /**
     * @param string $name
     * @param mixed $initVector
     * @return FilterInterface
     */
    public function build($name, $initVector = null) {
        $initVector = (array)$initVector;

        $name = $this->_formatName($name);

        if ($name == Chain::FILTER_NAME) {
            return Chain::newInstance($this, $initVector);
        }

        $class = new \ReflectionClass(__NAMESPACE__ . "\\" . $name);
        return $class->newInstanceArgs($initVector);
    }

    /**
     * @param $filters
     * @return Chain
     */
    public function buildFilterChain($filters) {
        return Chain::newInstance($this, (array)$filters);
    }

    /**
     * @param Field[] $fieldsFilter
     * @return FieldSet
     */
    public function fieldSet(array $fieldsFilter) {
        return new FieldSet($fieldsFilter);
    }

    private function _formatName($name) {
        return implode('', array_map('ucfirst', explode('_', strtolower(trim($name)))));
    }
}