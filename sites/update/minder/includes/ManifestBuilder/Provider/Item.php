<?php

class ManifestBuilder_Provider_Item {
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    /**
     * @param ManifestBuilder_Model_Consignment $consignment
     * @return array
     */
    public function getConsignmentItems(ManifestBuilder_Model_Consignment $consignment) {
        $select = new Zend_Db_Select($this->getDbAdapter());
        $select
            ->from('PACK_ID', array('PACK_TYPE', 'PACK_ID', 'DESPATCH_LABEL_NO', 'DIMENSION_X', 'DIMENSION_Y', 'DIMENSION_Z', 'PACK_WEIGHT', 'DIMENSION_UOM', 'PACK_WEIGHT_UOM', 'PACK_SERIAL_NO', 'PACK_SEQUENCE_NO', 'PACK_LAST_SEQUENCE_INDICATOR'))
            ->join('PICK_DESPATCH', 'PACK_ID.DESPATCH_ID = PICK_DESPATCH.DESPATCH_ID', array())
            ->join('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_TRANSIT_COVER_REQD', 'SERVICE_TRANSIT_COVER_AMOUNT', 'SERVICE_LOCATION_ID', 'SERVICE_SERVICE_CODE'))
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $consignment->AWB_CONSIGNMENT_NO);

        $result = array();

        foreach ($select->query()->fetchAll() as $resultRow) {
            $result[] = new ManifestBuilder_Model_Item($resultRow);
        }

        return $result;
    }

    /**
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    /**
     * @param \Zend_Db_Adapter_Abstract $dbAdapter
     * @return \ManifestBuilder_Provider_Item
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
        return $this;
    }
}