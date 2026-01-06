<?php

class ManifestBuilder_AustPost_Provider_CarrierService implements ManifestBuilder_DbAdapterAwareInterface {
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    public $_dbAdapter;

    /**
     * @param $carriersList
     * @param $manifestId
     * @return ManifestBuilder_AustPost_Model_CarrierService[]
     */
    public function getCarrierServiceListFromDespatch(array $carriersList, $manifestId) {
        $select = $this->getDbAdapter()->select();

        $select->from('PICK_DESPATCH', array('CARRIER_ID' => 'PICKD_CARRIER_ID', 'SERVICE_TYPE' => 'PICKD_SERVICE_TYPE'))->distinct();
        $select->joinLeft('CARRIER', 'PICK_DESPATCH.PICKD_CARRIER_ID = CARRIER.CARRIER_ID', array());
        $select->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array(
            'ACCOUNT' => 'COALESCE(CARRIER_SERVICE.SERVICE_ACCOUNT, CARRIER.ACCOUNT)',
            'SERVICE_CHARGE_CODES'
        ));

        if (count($carriersList) > 0) {
            $select->where("PICK_DESPATCH.PICKD_CARRIER_ID IN ('" . implode("', '", $carriersList) . "')");
        }

        if (is_null($manifestId))
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);

        $result = array();

        foreach ($select->query()->fetchAll() as $resultRow) {
            $result[] = new ManifestBuilder_AustPost_Model_CarrierService($resultRow);
        }

        return $result;
    }

    public function getNextManifestNumber(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation) {
        $db = $this->getDbAdapter();
        $generator = $merchantLocation->getCarrier()->FTP_MANIFEST_GENERATOR;

        if (empty($generator))
            throw new Exception('Manifest generator is not defined for CARRIER "' . $merchantLocation->getCarrier()->CARRIER_ID . '"');

        $sql            = 'SELECT GEN_ID(' . $generator  . ', 1 ) FROM RDB$DATABASE;';
        $nextManifestNumber = $db->fetchOne($sql);
        $startNo = $merchantLocation->getCarrier()->FTP_MANIFEST_START_NO;
        if (empty($startNo))
            $startNo = 0;
        $endNo = $merchantLocation->getCarrier()->FTP_MANIFEST_END_NO;
        if (empty($endNo))
            $endNo = 9999999;
        $wkDiff = 0;
        if ($startNo > $nextManifestNumber)
            $wkDiff += $startNo;
        if ($endNo < $nextManifestNumber )
            $wkDiff -= $endNo;
        if ($wkDiff != 0) {
            $sql            = 'SELECT GEN_ID(' . $generator . ', ' . $wkDiff .' ) FROM RDB$DATABASE;';
            $nextManifestNumber = $db->fetchOne($sql);
        }
        if (strlen($nextManifestNumber) < 7)
            $nextManifestNumber = str_pad($nextManifestNumber, 7, '0', STR_PAD_LEFT);
        $prefix = $merchantLocation->getCarrier()->FTP_MANIFEST_PREFIX;
        if (!empty($prefix))
            $nextManifestNumber = $prefix . "-" . $nextManifestNumber;

        return $nextManifestNumber;
    }

    public function getManifestHeader(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation, ManifestBuilder_Date $currentDate) {
        $result = new ManifestBuilder_AustPost_Model_ManifestHeader();

        $result->transactionDateTime = $currentDate->toAustPostDateTime();
        $result->transactionId = $this->_getNextTransactionId($merchantLocation);

        return $result;
    }

    public function getPcmsConsignment($manifestId, $locationId, ManifestBuilder_Date $currentDate) {
        $result = new ManifestBuilder_AustPost_Model_PcmsManifest();
        $result->manifestNumber = $manifestId;
        $result->merchantLocationId = $locationId;
        $result->dateLodged = $result->dateSubmitted = $currentDate->toAustPostDateTime();

        return $result;
    }

    protected function _getNextTransactionId(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation) {
        $generator = $merchantLocation->getCarrier()->FTP_ID_GENERATOR;

        if (empty($generator))
            throw new Exception('FTP_ID_GENERATOR generator is not defined for CARRIER "' . $merchantLocation->getCarrier()->CARRIER_ID . '"');

        return $this->getDbAdapter()->fetchOne('SELECT GEN_ID(' . $generator . ', 1 ) FROM RDB$DATABASE');
    }

    /**
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     * @return ManifestBuilder_DbAdapterAwareInterface
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
        return $this;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }
}