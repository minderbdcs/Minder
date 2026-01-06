<?php
  
class Minder_SequenceGenerator_RegisterBase_Abstract implements Minder_SequenceGenerator_RegisterBase_Interface {
    
    const NAN             = 'NAN';
    protected $map        = array();
    protected $base       = null;
    protected $nanValue   = '';
    
    public function __construct($nanValue = '') {
        $this->nanValue = $nanValue;
    }
    
    /**
    * @param integer $value
    * 
    * @returns string
    */
    public function fromBase10($value) {
        if ( $value == Minder_SequenceGenerator_RegisterBase_Abstract::NAN)
            return $this->nanValue;
            
        if (!isset($this->map[$value]))
            throw new Minder_SequenceGenerator_RegisterBase_Exception('Bad register value "' . $value . '".');
            
        return $this->map[$value];
    }
    
    /**
    * @param string $value
    * 
    * @returns integer
    */
    public function toBase10($value) {
        $flippedMap = array_flip($this->map);
        if (!isset($flippedMap[$value]))
            throw new Minder_SequenceGenerator_RegisterBase_Exception('Bad register value "' . $value . '".');
            
        return $flippedMap[$value];
    }
    
    /**
    * @returns integer
    */
    public function getBase() {
        return $this->base;
    }
    
} 
