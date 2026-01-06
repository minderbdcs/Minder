<?php
  
class Minder_SysScreen_Model_PackDimensions extends Minder_SysScreen_Model
{
    protected $data      = array();
    protected $namespace = '';
    
    public function __construct($namespace) {
        parent::__construct();
        
        $this->namespace = trim($namespace);
        if (empty($this->namespace))
            throw new Minder_SysScreen_Model_PackDimensions_Exception('Namespace for PackDimensions model could not be null.');
        
        $session = new Zend_Session_Namespace('pack_dimensions_data');
        $this->data = $session->data[$this->namespace];
    }
    
    public function count() {
        return count($this->data);
    }
    
    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false) {
        return array_slice($this->data, $rowOffset, $itemCountPerPage, true);
    }
}

class Minder_SysScreen_Model_PackDimensions_Exception extends Minder_SysScreen_Model_Exception {}