<?php

class AustpostManifest_Db_Table_Control extends Zend_Db_Table_Abstract implements ArrayAccess {
    protected $_name     = 'CONTROL';
    protected $_values   = null;
    
    protected static $_instance = null;
    protected static $_fromInit = false;
    
    public function __construct($config = array()) {
        if (!$this->_fromInit)
            throw new AustpostManifest_Db_Table_Control_Singletone_Exception('Should be only one instance of Singletone. Use ' . __CLASS__. '::getInstance() instead.');
        
        parent::__construct($config);
    }
    
    public static function getInstance($config = array()) {
        if (is_null(self::$_instance)) {
            self::$_fromInit = true;
            self::$_instance = new AustpostManifest_Db_Table_Control($config);
            self::$_fromInit = false;
        }
        
        return self::$_instance;
    }
    
    protected function _fillValues() {
        if (!is_null($this->_values))
            return;
        
        
        $this->_values = $this->getAdapter()->fetchRow($this->select(Zend_Db_Table_Abstract::SELECT_WITH_FROM_PART));
    }
    
    public function __get($key) {
        
        switch ($key) {
            case '_name':
            case '_values':
            case '_instance':
            case '_fromInit':
                return self::$$key;
                break;
            default:
                $this->_fillValues();
                if (isset($this->_values[$key]))
                    return $this->_values[$key];
        }
        
        throw new Exception('Unknown Control "' . $key . '"');
    }
    
    public function offsetSet($offset, $value) {
        throw new Exception('Readonly access allowed.');
    }
    public function offsetExists($offset) {
        $this->_fillValues();
        return isset($this->_values[$offset]);
    }
    public function offsetUnset($offset) {
        throw new Exception('Readonly access allowed.');
    }
    public function offsetGet($offset) {
        $this->_fillValues();
        if (isset($this->_values[$offset]))
            return $this->_values[$offset];

        throw new Exception('Unknown Control "' . $offset . '"');
    }    
}

class AustpostManifest_Db_Table_Control_Exception extends AustpostManifest_Exception {}

class AustpostManifest_Db_Table_Control_Singletone_Exception extends AustpostManifest_Db_Table_Control_Exception {}