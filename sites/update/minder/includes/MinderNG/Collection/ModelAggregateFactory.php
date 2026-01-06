<?php

namespace MinderNG\Collection;

class ModelAggregateFactory implements ModelAggregateFactoryInterface {
    private $_modelClassName;
    private $_reflectionClass;

    function __construct($modelClassName = '')
    {
        $this->_modelClassName = empty($modelClassName) ? (__NAMESPACE__ . '\\Model') : $modelClassName;
    }

    public function getIdAttribute()
    {
        $reflectionClass = $this->_getReflectionClass();

        return $reflectionClass->implementsInterface('\\MinderNG\\Collection\\IdAttributeProviderInterface')
            ? $reflectionClass->getMethod('getIdAttribute')->invoke(null)
            : ModelInterface::ID_ATTRIBUTE;
    }

    public function newInstance($aggregateData, $collection, $parse = false, $silent = false)
    {
        $reflectionClass = $this->_getReflectionClass();

        if (!$reflectionClass->implementsInterface('\\MinderNG\\Collection\\ModelAggregateInterface')) {
            throw new Exceptions\ShouldImplementModelAggregateInterface($this->_modelClassName);
        }

        /** @var ModelAggregateInterface $instance */
        $instance = $reflectionClass->newInstance();

//        if ($parse) {
//            $aggregateData = $instance->parse($aggregateData);
//        }

        $instance->getModel()->init($aggregateData, $parse, $silent, $collection);
        return $instance;
    }

    /**
     * @return \ReflectionClass
     */
    private function _getReflectionClass()
    {
        if (empty($this->_reflectionClass)) {
            $this->_reflectionClass = new \ReflectionClass($this->_modelClassName);
        }

        return $this->_reflectionClass;
    }

    public function calculateId(array $attributes = array())
    {
        $reflectionClass = $this->_getReflectionClass();

        return $reflectionClass->implementsInterface('\\MinderNG\\Collection\\IdAttributeProviderInterface')
            ? $reflectionClass->getMethod('calculateId')->invoke(null, $attributes)
            : (isset($attributes['id']) ? $attributes['id'] : null);
    }

    function __wakeup()
    {
        $this->_reflectionClass = null;
    }
}