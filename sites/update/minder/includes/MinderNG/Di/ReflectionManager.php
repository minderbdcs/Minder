<?php

namespace MinderNG\Di;

class ReflectionManager {
    /**
     * @var array
     */
    private $_data;

    function __construct(array $data = array())
    {
        $this->_data = $data;
    }

    private function _newInstance($className, $params) {
        $reflect = new \ReflectionClass($className);
        return (count($params) > 0) ? $reflect->newInstanceArgs($params) : new $className;
    }

    public function newInstance($className, $params) {
        $this->_data[$className] = isset($this->_data[$className]) ? $this->_data[$className] : array('methods' => array());


        return isset($this->_data[$className]['factory']) ?
            call_user_func_array($this->_data[$className]['factory'], $params) :
            $this->_newInstance($className, $params);
    }

    public static function _reflectionParameterToArray(\ReflectionParameter $source) {
        return array(
            'className' => $source->getClass() ? $source->getClass()->name : false,
            'defaultValueAvailable' => $source->isDefaultValueAvailable(),
            'defaultValue' => $source->isDefaultValueAvailable() ? $source->getDefaultValue() : null,
        );
    }

    private function _fetchReflectionMethod($className, $method) {
        $reflect = new \ReflectionMethod($className, $method);
        return array_map(
            function(\ReflectionParameter $parameter) {return ReflectionManager::_reflectionParameterToArray($parameter);},
            $reflect->getParameters()
        );
    }

    /**
     * @param $className
     * @param $method
     * @return array[]
     */
    public function reflectionMethod($className, $method) {
        $this->_data[$className] = isset($this->_data[$className]) ? $this->_data[$className] : array('methods' => array());

        if (!isset($this->_data[$className]['methods'][$method])) {
            $this->_data[$className]['methods'][$method] = $this->_fetchReflectionMethod($className, $method);
        }

        return $this->_data[$className]['methods'][$method];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}