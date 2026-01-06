<?php

class ManifestBuilder_Table_PickOrder extends Zend_Db_Table {
    protected $_name = 'PICK_ORDER';
    protected $_rowClass = 'ManifestBuilder_Model_PickOrder';

    /**
     * @param ManifestBuilder_Model_PickDespatch $pickDespatch
     * @return ManifestBuilder_Model_PickOrder
     */
    public function getFirstDespatchedOrder(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        $select = $this->select()
            ->from('PICK_DESPATCH', array())
            ->joinLeft('PICK_ITEM_DETAIL', 'PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID', array())
            ->joinLeft('PICK_ITEM', 'PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO', array())
            ->joinLeft('PICK_ORDER', 'PICK_ITEM.PICK_ORDER = PICK_ORDER.PICK_ORDER')
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $pickDespatch->AWB_CONSIGNMENT_NO)
            ->where("PICK_DESPATCH.DESPATCH_STATUS IN ('DC', 'DX')")
            ->limit(1);

        $result = $this->fetchRow($select);

        return is_null($result) ? $this->createRow(array()) : $result;
    }
}
