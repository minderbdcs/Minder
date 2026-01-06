<?php

namespace MinderNG\Collection\Helper;

class Helper {

    /**
     * @param \Traversable $collection
     * @param $callback
     * @return CallbackMapIterator
     */
    public function map($collection, $callback) {
        return new CallbackMapIterator(
            $collection,
            $callback
        );
    }

    /**
     * @param \Traversable $collection
     * @param $attribute
     * @return \Iterator
     */
    public function pluck(\Traversable $collection, $attribute) {
        return $this->map(
            $collection,
            function($value)use($attribute){return $value[$attribute];}
        );
    }

    /**
     * @param \Traversable $collection
     * @param $callback
     * @return \Iterator
     */
    public function filter(\Traversable $collection, $callback) {
        return new CallbackFilterIterator(
            new \IteratorIterator($collection),
            $callback
        );
    }

    /**
     * @param \Traversable $collection
     * @param array $properties
     * @param bool|true $strict
     * @return \Iterator
     */
    public function where(\Traversable $collection, array $properties, $strict = true) {
        return new CallbackFilterIterator(
            new \IteratorIterator($collection),
            function($currentObject)use($properties, $strict){
                foreach($properties as $key => $value) {
                    if (!isset($currentObject[$key])) {return false;}
                    if (is_callable($value)) {
                        return call_user_func($value, $currentObject[$key], $key, $currentObject, $strict);
                    } else {
                        if ($strict) {
                            if ($currentObject[$key] !== $value) {return false;}
                        } else {
                            if ($currentObject[$key] != $value) {return false;}
                        }
                    }

                }
                return true;
            }
        );
    }

    /**
     * @param \Traversable $collection
     * @param array $properties
     * @param bool|true $strict
     * @return mixed|null
     */
    public function findWhere(\Traversable $collection, array $properties, $strict = true) {
        foreach($this->where($collection, $properties, $strict) as $item) {
            return $item;
        }

        return null;
    }
}