<?php

interface Minder_Page_SysScreenMapper_Interface {
    /**
     * @abstract
     * @param string $recordId
     * @return Zend_Db_Table_RowSet
     */
    function find($recordId);

    function findRecord($recordData);

    public function update($rowData);
}