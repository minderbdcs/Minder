<?php

class AustpostManifest_Db_Table_PickDespatch extends Zend_Db_Table_Abstract {
    protected $_name = 'PICK_DESPATCH';
    
    /**
    * Retrieve list of carriers, which were user at specified date
    * 
    * @param int $date - timestamp
    * 
    * @return 
    */
    public function getCarriersAndServicesUsedAtDate($date) {
        $dateParam = date('Y-m-d', $date);

        $select = $this->select()
                        ->from($this, array('PICKD_CARRIER_ID AS CARRIER_ID', 'PICKD_SERVICE_TYPE AS SERVICE_TYPE'))
                        ->where('PICKD_EXIT >= ZEROTIME(?)', $dateParam)
                        ->where('PICKD_EXIT <= MAXTIME(?)', $dateParam);
        
        return $this->fetchAll($select);
    }

    public function getCarriersAndServicesForManifest($carriersList, $manifestId) {
        $select = $this->select()
                        ->distinct()
                        ->from($this, array('PICKD_CARRIER_ID AS CARRIER_ID', 'PICKD_SERVICE_TYPE AS SERVICE_TYPE'));

        if (count($carriersList) > 0)
            $select->where("PICK_DESPATCH.PICKD_CARRIER_ID IN ('" . implode("', '", $carriersList) . "')");

        if (is_null($manifestId))
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);

        return $this->fetchAll($select);
    }
}