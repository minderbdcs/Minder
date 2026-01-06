<?php

class Minder_AddPickItemRoutine_ItemProvider_Issn implements Minder_AddPickItemRoutine_ItemProvider_Interface {
    const PROVIDER_NAME = 'ISSN';

    /**
     * @var null|array
     */
    protected $_whLimitList = null;
    /**
     * @var null|array
     */
    protected $_companyLimitList = null;
    /**
     * @var array
     */
    protected $_tableFields = array();

    /**
     * @return array
     */
    protected function _getWhLimitList() {
        if (is_null($this->_whLimitList))
            $this->_whLimitList = array_keys(Minder::getInstance()->getWarehouseListLimited());

        return $this->_whLimitList;
    }

    /**
     * @return array
     */
    protected function _getCompanyLimitList() {
        if (is_null($this->_companyLimitList))
            $this->_companyLimitList = array_keys(Minder::getInstance()->getCompanyListLimited());

        return $this->_companyLimitList;
    }

    protected function _uppercase(&$item, $key) {
        $item = strtoupper($item);
    }

    /**
     * @param string $table
     * @return array
     */
    protected function _getTableFields($table) {
        if (!isset($this->_tableFields[$table])) {
            $this->_tableFields[$table] = Minder::getInstance()->getFieldList($table);
            array_walk($this->_tableFields[$table], array($this, '_uppercase'));
        }

        return $this->_tableFields[$table];
    }

    /**
     * Quote a raw string.
     *
     * @param string $value     Raw string
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $value = str_replace("'", "''", $value);
        return "'" . $value . "'";
    }

    /**
     * @param string $table
     * @param string $fieldName
     * @param callback $limitCallback - array function()
     * @return string
     */
    protected function _formatFieldLimitForTable($table, $fieldName, $limitCallback) {
        $tableFields = $this->_getTableFields($table);

        if (isset($tableFields[$fieldName])) {
            $tmpFullFieldName = $table . '.' . $fieldName;
            $limitArray = call_user_func($limitCallback);
            return '(' . $tmpFullFieldName . " IN ('" . implode("', '", $limitArray) . "') OR " . $tmpFullFieldName . ' IS NULL OR ' . $tmpFullFieldName . " = '')";
        }

        return '';
    }

    /**
     * @param string $ssnId
     * @return string
     */
    protected function _getStockInfoQuery($ssnId) {
        $limits = array("ISSN.SSN_ID = " . $this->_quote($ssnId));

        foreach (array('ISSN', 'SSN', 'PROD_PROFILE') as $table) {
            $whLimit = $this->_formatFieldLimitForTable($table, 'WH_ID', array($this, '_getWhLimitList'));
            if (!empty($whLimit)) $limits[] = $whLimit;

            $companyLimit = $this->_formatFieldLimitForTable($table, 'COMPANY_ID', array($this, '_getCompanyLimitList'));
            if (!empty($companyLimit)) $limits[] = $companyLimit;
        }

        return "
            SELECT
                ISSN.SSN_ID,
                ISSN.CURRENT_QTY,
                PROD_PROFILE.SALE_PRICE,
                SSN.SSN_SALE_PRICE
            FROM
                ISSN
                LEFT JOIN SSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID
                LEFT JOIN PROD_PROFILE ON ISSN.PROD_ID = PROD_PROFILE.PROD_ID
            WHERE
        " . implode(PHP_EOL . " AND ", $limits);
    }

    protected function _fetchSsnId($ssnId, $tableName) {
        if (empty($ssnId))
            return '';

        $sql = 'SELECT SSN_ID FROM ' . $tableName . ' WHERE SSN_ID = ?';
        $limits = array();

        $whLimit = $this->_formatFieldLimitForTable($tableName, 'WH_ID', array($this, '_getWhLimitList'));
        if (!empty($whLimit)) $limits[] = $whLimit;

        $companyLimit = $this->_formatFieldLimitForTable($tableName, 'COMPANY_ID', array($this, '_getCompanyLimitList'));
        if (!empty($companyLimit)) $limits[] = $companyLimit;

        if (!empty($limits))
            $sql .= ' AND ' . implode(' AND ', $limits);

        return strval(Minder::getInstance()->fetchOne($sql, $ssnId));
    }

    protected function _ssnIdExists($ssnId) {
        if (empty($ssnId))
            return false;

        return ($this->_fetchSsnId($ssnId, 'SSN') == $ssnId) || ($this->_fetchSsnId($ssnId, 'ISSN') == $ssnId);
    }

    /**
     * @param Minder_AddPickItemRoutine_Request $request
     * @return array
     */
    public function getStockInfo($request)
    {
        $ssnIds = is_array($request->itemIdList) ? $request->itemIdList : array($request->itemIdList);
        $result  = array();

        $minder = Minder::getInstance();

        foreach ($ssnIds as $ssnId) {
            if (!$this->_ssnIdExists($ssnId)) {
                $result[] = new Minder_AddPickItemRoutine_ItemProvider_StockInfo($ssnId, 0, false, 0);
                continue;
            }

            $tmpSsnIdRow = $minder->fetchAssoc($this->_getStockInfoQuery($ssnId));

            if ($tmpSsnIdRow === false) {
                $result[] = new Minder_AddPickItemRoutine_ItemProvider_StockInfo($ssnId, 0, true, 0);
            } else {
                $salePrice = (empty($tmpSsnIdRow['SSN_SALE_PRICE'])) ? $tmpSsnIdRow['SALE_PRICE'] : $tmpSsnIdRow['SSN_SALE_PRICE'];

                $tmpAvailableQty = $tmpSsnIdRow['CURRENT_QTY'];
                $result[] = new Minder_AddPickItemRoutine_ItemProvider_StockInfo($ssnId, $tmpAvailableQty, true, $salePrice);
            }
        }

        return $result;

    }

    /**
     * @param string $itemId
     * @return string
     */
    public function formatItemNotFoundMessage($itemId)
    {
        return 'Non-Product is not listed in SSN: ' . $itemId;
    }

    /**
     * @param Minder_AddPickItemRoutine_Request $request
     * @param array $stockInfoArray
     * @return void
     */
    public function addPickItem($request, $stockInfoArray = null)
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
                "EXECUTE PROCEDURE ADD_PICK_SSN_ITEMS(?, 'T', 'T', ?, '', ?, 'NOW', ?, ?)",
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