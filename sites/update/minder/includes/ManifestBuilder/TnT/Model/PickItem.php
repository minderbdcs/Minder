<?php

/**
 * Class ManifestBuilder_Model_ProdProfile
 * @property string PICK_LABEL_NO
 * @property string SALES_PRICE
 * @property string DISCOUNT
 */
class ManifestBuilder_TnT_Model_PickItem extends Zend_Db_Table_Row {
    protected $_tableClass = 'ManifestBuilder_TnT_Table_PickItem';


    public function getGoodsValue( $PickQty ) {
/*
  (COALESCE(PICK_ITEM.SALE_PRICE,0) * (COALESCE(PICK_ITEM.PICKED_QTY,0)))*
  (1 - COALESCE(PICK_ITEM.DISCOUNT,0)/100)
*/
        return (float) $this->SALE_PRICE * $PickQty * (1.0 - $this->DISCOUNT / 100.0);
    }

}
