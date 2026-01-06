<?php

class Minder_View_Helper_JsDataRowTableNameAdapter extends Zend_View_Helper_Abstract {

    /**
     * @param Zend_Db_Table | Zend_Db_Table_Row $dbTable
     * @return string
     */
    public function jsDataRowTableNameAdapter($dbTable) {
        if ($dbTable instanceof Zend_Db_Table_Row)
            $dbTable = $dbTable->getTable();

        return $dbTable->info(Zend_Db_Table::NAME);
    }
}