<?php

class Minder_Controller_Action_Helper_UomConverter extends Zend_Controller_Action_Helper_Abstract 
{
    protected $uoms   = array();
    
    public function direct($value, $fromUom, $toUom) {
        return $this->convert($value, $fromUom, $toUom);
    }
    
    public function convert($value, $fromUomCode, $toUomCode) {
        
        if (!is_numeric($value))
            throw new Minder_Controller_Action_Helper_UomConverter_Exception('Numeric value expected but "' . gettype($value) . '" given.');
            
        $uoms = $this->getUoms(array($fromUomCode, $toUomCode));
        
        if (!isset($uoms[$fromUomCode])) {
            throw new Minder_Controller_Action_Helper_UomConverter_Exception('No information found for "' . $fromUomCode . '" UOM.');
        }
            
        if (!isset($uoms[$toUomCode])) {
            throw new Minder_Controller_Action_Helper_UomConverter_Exception('No information found for "' . $toUomCode . '" UOM.');
        }
            
        $fromUom = $uoms[$fromUomCode];
        $toUom   = $uoms[$toUomCode];
        
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $fromUom, $toUom));
        
        return $value * $toUom['TO_STANDARD_CONV'] / $fromUom['TO_STANDARD_CONV'];
    }
    
    public function getUoms($codes) {
        if (!is_array($codes)) {
            $codes = array($codes);
        }
        
        $uomsToFetch     = array();
        $uomsDescription = array();
        
        foreach ($codes as $uomCode) {
            if (isset($this->uoms[$uomCode]))
                $uomsDescription[$uomCode] = $this->uoms[$uomCode];
            else 
                $uomsToFetch[$uomCode] = $uomCode;
        }
        
        if (count($uomsToFetch) > 0) {
            $filter['CODE IN (' . substr(str_repeat('?, ', count($uomsToFetch)), 0, -2) . ')'] = $uomsToFetch;
            
            $minder = Minder::getInstance();
            $tmpUoms = $minder->getUomsDescription($filter);
            
            $uomsDescription = array_merge($uomsDescription, $tmpUoms);
            $this->uoms      = array_merge($this->uoms, $tmpUoms);
        }
        
        return $uomsDescription;
    }
    
    public function getStandardUoms($types) {
        $filter = array('CODE IN (' . substr(str_repeat('?, ', count($types)), 0, -2) . ')' => array_values($types));
        
        $minder = Minder::getInstance();
        return $minder->getUomTypes($filter);
    }
    
}

class Minder_Controller_Action_Helper_UomConverter_Exception extends Minder_Exception {}