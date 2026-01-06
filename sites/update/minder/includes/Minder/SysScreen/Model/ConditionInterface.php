<?php

interface Minder_SysScreen_Model_ConditionInterface {
    /**
     * Set conditions for query. Replaces existent conditions.
     *
     * @param array   $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function setConditions($conditions = array());

    public function getConditions();

    /**
     * Add conditions for query.
     *
     * @param array   $conditions - conditions array
     * @return Minder_SysScreen_Model_Interface
     */
    public function addConditions($conditions = array());

    /**
     * Remove conditions for query.
     *
     * @param array   $conditions - conditions array. If empty remove all conditions.
     * @return Minder_SysScreen_Model_Interface
     */
    public function removeConditions($conditions = array());

    public function makeConditionsFromSearch($searchFields = array());

    public function makeConditionsFromId($ids = '', $exlude = false);

    public function setStaticConditions($conditions = array());
}