<?php

class Minder_LabelPrinter_Issn extends Minder_LabelPrinter_Abstract {

    protected $_issn            = array();
    protected $_ssn             = array();
    protected $_grn             = array();
    protected $_prodProfile     = array();
    protected $_companyId       = array();
    protected $locnId           = array();

    protected function _fetchIssn($ssnId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT ISSN.*, SSN.*, LOCATION.* FROM ISSN JOIN SSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID LEFT OUTER JOIN LOCATION ON SSN.HOME_LOCN_ID = LOCATION.LOCN_ID AND SSN.HOME_WH_ID = LOCATION.WH_ID WHERE ISSN.SSN_ID= ? ', $ssnId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        if (count($queryResult) < 1) {
            throw new Exception('ISSN #' . $ssnId . ' not found ');
        }

        return array_shift($queryResult);
    }

    protected function _getIssn($ssnId) {
        if (empty($this->_issn[$ssnId])) {
            $this->_issn[$ssnId] = $this->_fetchIssn($ssnId, $ssnId);
        }

        return $this->_issn[$ssnId];
    }

    protected function _fetchSsn($ssnId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM SSN WHERE SSN_ID = ?', $ssnId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getSsn($ssnId) {
        $issn = $this->_getIssn($ssnId);

        $originalSsnId = $issn['ISSN.ORIGINAL_SSN'];

        if (empty($this->_ssn[$originalSsnId])) {
            $this->_ssn[$originalSsnId] = $this->_fetchSsn($originalSsnId, $ssnId);
        }

        return $this->_ssn[$originalSsnId];
    }

    protected function _fetchGrn($grnNo, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM GRN WHERE GRN.GRN = ?', $grnNo))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getGrn($ssnId) {
        $ssn = $this->_getSsn($ssnId);

        if (empty($ssn)) {
            return array();
        }

        $grnNo = $ssn['SSN.GRN'];

        if (empty($this->_grn[$grnNo])) {
            $this->_grn[$grnNo] = $this->_fetchGrn($grnNo, $ssnId);
        }

        return $this->_grn[$grnNo];
    }

    protected function _fetchProdProfile($prodId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM PROD_PROFILE WHERE PROD_PROFILE.PROD_ID = ?', $prodId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getProdProfile($ssnId) {
        $issn = $this->_getIssn($ssnId);

        if (empty($issn)) {
            return array();
        }

        $prodId = $issn['ISSN.PROD_ID'];

        if (empty($this->_prodProfile[$prodId])) {
            $this->_prodProfile[$prodId] = $this->_fetchProdProfile($prodId, $ssnId);
        }

        return $this->_prodProfile[$prodId];
    }

    protected function _fetchCompanyId($companyId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt('SELECT FIRST 1 * FROM COMPANY WHERE COMPANY.COMPANY_ID = ?', $companyId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getCompany($ssnId) {
        $issn = $this->_getIssn($ssnId);

        if (empty($issn)) {
            return array();
        }

        $companyId = $issn['ISSN.COMPANY_ID'];

        if (empty($this->_companyId[$companyId])) {
            $this->_companyId[$companyId] = $this->_fetchCompanyId($companyId, $ssnId);
        }

        return $this->_companyId[$companyId];
    }



/*
*
*/

protected function _fetchLocationId($locnId, $whId, $labelId) {
        if (false === ($queryResult = $this->_getMinder()->fetchAllAssocExt("SELECT FIRST 1 * FROM LOCATION WHERE LOCATION.WH_ID= '".$whId."' AND LOCATION.LOCN_ID = ?", $locnId))) {
            throw new Exception('Error fetching label data #' . $labelId . ': ' . $this->_getMinder()->lastError);
        }

        return  (count($queryResult) < 1) ? array() : array_shift($queryResult);
    }

    protected function _getLocation($ssnId) {		
        $issn = $this->_getIssn($ssnId);

        if (empty($issn)) {
            return array();
        }

        $locnId = $issn['SSN.HOME_LOCN_ID'];
	$whId = $issn['SSN.HOME_WH_ID'];

        if (empty($this->_locnId[$locnId])) {
            $this->_locnId[$locnId] = $this->_fetchLocationId($locnId, $whId, $ssnId);
        }

        return $this->_locnId[$locnId];
    }

/*
*
*/

    protected function _fetchLabelDataFromTable($tableName, $labelId)
    {
        switch ($tableName) {
            case 'ISSN':
                return $this->_getIssn($labelId);
            case 'SSN':
                return $this->_getSsn($labelId);
            case 'GRN':
                return $this->_getGrn($labelId);
            case 'PROD_PROFILE':
                return $this->_getProdProfile($labelId);
            case 'COMPANY':
                return $this->_getCompany($labelId);
	    case 'LOCATION':
		return $this->_getLocation($labelId);
            default:
                return array();
        }
    }

    protected function _printLabel($labeldata)
    {
        return $this->_getPrinter()->printIssnLabel($labeldata);
    }

    function __construct()
    {
        parent::__construct('ISSN');
    }
}
