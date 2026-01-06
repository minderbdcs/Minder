<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;

class CompanyManager {
    static private $_virtualAllCompanyData = array('COMPANY_ID' => 'all');

    public function getUserCompanyLimitList(\Minder2_Model_SysUser $sysUser) {
        $accessList = array(static::$_virtualAllCompanyData);

        foreach($sysUser->getAccessCompanyList() as $company) {
            $accessList[] = $company->getFields();
        }

        $result = new CompanyCollection();
        $result->init($accessList, new AddOptions(false, true));

        return $result;
    }

    public function getCompanyLimit(\Minder2_Model_Company $legacyLimitModel) {
        $result = new Company();
        $result->init(
            $legacyLimitModel->existed ? $legacyLimitModel->getFields() : static::$_virtualAllCompanyData,
            true
        );

        return $result;
    }
}