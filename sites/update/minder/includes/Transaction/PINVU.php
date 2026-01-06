<?php
/**
 * Transaction_PINVU
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 *
 */

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';



class Transaction_PINVU Extends Transaction_PINV
{
    public $invoiceNo       =   ''; //INVOICE_NO to update

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'PINV';
        $this->transClass = 'U';
    }
    
    public function fillFieldsMap() {
        $this->_fieldsMap = array();
        
        
        $minder = Minder::getInstance();
        
        $sql = 'SELECT CODE, DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = ? AND CODE LIKE ?';
        $options = $minder->fetchAllAssoc($sql, 'REF_CODE', 'PINV_%');
        
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $optionsRow) {
                $tmpArr = explode('.', $optionsRow['DESCRIPTION']);
                
                $fieldDescription = new stdClass();
                $fieldDescription->tableName = '';
                $fieldDescription->fieldName = '';
                
                if (isset($tmpArr[1])) {
                    $fieldDescription->tableName = strtoupper($tmpArr[0]);
                    $fieldDescription->fieldName = strtoupper($tmpArr[1]);
                } else {
                    $fieldDescription->tableName = '';
                    $fieldDescription->fieldName = strtoupper($tmpArr[0]);
                }
                
                $this->_fieldsMap[strtoupper($optionsRow['CODE'])] = $fieldDescription;
            }
        }
    }
    
    public function getReferenceFields() {
        return array_map(create_function('$item', 'return $item->tableName . "." . $item->fieldName;'), $this->_fieldsMap);
    }
    
    protected function findCodeByDescription($fieldName, $tableName) {
        foreach ($this->_fieldsMap as $code => $fieldDescription) {
            if ($fieldDescription->tableName == $tableName && $fieldDescription->fieldName == $fieldName)
                return $code;
        }
        
        return null;
    }
    
    public function setField($fieldName, $tableName, $value) {
        $tableName = (empty($tableName)) ? '' : $tableName;
        $code = $this->findCodeByDescription($fieldName, $tableName);
        
        if (is_null($code))
            throw new Exception('Cannot update ' . $tableName . '.' . $fieldName . '. Check REF_CODE settings for PICK_INVOICE table.');
            
        $this->__set($code, $value);
    }
    
    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the PINVC transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        return $this->invoiceNo;
    }
    
}
