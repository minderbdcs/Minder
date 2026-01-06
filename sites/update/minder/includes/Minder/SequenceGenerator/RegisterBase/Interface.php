<?php

interface Minder_SequenceGenerator_RegisterBase_Interface {
    
    /**
    * @param integer $value
    * 
    * @returns string
    */
    public function fromBase10($value);
    
    /**
    * @param string $value
    * 
    * @returns integer
    */
    public function toBase10($value);
    
    /**
    * @returns integer
    */
    public function getBase();
}
