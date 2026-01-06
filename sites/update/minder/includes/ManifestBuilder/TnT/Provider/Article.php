<?php

class ManifestBuilder_TnT_Provider_Article implements ManifestBuilder_DbAdapterAwareInterface {

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $dbAdapter;

    public function getConnoteArticles(ManifestBuilder_AustPost_Model_Consignment $connote) {
        $select = $this->getDbAdapter()->select();
        $select
            ->from('PACK_ID', array('PACK_ID', 'DESPATCH_LABEL_NO', 'DIMENSION_X', 'DIMENSION_Y', 'DIMENSION_Z', 'PACK_WEIGHT', 'DIMENSION_UOM', 'PACK_WEIGHT_UOM', 'PACK_SERIAL_NO', 'PACK_SEQUENCE_NO', 'PACK_LAST_SEQUENCE_INDICATOR'))
            ->join('PICK_DESPATCH', 'PACK_ID.DESPATCH_ID = PICK_DESPATCH.DESPATCH_ID', array())
            ->join('CARRIER_SERVICE', 'PICK_DESPATCH.PICKD_SERVICE_RECORD_ID = CARRIER_SERVICE.RECORD_ID', array('SERVICE_TRANSIT_COVER_REQD', 'SERVICE_TRANSIT_COVER_AMOUNT', 'SERVICE_LOCATION_ID', 'SERVICE_SERVICE_CODE'))
            ->where('PICK_DESPATCH.AWB_CONSIGNMENT_NO = ?', $connote->AWB_CONSIGNMENT_NO);

        $result = array();

        foreach ($select->query()->fetchAll() as $resultRow) {
            $tmpArticle = new ManifestBuilder_TnT_Model_Article($resultRow);
            $tmpArticle->articleNo = $this->_getArticleNumber($tmpArticle);
            $result[] = $tmpArticle;
        }

        return $result;
    }

    protected function _getArticleNumber(ManifestBuilder_TnT_Model_Article $article) {
        $articleNo = $this->getDbAdapter()->fetchOne("SELECT out_data FROM CALC_CHECK_DIGIT_BDCS('" . $article->getRawNumber() . "', 'T')");

        return $articleNo;
    }

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
