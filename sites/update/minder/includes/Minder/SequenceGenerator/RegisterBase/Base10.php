<?php

class Minder_SequenceGenerator_RegisterBase_Base10 extends Minder_SequenceGenerator_RegisterBase_Abstract {

    protected $base = 10;
    
    public function __construct($nanValue = '0') {
        parent::__construct($nanValue);
    }
    
    /**
    * Redefine standard behaviour. As for Base 10 there is no need in Map.
    * So for performance just convert input value to string. 
    * 
    * @param integer $value
    * 
    * @returns string
    */
    public function fromBase10($value) {
        if ( $value == Minder_SequenceGenerator_RegisterBase_Abstract::NAN)
            return $this->nanValue;
            
        return strval($value);
    }
    
    /**
    * Redefine standard behaviour. As for Base 10 there is no need in Map.
    * So for performance just convert input value to integer and check (0..9) constraint. 
    * 
    * @param string $value
    * 
    * @returns integer
    */
    public function toBase10($value) {
        if (!is_numeric($value))
            throw new Minder_SequenceGenerator_RegisterBase_Exception('Bad register value "' . $value . '".');
        
        $value = intval($value);
        
        if (($value > 9) || ($value < 0))
            throw new Minder_SequenceGenerator_RegisterBase_Exception('Bad register value "' . $value . '".');
            
        return $value;
    }
}
