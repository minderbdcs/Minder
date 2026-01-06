<?php

    /**
     * @param ManifestBuilder_Model_PackId $packId
     * @return ManifestBuilder_TnT_Model_ProdProfile
     */
class ManifestBuilder_TnT_Table_PickItem extends Zend_Db_Table {
    protected $_name = 'PICK_ITEM';
    protected $_rowClass = 'ManifestBuilder_TnT_Model_PickItem';

    public function _getDespatchedPickItem(ManifestBuilder_Model_PackId $packId) {
        $select = $this->select();

        $select->from('PACK_ID', array())
            ->join('PICK_ITEM_DETAIL', 'PACK_ID.PACK_ID = PICK_ITEM_DETAIL.PACK_ID', array())
            ->join('PICK_ITEM', 'PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO', array('PICK_LABEL_NO', 'SALE_PRICE','DISCOUNT'))
            ->where('PACK_ID.PACK_ID = ?', array($packId->PACK_ID))
            ->limit(1);
/* what about when no record in pick_item_detail for pack_id
   have records with despatch_id
   so pick first pick_item_detail with null pack_id but have the despatch_id
*/

        $result = $this->fetchRow($select);
        $wkMinQty = 0;
  
        if (is_null($result)) {
            $select->reset();
            $select->from('PACK_ID', array())
                ->joinLeft('PICK_ITEM_DETAIL', 'PACK_ID.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID ', array())
                ->joinLeft('PICK_ITEM', 'PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO', array('PICK_LABEL_NO', 'SALE_PRICE','DISCOUNT'))
                ->where('PACK_ID.PACK_ID = ?', array($packId->PACK_ID))
                ->where('PICK_ITEM_DETAIL.PACK_ID IS NULL' )
                ->where('PICK_ITEM_DETAIL.QTY_PICKED > ?', $wkMinQty )
                ->limit(1);
        }
        return is_null($result) ? $this->createRow(array()) : $result;
    }
}
