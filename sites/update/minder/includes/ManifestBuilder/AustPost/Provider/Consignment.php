<?php

class ManifestBuilder_AustPost_Provider_Consignment implements ManifestBuilder_DbAdapterAwareInterface {
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    /**
     * @param ManifestBuilder_AustPost_Model_CarrierService $merchantLocation
     * @param $manifestId
     * @param ManifestBuilder_Date $createDateTime
     * @return ManifestBuilder_AustPost_Model_Consignment[]
     */
    public function getConsignments(ManifestBuilder_AustPost_Model_MerchantLocation $merchantLocation, $manifestId, ManifestBuilder_Date $createDateTime) {
        $carrierServices = Minder_ArrayUtils::mapField($merchantLocation->getCarrierServices()->toArray(), 'RECORD_ID');
        $services = $this->getDbAdapter()->quote($carrierServices);


        $select = $this->getDbAdapter()->select();
/*
        $select->distinct()
            ->from('PICK_DESPATCH', array('AWB_CONSIGNMENT_NO'))
            ->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_CHARGE_CODES', 'SERVICE_SIGNATURE_REQD', 'SERVICE_PARTIAL_DELIVERY'))
            ->joinLeft('CARRIER', 'CARRIER_SERVICE.CARRIER_ID = CARRIER.CARRIER_ID', array(
                'ACCOUNT' => 'COALESCE(CARRIER_SERVICE.SERVICE_ACCOUNT, CARRIER.ACCOUNT)'
            ))
            ->where('PICK_DESPATCH.PICKD_SERVICE_RECORD_ID IN (' . $this->getDbAdapter()->quote($carrierServices) . ')')
            ->where("PICK_DESPATCH.DESPATCH_STATUS IN ('DC', 'DX')");
*/
        $select->distinct()
            ->from('PICK_DESPATCH', array('AWB_CONSIGNMENT_NO'))
            ->joinLeft('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_CHARGE_CODES', 'SERVICE_SIGNATURE_REQD', 'SERVICE_PARTIAL_DELIVERY'))
            ->joinLeft('CARRIER', 'CARRIER_SERVICE.CARRIER_ID = CARRIER.CARRIER_ID', array(
                'ACCOUNT' => 'COALESCE(CARRIER_SERVICE.SERVICE_ACCOUNT, CARRIER.ACCOUNT)',
                'EMAIL_TRACKING' 
            ))
            ->where('PICK_DESPATCH.PICKD_SERVICE_RECORD_ID IN (' . $this->getDbAdapter()->quote($carrierServices) . ')')
            ->where("PICK_DESPATCH.DESPATCH_STATUS IN ('DC', 'DX')");

        if (is_null($manifestId))
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID IS NULL');
        else
            $select->where('PICK_DESPATCH.PICKD_MANIFEST_ID = ?', $manifestId);

        $senderAddress = $this->_getSenderAddress();
        $result = array();
        foreach ($select->query()->fetchAll() as $resultRow) {
            $resultRow = array_merge($resultRow, $this->_getDespatchAddressDetails($resultRow['AWB_CONSIGNMENT_NO']));
            $resultRow = array_merge($resultRow, $senderAddress);
            $resultRow = array_merge($resultRow, $this->_getOrdersSenderAddress($resultRow['COMPANY_ID'] ,$resultRow['COUNTRY']));
            $tmpManifest = new ManifestBuilder_AustPost_Model_Consignment($resultRow);
            $tmpManifest->createDateTime = $createDateTime->toAustPostDateTime();
            $tmpManifest->containsDangerousGoods = $this->_getContainsDangerousGoods($resultRow['AWB_CONSIGNMENT_NO']);

            $result[] = $tmpManifest;

        }

        return $result;
    }

    protected function _getDespatchAddressDetails($consignmentNo) {
        $dbSelect = $this->getDbAdapter()->select();
        $dbSelect->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('PICK_ITEM', 'PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO', array())
            ->joinLeft('PICK_ORDER', 'PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER')
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $consignmentNo)
            ->where("PICK_DESPATCH.DESPATCH_STATUS IN ('DC', 'DX')")
            ->limit(1);

        $result = $dbSelect->query()->fetch();

        return is_array($result) ? $result : array();
    }

    protected function _getSenderCompany() {
	// this is the default company
        $select = $this->getDbAdapter()->select();

        $select->from('CONTROL', array('COMPANY_ID'))->limit(1);

        return $this->getDbAdapter()->fetchOne($select);
    }

    protected function _getSenderAddress() {
        $companyId = $this->_getSenderCompany();
        $select = $this->getDbAdapter()->select();
        $select->from('PERSON', array('ADDRESS_LINE1', 'ADDRESS_LINE2', 'ADDRESS_LINE3', 'ADDRESS_LINE4', 'ADDRESS_LINE5', 'CITY', 'COUNTRY', 'STATE', 'POST_CODE'))
            ->where('PERSON_ID = ?', $companyId)
            ->limit(1);
        // use the address of the default company

        $result = $select->query()->fetch();

        if (empty($result)) {
            throw new Exception('No PERSON records with COMPANY_ID = "' . $companyId . '" was found. Cannot get Return Address information.');
        }

        return $result;
    }

    protected function _getOrdersSenderAddress($companyId, $countryDefault) {
        $select = $this->getDbAdapter()->select();
        $selectCountry = $this->getDbAdapter()->select();
        $selectCountry->from('PERSON', array('COUNTRY'))
            ->where('PERSON_ID = ?', $companyId);

        $companyCountry = $this->getDbAdapter()->fetchOne($selectCountry);

	// if the country of the company is the default country
	// then get the rest of the address
        if ($companyCountry == $countryDefault) {
            //$select->from('PERSON', array('ADDRESS_LINE1', 'ADDRESS_LINE2', 'ADDRESS_LINE3', 'ADDRESS_LINE4', 'ADDRESS_LINE5', 'CITY', 'COUNTRY', 'STATE', 'POST_CODE','FIRST_NAME'))
            // only use the name of the address along with the default address
            $select->from('PERSON', array('FIRST_NAME'))
            ->where('PERSON_ID = ?', $companyId);
	} else {
            // only use the name of the address along with the default address
            $select->from('PERSON', array('FIRST_NAME'))
            ->where('PERSON_ID = ?', $companyId);
        }

        $result = $select->query()->fetch();

        if (empty($result)) {
            throw new Exception('No PERSON records with COMPANY_ID = "' . $companyId . '" was found. Cannot get Return Address information.');
        }

        return $result;
    }

    protected function _getContainsDangerousGoods($consignmentNo) {
        $dbSelect = $this->getDbAdapter()->select();
/*
        $dbSelect->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', array())
            ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID', array('PROD_ID'))
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $consignmentNo)
            ->where('PROD_PROFILE.PP_HAZARD_STATUS IS NOT NULL AND PROD_PROFILE.PP_HAZARD_STATUS <> ?', '')
            ->limit(1);
*/
        $dbSelect->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', array())
            ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID AND PICK_ITEM_DETAIL.COMPANY_ID = PROD_PROFILE.COMPANY_ID', array('PROD_ID'))
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $consignmentNo)
            ->where('PROD_PROFILE.PP_HAZARD_STATUS IS NOT NULL AND PROD_PROFILE.PP_HAZARD_STATUS <> ?', '')
            ->limit(1);

        if (count($dbSelect->query()->fetchAll()) > 0)
            return 'true';

        return 'false';
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
