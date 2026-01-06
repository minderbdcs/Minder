<?php

interface Minder2_DataSet_Interface extends Countable {

    /**
     * @abstract
     * @param null|int $rowOffset
     * @param null|int $itemCountPerPage
     * @param null|Minder_SysScreen_ModelCondition $conditions
     * @return array
     */
    public function getItems($rowOffset = null, $itemCountPerPage = null, $conditions = null);

    public function fetchFields($fields, $conditions = null, $rowOffset = null, $itemCountPerPage = null, $distinct = false);

    public function fetchAggregatedValue($field, $conditions = null);

    public function countRows($extraConditions = null);

    /**
     * @abstract
     * @param $rows
     * @param bool $exclude
     * @return Minder_SysScreen_ModelCondition
     */
    public function makeFindConditions($rows, $exclude = false);

    /**
     * @abstract
     * @param array $searchFieldsDescription
     * @return Minder_SysScreen_ModelCondition
     */
    public function makeConditionsFromSearch($searchFieldsDescription);

    public function update($rows);
}