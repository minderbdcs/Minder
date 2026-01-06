<?php
class Minder_SysScreen_Model_ProductDetails extends Minder_SysScreen_Model_ProdProfile_Abstract
{
    public function selectProdId($rowOffset, $itemCountPerPage) {
        $prodId = array();
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PROD_PROFILE.PROD_ID');
        if (is_array($result) && count($result) > 0)
            $prodId = array_map(create_function('$item', 'return $item["PROD_ID"];'), $result);
        
        return $prodId;
    }
    
    public function selectProdIdAndShortDesc($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'PROD_PROFILE.PROD_ID, PROD_PROFILE.SHORT_DESC');
    }

//    public function selectProductLabelData($rowOffset, $itemCountPerPage) {
        //for now will pass all fields from model tables untill other specified
//        return $this->selectArbitraryDataExt($rowOffset, $itemCountPerPage, '*');
//    }
    protected function _selectProdIdAndCompanyId($rowOffset, $itemCountPerPage)
    {
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PROD_PROFILE.PROD_ID, PROD_PROFILE.COMPANY_ID');
        return is_array($result) ? $result : array();
    }
}

class Minder_SysScreen_Model_ProductDetails_Exception extends Minder_SysScreen_Model_Exception {}
