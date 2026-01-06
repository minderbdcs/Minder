<?php

class Minder_SysScreen_PartBuilder_Button extends Minder_SysScreen_PartBuilder {
    protected $_partName                      = 'BUTTON';
    protected $_tableName                     = 'SYS_SCREEN_BUTTON';
    protected $_orderByFieldName              = 'SSB_SEQUENCE';

    protected $_staticLimitsUserExpression    = 'SSB_USER_TYPE';
    protected $_staticLimitsDeviceExpression  = 'SSB_DEVICE_TYPE';

    protected function _isExpandable()
    {
        return true;
    }


    protected function _getPartFilters()
    {
        return array(
            'SS_NAME = ?' => array($this->_ssRealName),
            'SSB_TAB_STATUS = ?'    => array('OK')
        );
    }

    protected function _getUserCompanyAccessList() {
        $companyList = Minder2_Environment::getCurrentUser()->getAccessCompanyList();
        return array_keys($companyList);
    }

    protected function _getStaticLimits()
    {
        $filter = array();

        $mask       = $this->_staticLimitsMask;

        if (($mask & self::DEVICE_LIMIT_MASK) == self::DEVICE_LIMIT_MASK) {
            $myDeviceType = $this->_getCurrentDeviceType();

            $filter['(' . $this->_staticLimitsDeviceExpression . " = ? OR " . $this->_staticLimitsDeviceExpression . " = ? OR " . $this->_staticLimitsDeviceExpression . ' IS NULL)'] = array($myDeviceType, '');
        }

        if (($mask & self::COMPANY_LIMIT_MASK) == self::COMPANY_LIMIT_MASK) {
            $companyList = $this->_getUserCompanyAccessList();

            if (empty($companyList))
                $filter["(" . $this->_staticLimitsCompanyExpression . " = '' OR " . $this->_staticLimitsCompanyExpression . ' IS NULL)'] = array();
            else
                $filter['(' . $this->_staticLimitsCompanyExpression . " IN (" . substr(str_repeat('?, ', count($companyList)), 0, -2) . ") OR " . $this->_staticLimitsCompanyExpression . " = '' OR " . $this->_staticLimitsCompanyExpression . ' IS NULL)'] = $companyList;

            if (!empty($this->minder->limitCompany) && 'all' != strtolower($this->minder->limitCompany))
                $filter["(" . $this->_staticLimitsCompanyExpression . " = '' OR " . $this->_staticLimitsCompanyExpression . " IS NULL OR " . $this->_staticLimitsCompanyExpression . " = ?)"] = array($this->minder->limitCompany);
        }

        if (($mask & self::WH_LIMIT_MASK) == self::WH_LIMIT_MASK) {
            $filter['(' . $this->_staticLimitsWhExpression . " = ? OR " . $this->_staticLimitsWhExpression . " = ? OR " . $this->_staticLimitsWhExpression . ' IS NULL)'] = array($this->minder->whId, '');
        }

        return $filter;
    }


}