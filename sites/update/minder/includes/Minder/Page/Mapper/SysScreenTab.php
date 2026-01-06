<?php

class Minder_Page_Mapper_SysScreenTab {

    protected function _filterByUserCategory($queryResults, $userCategory) {
        $result = array();
        foreach ($queryResults as $resultRow) {
            if (empty($resultRow['SST_USER_TYPE']) || (false !== stripos($resultRow['SST_USER_TYPE'], $userCategory)))
                $result[] = $resultRow;
        }

        return $result;
    }

    /**
     * @param $ssName
     * @param Minder2_Environment|null $environment
     * @return array
     */
    public function fetchEditResultTab($ssName, $environment = null) {
        $dbTable = $this->_getDbTable();
        $select  = $dbTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        $select->where('SS_NAME = ?', $ssName)->where('SST_TAB_STATUS = ?', 'OK')->where('SST_FIELD_TYPE = ?', 'ER');

        if (!is_null($environment)) {
            $select
                ->where("WH_ID IS NULL OR WH_ID = '' OR WH_ID = ?", $environment->getCurrentWarehouse()->WH_ID)
                ->where("COMPANY_ID IS NULL OR COMPANY_ID = '' OR COMPANY_ID = ?", $environment->getCurrentCompany()->COMPANY_ID)
                ->where("SST_DEVICE_TYPE IS NULL OR SST_DEVICE_TYPE	= '' OR SST_DEVICE_TYPE	= ?", $environment->getCurrentDevice()->DEVICE_TYPE);
        }

        $queryResult = $select->query()->fetchAll();
        if (empty($queryResult))
            return array();

        if (!is_null($environment))
            $queryResult = $this->_filterByUserCategory($queryResult, $environment->getCurrentUser()->USER_CATEGORY);

        $result = array();
        $rowClass = $dbTable->getRowClass();

        foreach ($queryResult as $resultRow) {
            $result[] = new $rowClass(array('table' => $dbTable, 'data' => $resultRow, 'stored' => true));
        }

        return $result;
    }

    protected function _getDbTable() {
        return new Minder_Db_Table_SysScreenTab();
    }
}