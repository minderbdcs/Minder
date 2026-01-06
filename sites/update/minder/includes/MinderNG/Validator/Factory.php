<?php

namespace MinderNG\Validator;

class Factory {
    public function build($name, $initVector) {
        $initVector = (array)$initVector;

        $name = $this->_formatName($name);

        if ($name == Chain::CHAIN) { return $this->chain($initVector); }
        if ($name == Chain::SET) { return $this->set($initVector); }

        $class = new \ReflectionClass(__NAMESPACE__ . "\\" . $name);
        return $class->newInstanceArgs($initVector);
    }

    public function chain($initVector) {
        return Chain::instance($this, $initVector, true);
    }

    /**
     * @param $initVector
     * @return ValidatorInterface
     */
    public function set($initVector) {
        return Chain::instance($this, $initVector, false);
    }

    /**
     * @param Field[] $fields
     * @return FieldSet
     */
    public function fieldSet($fields) {
        return new FieldSet($fields);
    }

    private function _formatName($name) {
        return implode('', array_map('ucfirst', explode('_', strtolower(trim($name)))));
    }
}