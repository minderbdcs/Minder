<?php

/**
 * Class ManifestBuilder_Model_ProdProfile
 * @property string PROD_ID
 * @property string COMPANY_ID
 * @property string SHORT_DESC
 */
class ManifestBuilder_TnT_Model_ProdProfile extends Zend_Db_Table_Row {
    protected $_tableClass = 'ManifestBuilder_TnT_Table_ProdProfile';


    public function getGoodsDescription() {
        return $this->SHORT_DESC;
    }

}
