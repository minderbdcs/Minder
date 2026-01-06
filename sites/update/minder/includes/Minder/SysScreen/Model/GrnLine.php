<?php
class Minder_SysScreen_Model_GrnLine extends Minder_SysScreen_Model
{
    public function selectProductLabelData($rowOffset, $itemCountPerPage) {
        $labelData = array();
        $prodIds   = array();
        $result    = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PROD_PROFILE.PROD_ID ');
        
        if (count($result) > 0)
            $prodIds = array_map(create_function('$item', 'return $item["PROD_ID"];'), $result);
        else 
            return $labelData;
        
        $minder    = Minder::getInstance();
        $labelData = $minder->selectProductLabelData($prodIds);
        
        return $labelData;
    }
    
    public function selectIssnLabelData($rowOffset, $itemCountPerPage) {
        //for now will pass all fields from model tables untill other specified
        return $this->selectArbitraryDataExt($rowOffset, $itemCountPerPage, '*');
    }
    
    public function selectProdId($rowOffset, $itemCountPerPage) {
        $prodId = array();
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ISSN.PROD_ID');
        if (is_array($result) && count($result) > 0)
            $prodId = array_map(create_function('$item', 'return $item["PROD_ID"];'), $result);
        
        return $prodId;
    }

    public function selectDataForGnnpTransaction($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ISSN.ORIGINAL_QTY, ISSN.PROD_ID, GRN.ORDER_NO, GRN.ORDER_LINE_NO, GRN.GRN');
    }
}

class Minder_SysScreen_Model_GrnLine_Exception extends Minder_SysScreen_Model_Exception {}
