<?php

class Minder_SysScreen_DataSource_SystemParameterProvider implements Minder_SysScreen_DataSource_Parameter_Interface {

    public function getCompanyLimit() {
        return "'" . Minder2_Environment::getCompanyLimit()->COMPANY_ID . "'";
    }

    function getValue($paramName)
    {
        switch ($paramName) {
            case "%COMPANY_LIMIT%":
                return $this->getCompanyLimit();
            default:
                throw new Minder_SysScreen_DataSource_Parameter_Exception('Unsupported Datasource Parameter "' . $paramName . '"');
        }
    }

}