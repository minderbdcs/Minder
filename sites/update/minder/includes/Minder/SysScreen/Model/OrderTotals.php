<?php

class Minder_SysScreen_Model_OrderTotals extends Minder_SysScreen_Model {
    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return 1;
    }

    /**
     * Add conditions for query.
     *
     * @param array   $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function addConditions($conditions = array())
    {
        // not used
    }

    protected function _getTotalOrders() {
        $sql = "
            SELECT
                COUNT(PICK_ORDER)
            FROM
                PICK_ORDER
            WHERE
                PICK_STATUS NOT IN (?, ?, ?)
        ";

        return Minder::getInstance()->findValue($sql, 'DX', 'DC', 'CN');
    }

    protected function _getTotalLines() {
        $sql = "
            SELECT
                COUNT(PICK_ITEM.PICK_LABEL_NO)
            FROM
                PICK_ORDER
                RIGHT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
            WHERE
                PICK_ORDER.PICK_STATUS NOT IN (?, ?, ?)
                AND (PICK_ITEM.PICK_LINE_STATUS NOT IN (?)  OR PICK_ITEM.PICK_LINE_STATUS IS NULL)
        ";

        return Minder::getInstance()->findValue($sql, 'DX', 'DC', 'CN', 'CN');
    }

    protected function _getApprovedForDespatch() {
        $sql = "
            SELECT
                COUNT(PICK_ORDER)
            FROM
                PICK_ORDER
            WHERE
                PICK_STATUS IN (?)
        ";

        return Minder::getInstance()->findValue($sql, 'DA');
    }

    protected function _getApprovedForPicking() {
        $sql = "
            SELECT
                COUNT(DISTINCT PICK_ORDER.PICK_ORDER)
            FROM
                PICK_ORDER
                RIGHT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
            WHERE
                PICK_ORDER.PICK_STATUS IN (?, ?)
                AND PICK_ITEM.PICK_LINE_STATUS IN (?, ?)
        ";

        return Minder::getInstance()->findValue($sql, 'OP', 'DA', 'OP', 'AS');
    }

    protected function _getAwaitingDespatch() {
        $sql = "
            SELECT
                COUNT(DISTINCT PICK_ORDER.PICK_ORDER)
            FROM
                PICK_ORDER
                RIGHT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
            WHERE
                PICK_ORDER.PICK_STATUS IN (?)
                AND PICK_ITEM.PICK_LINE_STATUS IN (?)
        ";

        return Minder::getInstance()->findValue($sql, 'DA', 'PL');
    }

    protected function _getWaitingApproval() {
        $sql = "
            SELECT
                COUNT(PICK_ORDER)
            FROM
                PICK_ORDER
            WHERE
                PICK_STATUS IN (?, ?)
        ";

        return Minder::getInstance()->findValue($sql, 'CF', 'HD');
    }

    protected function _getUnconfirmed() {
        $sql = "
            SELECT
                COUNT(PICK_ORDER)
            FROM
                PICK_ORDER
            WHERE
                PICK_STATUS IN (?)
        ";

        return Minder::getInstance()->findValue($sql, 'UC');
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $rowOffset
     * @param  integer $itemCountPerPage Number of items per page
     * @param  boolean $getPKeysOnly get all fields or primary key expression only
     * @return array
     */
    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false)
    {
        // TODO: Implement getItems() method.

        $result = array(
            $this->getPKeyAlias() => 0,
            'TOTAL_ORDERS' => $this->_getTotalOrders(),
            'TOTAL_LINES' => $this->_getTotalLines(),
            'APPROVED_DESPATCH' => $this->_getApprovedForDespatch(),
            'APPROVED_PICKING' => $this->_getApprovedForPicking(),
            'AWAITING_DESPATCH' => $this->_getAwaitingDespatch(),
            'AWAITING_APPROVAL' => $this->_getWaitingApproval(),
            'UNCONFIRMED' => $this->_getUnconfirmed()
        );

        return array($result);
    }

    /**
     * Remove conditions for query.
     *
     * @param array   $conditions - conditions array. If empty remove all conditions.
     * @return Minder_SysScreen_Model_Interface
     */
    public function removeConditions($conditions = array())
    {
        // not used
    }

    /**
     * Set conditions for query. Replaces existent condotions.
     *
     * @param array   $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function setConditions($conditions = array())
    {
        // not used
    }


}