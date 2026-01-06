<?php

/**
 * @property string $depotIdPropertyName
 */
class PostcodeDepot_Carriers_Adapter extends PostcodeDepot_Adapter_Array {
    protected $_fieldsMap = array(
        'postcode' => 'postCode',
        'depot_id' => 'depot_nn'
    );

    protected $_depotIdPropertyName = '';

    public function __construct($depotIdPropertyName, $fields = array())
    {
        $this->_depotIdPropertyName = $depotIdPropertyName;
        parent::__construct($fields);
    }

    public function __set($name, $value) {
        if ($name == 'depotIdPropertyName') $this->_depotIdPropertyName = strval($value);
    }

    public function __get($name) {
        return ($name == 'depotIdPropertyName') ? $this->_depotIdPropertyName : null;
    }

    /**
     * @param mixed $source
     * @return PostcodeDepot
     */
    public function convert($source)
    {
        $result = new PostcodeDepot();
        foreach ($this->_fields as $fieldId => $fieldName) {
            $fieldValue = $source[$fieldId];
            switch (strtolower($fieldName)) {
                case 'postcode':
                    $result->postCode = trim($fieldValue);
                    break;
                case 'depot_id':
                    $tmpPropertyName = $this->_depotIdPropertyName;
                    $result->$tmpPropertyName = trim($fieldValue);
                    break;
            }
        }

        return $result;
    }

}