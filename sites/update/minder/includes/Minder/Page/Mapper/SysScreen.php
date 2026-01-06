<?php

class Minder_Page_Mapper_SysScreen {
    /**
     * @param string $ssName
     * @param Minder2_Environment|null $environment
     * @return Minder_Db_Table_SysScreenRow|null
     */
    public function find($ssName, $environment = null) {
        $dbTable = $this->_getDbTable();
        $select = $dbTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        $select->where('SS_NAME = ?', $ssName);

        if (!is_null($environment)) {
            $select
                ->where("COMPANY_ID IS NULL OR COMPANY_ID = '' OR COMPANY_ID = ?", $environment->getCurrentCompany()->COMPANY_ID)
                ->where("WH_ID IS NULL OR WH_ID = '' OR WH_ID = ?", $environment->getCurrentWarehouse()->WH_ID);
        }

        if (false === ($queryResult = $select->query()->fetch()))
            return null;

        $rowClass = $dbTable->getRowClass();
        return new $rowClass(array('table' => $dbTable, 'data' => $queryResult, 'stored' => true));
    }

    /**
     * @return Minder_Db_Table_SysScreen
     */
    protected function _getDbTable() {
        return new Minder_Db_Table_SysScreen();
    }
}