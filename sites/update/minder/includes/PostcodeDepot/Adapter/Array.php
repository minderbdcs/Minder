<?php


/**
 * Convert array to PostcodeDepot object
 */
abstract class PostcodeDepot_Adapter_Array implements PostcodeDepot_Adapter_Interface {
    protected $_fieldsMap = array();
    protected $_fields    = array();

    /**
     * @param array $fields
     */
    public function __construct($fields = array()) {
        $this->setFieldsOrder($fields);
    }

    /**
     * @throws PostcodeDepot_Adapter_Array_BadFieldException
     * @param array $fields
     * @return void
     */
    public function setFieldsOrder($fields) {
        foreach ($fields as $fieldName) {
            $tmpName = strtolower($fieldName);
            if (!isset($this->_fieldsMap[$tmpName]))
                throw new PostcodeDepot_Adapter_Array_BadFieldException('Unsupported field "' . $fieldName . '".');

            $this->_fields[] = $tmpName;
        }
    }
}