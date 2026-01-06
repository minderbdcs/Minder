<?php

class ManifestBuilder_Table_PackId extends Zend_Db_Table {
    protected $_name = 'PACK_ID';
    protected $_rowClass = 'ManifestBuilder_Model_PackId';

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return Zend_Db_Table_Rowset_Abstract | ManifestBuilder_Model_PackId[]
     */
    public function _getDespatchedPackIds(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        $select = $this->select(static::SELECT_WITH_FROM_PART)
            ->where('DESPATCH_ID = ?', $pickDespatch->DESPATCH_ID);

        return $this->fetchAll($select);
    }
}