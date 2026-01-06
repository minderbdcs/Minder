<?php

class Minder_Page_Mapper_SysMenu {

    /**
     * @param string $menuId
     * @param Minder2_Environment|null $environment
     * @return Zend_Db_Table_Row|null
     */
    public function find($menuId, $environment = null) {
        $sysMenuTable = $this->_getDbTable();
        $select = $sysMenuTable->select(Zend_Db_Table::SELECT_WITH_FROM_PART);

        $select->where('SM_SUBMENU_ID = ?', $menuId);

        if (!is_null($environment)) {
            $select
                ->where("SM_COMPANY_ID IS NULL OR SM_COMPANY_ID = '' OR SM_COMPANY_ID = ?", $environment->getCurrentCompany()->COMPANY_ID)
                ->where("SM_MENU_STATUS = ?", 'OK')
                ->where("SM_USER_CATEGORY IS NULL OR SM_USER_CATEGORY = '' OR SM_USER_CATEGORY = ?", strval($environment->getCurrentUser()->USER_CATEGORY))
                ->where("SM_WH_ID IS NULL OR SM_WH_ID = '' OR SM_WH_ID = ?", $environment->getCurrentWarehouse()->WH_ID);
        }

        if (($queryResult = $select->query()->fetch()) === false)
            return null;

        $rowClass = $sysMenuTable->getRowClass();
        return new $rowClass(array('table' => $sysMenuTable, 'data' => $queryResult, 'stored' => true));
    }

    /**
     * @return Minder_Db_Table_SysMenu
     */
    protected function _getDbTable() {
        return new Minder_Db_Table_SysMenu();
    }
}