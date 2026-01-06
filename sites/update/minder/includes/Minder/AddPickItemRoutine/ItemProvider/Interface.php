<?php

interface Minder_AddPickItemRoutine_ItemProvider_Interface {

    /**
     * @abstract
     * @param Minder_AddPickItemRoutine_Request $request
     * @return array
     */
    function getStockInfo($request);

    /**
     * @abstract
     * @param Minder_AddPickItemRoutine_Request $request
     * @param array $stockInfoArray
     * @return void
     */
    function addPickItem($request, $stockInfoArray = null);

    /**
     * @abstract
     * @param string $itemId
     * @return string
     */
    function formatItemNotFoundMessage($itemId);
}