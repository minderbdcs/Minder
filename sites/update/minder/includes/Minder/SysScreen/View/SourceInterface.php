<?php

interface Minder_SysScreen_View_SourceInterface extends
    Minder_SysScreen_Model_MasterSlaveInterface,
    Minder_SysScreen_Model_ConditionInterface,
    Minder_SysScreen_Model_OrderInterface
{
    /**
     * @param $limit
     * @param bool $withOrder
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getSelectQuery($limit, $withOrder = true);

    public function setFields($fields);

    public function setPrimaryKeys($keys);

    public function reorderResultFields($newOrder);

    /**
     * @param $limit
     * @return Minder_SysScreen_View_QueryPart
     */
    public function getKeysQuery($limit);

    /**
     * @return Minder_SysScreen_View_QueryPart[]
     */
    public function getCountQueries();

    public function getPrimaryKeyAlias();

    public function getName();

    public function setSearchFields($fields);

    public function getAllResultFields();

    public function init();
}