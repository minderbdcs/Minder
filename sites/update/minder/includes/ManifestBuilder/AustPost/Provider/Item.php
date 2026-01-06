<?php

class ManifestBuilder_AustPost_Provider_Item implements ManifestBuilder_DbAdapterAwareInterface {

    public function getItems(ManifestBuilder_AustPost_Model_Article $article) {
        $select = $this->getDbAdapter()->select();

/*
        $select->from('PACK_ID', array('DESPATCH_ID'))
            ->joinLeft('PICK_ITEM_DETAIL', 'PACK_ID.PACK_ID = PICK_ITEM_DETAIL.PACK_ID', array('PICK_LABEL_NO', 'QTY_PICKED', 'PICK_DETAIL_STATUS'))
            ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', 'SSN_ID')
            ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID', array('PROD_ID', 'SHORT_DESC'))
            ->where('PACK_ID.PACK_ID = ?', array($article->PACK_ID));
*/
        $select->from('PACK_ID', array('DESPATCH_ID'))
            ->joinLeft('PICK_ITEM_DETAIL', 'PACK_ID.PACK_ID = PICK_ITEM_DETAIL.PACK_ID', array('PICK_LABEL_NO', 'QTY_PICKED', 'PICK_DETAIL_STATUS'))
            ->joinLeft('ISSN', 'PICK_ITEM_DETAIL.SSN_ID = ISSN.SSN_ID', 'SSN_ID')
            ->joinLeft('PROD_PROFILE', 'ISSN.PROD_ID = PROD_PROFILE.PROD_ID AND PICK_ITEM_DETAIL.COMPANY_ID = PROD_PROFILE.COMPANY_ID', array('PROD_ID', 'SHORT_DESC'))
            ->where('PACK_ID.PACK_ID = ?', array($article->PACK_ID));

        $result = array();

        foreach ($select->query()->fetchAll() as $resultRow) {
            $result[] = new ManifestBuilder_AustPost_Model_Item($resultRow);
        }

        return $result;
    }

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    public $dbAdapter;

    /**
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     * @return ManifestBuilder_DbAdapterAwareInterface
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        return $this;
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }
}
