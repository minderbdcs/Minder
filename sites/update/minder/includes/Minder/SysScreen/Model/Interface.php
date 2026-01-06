<?php
interface Minder_SysScreen_Model_Interface extends Countable,
    Minder_SysScreen_Model_MasterSlaveInterface,
    Minder_SysScreen_Model_ConditionInterface,
    Minder_SysScreen_Model_OrderInterface
{
    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Rows offset
     * @param  integer $itemCountPerPage Number of items per page
     * @param  boolean $getPKeysOnly get all fields or primary key expression only
     * @return array
     */
    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false);

    public function getPKeyAlias();


}
