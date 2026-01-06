<?php

class Minder_View_Helper_JsDataRowMetadataAdapter extends Zend_View_Helper_Abstract {

    protected function _metadataFilter($item) {
        return $item['VIRTUAL'];
    }

    /**
     * @param Minder_Db_SysScreenTable  $sysScreenTable
     * @return array
     */
    protected function _formatRowMetadata($sysScreenTable) {
        $metadata = $sysScreenTable->info(Zend_Db_Table::METADATA);
        return array_filter($metadata, array($this, '_metadataFilter'));
    }

    /**
     * @param Minder_Db_SysScreenTable | Zend_Db_Table_Row $sysScreenTable
     * @return array
     */
    public function jsDataRowMetadataAdapter($sysScreenTable) {
        if ($sysScreenTable instanceof Zend_Db_Table_Row)
            $sysScreenTable = $sysScreenTable->getTable();

        if (!$sysScreenTable instanceof Minder_Db_SysScreenTable)
            return array();

        return json_encode($this->_formatRowMetadata($sysScreenTable));
    }
}