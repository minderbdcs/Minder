<?php

class ManifestBuilder_Table_ProdProfile extends Zend_Db_Table {
    protected $_name = 'PROD_PROFILE';

    public function getDespatchedProductsWithDangerousGoodsAmount(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        $select = $this->select();
        $select->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', array())
            ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID', array('PROD_ID'))
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $pickDespatch->AWB_CONSIGNMENT_NO)
            ->where('PROD_PROFILE.PP_HAZARD_STATUS IS NOT NULL AND PROD_PROFILE.PP_HAZARD_STATUS <> ?', '')
            ->limit(1);

        return count($select->query()->fetchAll());
    }
}