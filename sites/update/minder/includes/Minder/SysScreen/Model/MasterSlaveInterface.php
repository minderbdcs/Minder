<?php

interface Minder_SysScreen_Model_MasterSlaveInterface {
    public function removeMasterSelectionConditions();

    public function setEmptyMasterSelectionConditions();

    public function createEmptyMasterSelectionConditions();

    public function addMasterSelectionConditions($conditions);

    public function createMasterSelectionConditions($relation, $filterValues);

    public function selectForeignKeyValues($relation, $offset, $limit);


    /**
     * @param string $mode
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function startMasterSelectionConditions($mode = Minder_SysScreen_ModelCondition::OPERATOR_OR);

    /**
     * @param $relation
     * @param $filterValues
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function createAndAddMasterSelectionConditions($relation, $filterValues);

    /**
     * @param $relation
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function addEmptyMasterSelectionConditions($relation);

    /**
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function applyMasterSelectionConditions();
}