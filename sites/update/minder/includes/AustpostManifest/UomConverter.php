<?php

class AustpostManifest_UomConverter {
    
    protected function getUomToStandardConv($uom) {
        $uomRegistry = array();
        
        if (Zend_Registry::isRegistered('uom_registry'))
            $uomRegistry = Zend_Registry::get('uom_registry');
        
        if (isset($uomRegistry[$uom])) {
            return $uomRegistry[$uom];
        }
        
        $db        = AustpostManifest::getInstance()->getDb();
        $uomSelect = new Zend_Db_Select($db);
        $uomSelect->from('UOM', array('TO_STANDARD_CONV'))
                    ->where('CODE = ?', $uom);
        
        $uomRegistry[$uom] = $db->fetchOne($uomSelect);
        Zend_Registry::set('uom_registry', $uomRegistry);
        
        return $uomRegistry[$uom];
    }
    
    public function convert($fromUom, $toUom, $value) {
        $fromToStdConv = $this->getUomToStandardConv($fromUom);
        if ($fromToStdConv == null)
            throw new AustpostManifest_UomConverter_Exception('UOMs "' . $fromUom . '" TO_STANDARD_CONF is undefined', AustpostManifest_UomConverter_Exception::FROM_UNDEFINED);
        
        $toToStdConv   = $this->getUomToStandardConv($toUom);
        if ($toToStdConv == null)
            throw new AustpostManifest_UomConverter_Exception('UOMs "' . $toUom . '" TO_STANDARD_CONF is undefined', AustpostManifest_UomConverter_Exception::TO_UNDEFINED);
        
        return $value * $toToStdConv / $fromToStdConv;
    }
}

class AustpostManifest_UomConverter_Exception extends AustpostManifest_Exception {
    const FROM_UNDEFINED = 1;
    const TO_UNDEFINED   = 2;
}