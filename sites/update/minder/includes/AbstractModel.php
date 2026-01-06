<?php

abstract class AbstractModel implements AbstractModel_Prototype_Interface {
    public function __construct($source = null) {
        if (is_array($source))
            $this->fillValuesFromTableRow($source);
    }

    public function __get($name) {
        $tmpKey = '_' . $name;

        if (property_exists($this, $tmpKey))
            return $this->$tmpKey;

        throw new Minder_Exception(get_class($this) . '::' . $name . ' does not exist.');
    }

    public function __set($name, $value) {
        $tmpKey = '_' . $name;

        if (property_exists($this, $tmpKey))
            return $this->$tmpKey = $value;

        throw new Minder_Exception(get_class($this) . '::' . $name . ' does not exist.');
    }

    public function fillValuesFromTableRow($valuesArray) {
        foreach ($valuesArray as $fieldName => $fieldValue) {
            $propertyName = transformToObjectProp($fieldName);
            $this->__set($propertyName, $fieldValue);
        }
    }

    /**
     * @abstract
     * @return boolean
     */
    abstract public function existedRecord();
}