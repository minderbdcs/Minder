<?php

class Minder_Page_Mapper_SysScreenAction {

    /**
     * @param string $ssName
     * @param Minder2_Environment|null $environment
     * @return array
     */
    public function fetchEditResultsActions($ssName, $environment = null) {
        $dbTable = $this->_getDbTable();
        $select = $dbTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        $select->where('SS_NAME = ?', $ssName)->where('SSA_ACTION_STATUS = ?', 'OK');

        $queryResult = $select->query()->fetchAll();
        if (empty($queryResult))
            return array();

        $result = array();
        $rowClass = $dbTable->getRowClass();
        foreach ($queryResult as $resultRow) {
            $result[] = new $rowClass(array('table' => $dbTable, 'data' => $resultRow, 'stored' => true));
        }

        return $result;
    }

    protected function _getDbTable() {
        return new Minder_Db_Table_SysScreenAction();
    }
}