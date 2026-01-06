<?php

class Minder_Controller_Action_Helper_Crud extends Zend_Controller_Action_Helper_Abstract {
    const PROD_PROFILE = 'PROD_PROFILE';
    const COMPANY_ID = 'company_id';

    protected $_companyIds = array();

    public function fillDefaults($table, $rowData) {
        switch (strtoupper($table)) {
            case self::PROD_PROFILE:
                return $this->_fillProdProfileDefaults($rowData);
        }

        return $rowData;
    }

    public function validateData($table, $rowData, Minder_JSResponse $response = null) {
        $response = (is_null($response)) ? new Minder_JSResponse() : $response;

        switch (strtoupper($table)) {
            case self::PROD_PROFILE:
                return $this->_validateProdProfile($rowData, $response);
        }

        return $response;
    }

    protected function _validateProdProfile($rowData, Minder_JSResponse $response) {
        $companyIds = $this->_getCompanyIds();
        $companyIds[] = 'ALL';

        if (!empty($rowData[self::COMPANY_ID])) {
            if (!in_array($rowData[self::COMPANY_ID], $companyIds)) {
                $response->addErrors("Company #" . $rowData[self::COMPANY_ID] . ' not exists.');
            }
        }

        return $response;
    }

    protected function _fillProdProfileDefaults($rowData) {
        if (empty($rowData[self::COMPANY_ID])) {
            $rowData[self::COMPANY_ID] = $this->_getDefaultCompany();
        }

        return $rowData;
    }

    protected function _getDefaultCompany() {
        $companyIds = $this->_getCompanyIds();

        return (count($companyIds) == 1) ? array_shift($companyIds) : '';
    }

    protected function _getCompanyIds() {
        if (empty($this->_companyIds)) {
            $this->_companyIds = $this->_fetchCompanyIds();
        }

        return $this->_companyIds;
    }

    protected function _fetchCompanyIds() {
        $sql = "SELECT COMPANY_ID FROM COMPANY";

        return Minder::getInstance()->fetchColumn('COMPANY_ID', $sql);
    }
}