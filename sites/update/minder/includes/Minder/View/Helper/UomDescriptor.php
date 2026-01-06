<?php
  
class Minder_View_Helper_UomDescriptor extends Zend_View_Helper_Abstract
{
    protected $uoms       = array();
    protected $uomTypes   = array();
    protected $uomsByType = array();
    
    
    public function UomDescriptor($codes) {
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
    
    /**
    * Return UOM descriptions for given types grouped by types
    * 
    * @param array|string $uomTypes - UOM types to fetch
    * 
    * @return array - UOM descriptions
    */
    public function getUomDescriptionByType($uomTypes) {
        if (!is_array($uomTypes)) {
            $uomTypes = array($uomTypes);
        }
        
        $typesToFetch    = array();
        $uomsDescription = array();
        foreach ($uomTypes as $type) {
            if (isset($this->uomsByType[$type])) {
                $uomsDescription[$type] = $this->uomsByType[$type];
            } else {
                $typesToFetch[] = $type;
            }
        }
        
        if (count($typesToFetch) > 0) {
            $filter['UOM_TYPE IN (' . substr(str_repeat('?, ', count($typesToFetch)), 0, -2) . ')'] = $typesToFetch;
            $minder = Minder::getInstance();
            $tmpUoms = $minder->getUomsDescription($filter);
            
            foreach ($tmpUoms as $code => $description) {
                if (!isset($uomsDescription[$description['UOM_TYPE']]))
                    $uomsDescription[$description['UOM_TYPE']] = array();
                    
                if (!isset($uomsDescription[$description['UOM_TYPE']][$code]))
                    $uomsDescription[$description['UOM_TYPE']][$code] = $description;
            }
            
            $this->uomsByType = array_merge($this->uomsByType, $uomsDescription);
            $this->uoms       = array_merge($this->uoms, $tmpUoms);
        }
        
        return $uomsDescription;
    }
    
    public function getUomTypes($codes) {
        if (!is_array($codes)) {
            $codes = array($codes);
        }
        
        $typesToFetch = array();
        $uomTypes     = array();
        
        foreach ($codes as $typeCode) {
            if (isset($this->uomTypes[$typeCode]))
                $uomTypes[$typeCode] = $this->uomTypes[$typeCode];
            else 
                $typesToFetch[$typeCode] = $typeCode;
        }
        
        if (count($typesToFetch) > 0) {
            $filter['CODE IN (' . substr(str_repeat('?, ', count($typesToFetch)), 0, -2) . ')'] = $typesToFetch;
            
            $minder = Minder::getInstance();
            $tmpTypes = $minder->getUomTypes($filter);
            
            $uomTypes       = array_merge($uomTypes, $tmpTypes);
            $this->uomTypes = array_merge($this->uomTypes, $tmpTypes);
        }
        
        return $uomTypes;
    }
}