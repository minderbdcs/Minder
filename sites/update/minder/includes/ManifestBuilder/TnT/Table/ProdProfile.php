<?php

    /**
     * @param ManifestBuilder_Model_PackId $packId
     * @return ManifestBuilder_TnT_Model_ProdProfile
     */
class ManifestBuilder_TnT_Table_ProdProfile extends Zend_Db_Table {
    protected $_name = 'PROD_PROFILE';
    protected $_rowClass = 'ManifestBuilder_TnT_Model_ProdProfile';

    public function _getDespatchedProducts(ManifestBuilder_Model_PackId $packId) {
        $select = $this->select();

        $select->from('PACK_ID', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PACK_ID.PACK_ID = PICK_ITEM_DETAIL.PACK_ID', array())
            ->joinLeft('PROD_PROFILE', 'PICK_ITEM_DETAIL.PROD_ID = PROD_PROFILE.PROD_ID AND PICK_ITEM_DETAIL.COMPANY_ID = PROD_PROFILE.COMPANY_ID', array('PROD_ID', 'SHORT_DESC'))
            ->where('PACK_ID.PACK_ID = ?', array($packId->PACK_ID))
            ->limit(1);

        $result = $this->fetchRow($select);
  
        return is_null($result) ? $this->createRow(array()) : $result;
    }
}
