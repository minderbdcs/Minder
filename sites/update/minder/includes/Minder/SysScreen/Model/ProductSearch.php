<?php

class Minder_SysScreen_Model_ProductSearch extends Minder_SysScreen_Model_AbstractProduct
{
    public function __construct() {
        $minder = Minder::getInstance();
        
        if ($minder->defaultControlValues['CONFIRM_WITH_NO_PROD'] !== 'T') {
            $this->staticConditions['COALESCE(AVAILABLE_QTY, 0) > 0'] = array();
        }
    }

    public function selectProdId($rowOffset, $itemCountPerPage) {
        $result = array();

        if (false !== ($response = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PROD_PROFILE.PROD_ID'))) {
            $result = array_map(create_function('$item', 'return $item["PROD_ID"];'), $response);
        }

        return $result;
    }

    public function selectProductInfoForAddPickItem($rowOffset, $itemCountPerPage) {
        $result = array();
        
        if (false !== ($response = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PROD_PROFILE.PROD_ID, PROD_PROFILE.SALE_PRICE'))) {
            $result = $response;
        }
        
        return $result;
    }
}
