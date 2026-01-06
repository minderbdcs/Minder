<?php

class Minder_AddPickItemRoutine_ItemProvider_ProdProfile implements Minder_AddPickItemRoutine_ItemProvider_Interface {
    const PROVIDER_NAME = 'PROD_PROFILE';

    /**
     * @param Minder_AddPickItemRoutine_Request $request
     * @return void
     */
    function getStockInfo($request)
    {
        $prodIds = is_array($request->itemIdList) ? $request->itemIdList : array($request->itemIdList);
        $result  = array();

        $minder = Minder::getInstance();

        foreach ($prodIds as $prodId) {
            $tmpProdIdRow = $minder->fetchAssoc('SELECT PROD_ID, SALE_PRICE FROM PROD_PROFILE WHERE PROD_ID = ?', $prodId);

            if ($tmpProdIdRow === false) {
                $result[] = new Minder_AddPickItemRoutine_ItemProvider_StockInfo($prodId, 0, false, 0);
            } else {
                $tmpAvailableQty = $minder->fetchOne("SELECT AVAILABLE_QTY FROM PRODUCT_CMP_WH_STOCK_STATUS_V(?, ?, ?, ?,'AVAILABLE_QTY')", $prodId, '', '', $minder->userId);
                $result[] = new Minder_AddPickItemRoutine_ItemProvider_StockInfo($prodId, $tmpAvailableQty, true, $tmpProdIdRow['SALE_PRICE']);
            }
        }

        return $result;
    }

    /**
     * @param string $itemId
     * @return string
     */
    function formatItemNotFoundMessage($itemId)
    {
        return 'PROD_ID #' . $itemId . ' not found.';
    }

    /**
     * @param Minder_AddPickItemRoutine_Request $request
     * @param array $stockInfoArray
     * @return void
     */
    function addPickItem($request, $stockInfoArray = null)
    {
        if (is_null($stockInfoArray))
            $stockInfoArray = $this->getStockInfo($request);

        $minder = Minder::getInstance();

        /**
         * @var Minder_AddPickItemRoutine_ItemProvider_StockInfo $stockInfo
         */
        foreach ($stockInfoArray as $stockInfo) {

            $toAddAmount = min($request->toAddAmount, $stockInfo->availableQty);
            $itemPrice   = (empty($stockInfo->defaultPrice)) ? $request->defaultPrice : $stockInfo->defaultPrice;

            $minder->execSQL(
                "EXECUTE PROCEDURE ADD_PICK_ITEMS(?, 'T', 'T', ?, '', '', ?, 'NOW', ?, ?)",
                array(
                    $request->orderNo,
                    $stockInfo->itemId,
                    $toAddAmount,
                    $minder->userId,
                    $itemPrice
                )
            );

            $request->toAddAmount -= $toAddAmount;

            if ($request->toAddAmount < 1) break;
        }
    }
}
