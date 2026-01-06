<?php

class ManifestBuilder_Provider_Carrier {
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;


    public function getCarrier($carrierId) {
        $select = new Zend_Db_Select($this->getDbAdapter());

        $select
            ->from('CARRIER')
            ->where('CARRIER_ID = ?', $carrierId);

        return new ManifestBuilder_Model_Carrier($select->query()->fetch());
    }

    public function getNextManifestNumber($carrierId) {
        $db = $this->getDbAdapter();
        $carrierData = $db->fetchRow("SELECT FTP_MANIFEST_GENERATOR, FTP_MANIFEST_START_NO, FTP_MANIFEST_END_NO, FTP_MANIFEST_PREFIX FROM CARRIER WHERE CARRIER_ID = ?", array($carrierId));
        $generator = $carrierData['FTP_MANIFEST_GENERATOR'];

        if (empty($generator))
            throw new Exception('Manifest generator is not defined for CARRIER "' . $carrierId . '"');

        //$nextManifestNumber = $db->fetchOne('SELECT GEN_ID(' . $generator . ', 1 ) FROM RDB$DATABASE');

        $sql            = 'SELECT GEN_ID(' . $generator  . ', 1 ) FROM RDB$DATABASE;';
        $nextManifestNumber = $db->fetchOne($sql);
        if (empty($carrierData['FTP_MANIFEST_START_NO']))
            $carrierData['FTP_MANIFEST_START_NO'] = 0;
        if (empty($carrierData['FTP_MANIFEST_END_NO']))
            $carrierData['FTP_MANIFEST_END_NO'] = 9999999;
        $wkDiff = 0;
        if ($carrierData['FTP_MANIFEST_START_NO'] > $nextManifestNumber)
            $wkDiff += $carrierData['FTP_MANIFEST_START_NO'] ;
        if ($carrierData['FTP_MANIFEST_END_NO'] < $nextManifestNumber )
            $wkDiff -= $carrierData['FTP_MANIFEST_END_NO'] ;
        if ($wkDiff != 0) {
            $sql            = 'SELECT GEN_ID(' . $generator . ', ' . $wkDiff .' ) FROM RDB$DATABASE;';
            $nextManifestNumber = $db->fetchOne($sql);
        }
        if (strlen($nextManifestNumber) < 7)
            $nextManifestNumber = str_repeat('0', 7 - strlen($nextManifestNumber)) . $nextManifestNumber;
        if (!empty($carrierData['FTP_MANIFEST_PREFIX']))
            $nextManifestNumber = $carrierData['FTP_MANIFEST_PREFIX'] . $nextManifestNumber;

        return $nextManifestNumber;
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
     * @return \ManifestBuilder_Provider_CarrierService
     */
    public function setDbAdapter($dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
        return $this;
    }

    /**
     * @param $carriersList
     * @param $manifestId
     * @return ManifestBuilder_Model_CarrierOutputFormat[]
     */
    public function getCarrierOutputFormats($carriersList, $manifestId) {
        $select = $this->getDbAdapter()->select();

        $select->
            from('PICK_DESPATCH', array())->
            join('CARRIER', 'PICK_DESPATCH.PICKD_CARRIER_ID = CARRIER.CARRIER_ID', array('CARRIER_ID' => 'CARRIER.CARRIER_ID', 'CONNOTE_EXPORT_METHOD' => 'CARRIER.CONNOTE_EXPORT_METHOD'))->
            distinct();

        if (count($carriersList) > 0) {
            $select->where("PICK_DESPATCH.PICKD_CARRIER_ID IN ('" . implode("', '", $carriersList) . "')");
        }

        if (is_null($manifestId))
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);

        $result = array();

        foreach ($select->query()->fetchAll() as $resultRow) {
            $result[] = new ManifestBuilder_Model_CarrierOutputFormat($resultRow);
        }

        return $result;
    }
}