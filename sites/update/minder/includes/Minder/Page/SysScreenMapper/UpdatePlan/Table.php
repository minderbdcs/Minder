<?php

class Minder_Page_SysScreenMapper_UpdatePlan_Table {
    public $name   = null;
    public $pKeys  = array();
    public $fields = array();

    function __construct($name = null) {
        $this->setName($name);
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function merge(Minder_Page_SysScreenMapper_UpdatePlan_Table $table) {
        if ($table->getName() != $this->getName())
            throw new Exception('Cannot merge different tables.');

        $this->pKeys  = array_merge($this->pKeys, $table->pKeys);
        $this->fields = array_merge($this->fields, $table->fields);

        return $this;
    }

    public function setKey($keyName, $keyValue) {
        if (array_key_exists($keyName, $this->pKeys))
            $this->pKeys[$keyName] = $keyValue;

        return $this;
    }

    /**
     * @param Minder_Db_SysScreenTable $dbTable
     * @return array
     */
    public function getEmptyKeys($dbTable) {
        $result = array();

        foreach ($this->pKeys as $fieldName => $fieldValue) {
            if (is_null($fieldValue))
                $result[] = $dbTable->formatRealFieldIndex($this->getName(), $fieldName);
        }

        return $result;
    }

    public function setField($fieldName, $fieldValue) {
        if (array_key_exists($fieldName, $this->fields))
            $this->fields[$fieldName] = $fieldValue;

        return $this;
    }
}