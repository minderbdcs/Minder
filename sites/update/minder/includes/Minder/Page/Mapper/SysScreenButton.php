<?php

class Minder_Page_Mapper_SysScreenButton {
    /**
     * @param array $queryResults
     * @param string $userCategory
     * @return array
     */
    protected function _filterByUserCategory($queryResults, $userCategory) {
        $result = array();
        foreach ($queryResults as $resultRow) {
            if (empty($resultRow['SSB_USER_TYPE']) || (false !== stripos($resultRow['SSB_USER_TYPE'], $userCategory)))
                $result[] = $resultRow;
        }

        return $result;
    }

    /**
     * @param string $ssName
     * @param Minder2_Environment|null $environment
     * @return array
     */
    public function fetchEditResultButtons($ssName, $environment = null) {
        $dbTable = $this->_getDbTable();
        $select = $dbTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        $select->where('SS_NAME = ?', $ssName)->where('SSB_TAB_STATUS = ?', 'OK')->where("SSB_BUTTON_TYPE = ?", 'ER');

        if (!is_null($environment)) {
            $select
                ->where("COMPANY_ID IS NULL OR COMPANY_ID = '' OR COMPANY_ID = ?", $environment->getCurrentCompany()->COMPANY_ID)
                ->where("WH_ID IS NULL OR WH_ID = '' OR WH_ID = ?", $environment->getCurrentWarehouse()->WH_ID)
                ->where("SSB_DEVICE_TYPE IS NULL OR SSB_DEVICE_TYPE = '' OR SSB_DEVICE_TYPE = ?", $environment->getCurrentDevice()->DEVICE_TYPE);
        }

        $queryResult = $select->query()->fetchAll();
        if (empty($queryResult))
            return array();

        if (!empty($environment)) {
            if (!$environment->getCurrentUser()->isSuperAdmin())
                $queryResult = $this->_filterByUserCategory($queryResult, $environment->getCurrentUser()->USER_CATEGORY);
        }

        $result = array();
        $rowClass = $dbTable->getRowClass();
        foreach ($queryResult as $resultRow) {
            $result[] = new $rowClass(array('table' => $dbTable, 'data' => $resultRow, 'stored' => true));
        }

        return $result;
    }

    protected function _getDbTable() {
        return new Minder_Db_Table_SysScreenButton();
    }

}