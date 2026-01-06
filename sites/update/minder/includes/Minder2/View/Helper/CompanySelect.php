<?php

class Minder2_View_Helper_CompanySelect extends Zend_View_Helper_Abstract {

    /**
     * @param Minder2_Model_Company $companyA
     * @param Minder2_Model_Company $companyB
     * @return int
     */
    protected function _sorter($companyA, $companyB) {
        if ($companyA->COMPANY_ID > $companyB->COMPANY_ID)
            return 1;

        if ($companyA->COMPANY_ID < $companyB->COMPANY_ID)
            return -1;

        return 0;
    }

    protected function _getCompanyLimitList() {
        $result = array();

        try {
            $defaultCompany = Minder2_Environment::getInstance()->getSystemControls()->getCompany();
            $companyList = Minder2_Environment::getInstance()->getCompanyList();

            if (isset($companyList[$defaultCompany->COMPANY_ID]))
                unset($companyList[$defaultCompany->COMPANY_ID]); //will add later

            usort($companyList, array($this, '_sorter'));
            array_unshift($companyList, $defaultCompany);

            /**
             * @var Minder2_Model_Company $company
             */
            foreach ($companyList as $company) {
                $result[$company->COMPANY_ID] = $company->COMPANY_ID;
            }
        } catch (Exception $e) {

        }

        return $result;
    }

    public function companySelect($name, $value, $attribs = null, $listsep = "<br />\n") {
        return $this->view->formSelect($name, $value, $attribs, $this->_getCompanyLimitList(), $listsep);
    }
}