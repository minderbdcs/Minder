<?php

    /**
     * @param ManifestBuilder_Model_PackId $packId
     * @return ManifestBuilder_TnT_Model_Uom
     */
class ManifestBuilder_TnT_Table_Uom extends Zend_Db_Table {
    protected $_name = 'UOM';
    protected $_rowClass = 'ManifestBuilder_TnT_Model_Uom';

    public function _getPackUom(ManifestBuilder_Model_PackId $packId) {
//var_dump($packId);
//var_dump($packId->DIMENSION_UOM);
        $select = $this->select();

        $select->from('UOM' )

            ->where('UOM.CODE = ?', array($packId->DIMENSION_UOM))
            ->limit(1);

        $result = $this->fetchRow($select);
  
        return is_null($result) ? $this->createRow(array()) : $result;
    }
}
