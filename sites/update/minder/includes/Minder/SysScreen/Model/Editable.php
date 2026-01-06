<?php
abstract class Minder_SysScreen_Model_Editable extends Minder_SysScreen_Model implements Minder_SysScreen_Model_Editable_Interface
{
    public function __construct() {
        parent::__construct();
    }
    
    protected function getField($fieldId) {
        foreach ($this->fields as $fieldDesc) {
            if ($fieldId == $fieldDesc['RECORD_ID']) {
                return $fieldDesc;
            }
        }
        
        throw new Minder_SysScreen_Model_Editable_Exception('Column #' . $fieldId . ' does not exists in ' . get_class($this) . ' model.');
    }
    
    protected function validateField($dataField, $validateFor = 'update') {
        
        $fieldDesc = $this->getField($dataField['column_id']);
        
        switch ($validateFor) {
            case 'update' :
                if ($fieldDesc['SSV_INPUT_METHOD'] == 'RO') 
                    throw new Minder_SysScreen_Model_Editable_Exception('Collumn #' . $dataField['column_id'] . ' is not editable.');
                break;
            case 'create' :
                if ($fieldDesc['SSV_INPUT_METHOD_NEW'] == 'RO') 
                    throw new Minder_SysScreen_Model_Editable_Exception('Collumn #' . $dataField['column_id'] . ' is not editable.');
                break;
                
            default:
                throw new Minder_SysScreen_Model_Editable_Exception('Unknown validateFor: "' . $validateFor . '". Valid are: "update", "create".');
        }
                    
        if (empty($fieldDesc['SSV_NAME']))
            throw new Minder_SysScreen_Model_Editable_Exception('Collumn #' . $dataField['column_id'] . ' is not editable.');
        
        //todo: add validation for dropdowns
    }
    
    protected function validateRow($dataRow, $validateFor = 'update') {
        foreach ($dataRow as $dataField) {
            $this->validateField($dataField, $validateFor);
        }
    }
    
    protected function validateData($data, $validateFor = 'update') {
        foreach ($data as $dataRow) {
            $this->validateRow($dataRow, $validateFor);
        }
    }
    
    protected function updateRow($row, $table, $condition) {
        $tmpFieldSection = array();
        $tmpFieldArgs    = array();
        $tmpWhereSection = array();
        
        foreach ($row as $field) {
            $tmpFieldSection[] = $field['name'] . ' = ?';
            $tmpFieldArgs[]    = trim($field['value']);
        }
        
        foreach ($condition as $key => $val) {
            $tmpWhereSection[] = $key;
            $tmpFieldArgs      = array_merge($tmpFieldArgs, array_values($val));
        }
        
        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $tmpFieldSection) . ' WHERE ' . implode(' AND ', $tmpWhereSection);
        
//        array_unshift($tmpFieldArgs, $sql);
        
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $sql, $tmpFieldArgs));
        
        $minder = Minder::getInstance();
        
        if (false === ($result = $minder->execSQL($sql, $tmpFieldArgs))) 
            throw new Minder_SysScreen_Model_Editable_Exception('Error updating record: ' . $minder->lastError);
        
        return $result;
    }
    
    protected function createRow($row, $table) {
        $tmpInsertRow = array();
        
        foreach ($row as $field) {
            $tmpField                            = $this->getField($field['column_id']);
            $tmpInsertRow[$tmpField['SSV_NAME']] = trim($field['value']);
        }
        
        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', array_keys($tmpInsertRow)) . ') VALUES (' . substr(str_repeat('?, ', count($tmpInsertRow)), 0, -2) . ')';
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $sql, $tmpInsertRow));
        
        $minder = Minder::getInstance();
        if (false === ($result = $minder->execSQL($sql, array_values($tmpInsertRow)))) 
            throw new Minder_SysScreen_Model_Editable_Exception('Error creating record: ' . $minder->lastError);
        
        return $result;
    }
}

class Minder_SysScreen_Model_Editable_Exception extends Minder_SysScreen_Model_Exception {}