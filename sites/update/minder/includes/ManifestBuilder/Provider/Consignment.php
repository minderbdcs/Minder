<?php

class ManifestBuilder_Provider_Consignment {
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    /**
     * @param $carrierId
     * @param $serviceType
     * @param $manifestId
     * @return ManifestBuilder_Model_Consignment[]
     */
    public function getConsignments(ManifestBuilder_AustPost_Model_CarrierService $carrierService, $manifestId) {
        $select = new Zend_Db_Select($this->getDbAdapter());

        $select->distinct()
            ->from('PICK_DESPATCH', array('PICK_DESPATCH.DESPATCH_ID', 'AWB_CONSIGNMENT_NO', 'CREATE_DATE', 'SENDER_ACCOUNT', 'RECEIVER_ACCOUNT', 'PICKD_CHARGE_TO', 'PICKD_PALLET_QTY', 'PICKD_CARTON_QTY', 'PICKD_SATCHEL_QTY', 'PICKD_WT_CALC', 'PICKD_WT_ACTUAL', 'PICKD_VOL_ACTUAL'))
            ->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_CHARGE_CODES', 'SERVICE_SIGNATURE_REQD', 'SERVICE_PARTIAL_DELIVERY'))
            ->joinLeft('CARRIER', 'CARRIER_SERVICE.CARRIER_ID = CARRIER.CARRIER_ID', array())
            ->where('PICK_DESPATCH.PICKD_CARRIER_ID = ?', $carrierService->CARRIER_ID)
            ->where('CARRIER_SERVICE.SERVICE_ACCOUNT = ? OR (CARRIER_SERVICE.SERVICE_ACCOUNT IS NULL AND CARRIER.ACCOUNT = ?)', $carrierService->ACCOUNT)
            ->where('PICK_DESPATCH.PICKD_SERVICE_TYPE = ?', $carrierService->SERVICE_TYPE);

        if (is_null($manifestId))
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);

        $result = array();

        foreach ($select->query()->fetchAll() as $resultRow) {
            $resultRow = array_merge($resultRow, $this->_getPickOrder($resultRow['DESPATCH_ID']));
            $resultRow['ACCOUNT'] = $carrierService->ACCOUNT;
            $result[] = new ManifestBuilder_Model_Consignment($resultRow);
        }

        return $result;
    }

    protected function _getPickOrder($despatchId) {
        $select = new Zend_Db_Select($this->getDbAdapter());

        $select->limit(1)
            ->from('PICK_ITEM_DETAIL', 'PICK_LABEL_NO')
            ->join('PICK_ORDER', 'PICK_ITEM_DETAIL.PICK_ORDER = PICK_ORDER.PICK_ORDER', array('PICK_ORDER.PICK_ORDER', 'PO_OTHER1' => 'PICK_ORDER.OTHER1'))
            ->where('PICK_ITEM_DETAIL.DESPATCH_ID = ?', $despatchId);

        $result = $select->query()->fetchAll();

        return count($result) > 0 ? current($result) : array('PICK_LABEL_NO' => '', 'PO_OTHER1' => '', 'PICK_ORDER' => 'PICK_ORDER');


    }

    /**
     * @param ManifestBuilder_Model_Consignment $consignment
     * @return ManifestBuilder_Model_DeliveryAddress
     */
    public function getDeliveryAddress(ManifestBuilder_Model_Consignment $consignment) {
        $select = new Zend_Db_Select($this->getDbAdapter());
        $select->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('PICK_ITEM', 'PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO', array())
            ->joinLeft('PICK_ORDER', 'PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER')
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $consignment->AWB_CONSIGNMENT_NO)
            ->limit(1);

        if (is_array($fetchedRow = $select->query()->fetch())) {
            $result = new ManifestBuilder_Model_DeliveryAddress($fetchedRow);
        } else {
            $result = new ManifestBuilder_Model_DeliveryAddress(array());
        }

        return $result;
    }

    public function containsDangerousGoods(ManifestBuilder_Model_Consignment $consignment) {

        $select = new Zend_Db_Select($this->getDbAdapter());
        $select->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', array())
            ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID', array('PROD_ID'))
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $consignment->AWB_CONSIGNMENT_NO)
            ->where('PROD_PROFILE.PP_HAZARD_STATUS IS NOT NULL AND PROD_PROFILE.PP_HAZARD_STATUS <> ?', '')
            ->limit(1);

        if (count($select->query()->fetchAll()) > 0)
            return true;

        return false;
    }

    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    /**
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     * @return ManifestBuilder_Provider_Consignment
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
        return $this;
    }
}