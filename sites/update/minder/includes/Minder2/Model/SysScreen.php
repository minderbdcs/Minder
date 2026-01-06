<?php

/**
 * @property $settings
 * @property $dbSettings
 * @property $searchResultFields
 * @property $searchResultTabs
 * @property $searchResultActions
 *
 * @property $searchFields
 * @property $searchTabs
 * @property $searchActions
 * 
 * @property string $SS_NAME
 * @property string $RECORD_ID
 * @property boolean $SS_VIEW_BY_DISPLAY
 * @property boolean $SS_PAGE_NO_DISPLAY
 * @property boolean $SS_TITLE_DISPLAY
 * @property boolean $SS_SELECT_COMPLETE_DISPLAY
 * @property boolean $SS_SELECTED_VISIBLE
 * @property boolean $SS_SUMMARY_VISIBLE
 * @property int $SS_SEQUENCE
 * @property int $SLAVE_SCREENS
 *
 * @property string $serviceUrl
 * @property string $exportUrl
 *
 * @property int $rowOffset
 * @property int $showBy
 *
 * @property int _SHOW_BY
 * @property int _PAGE_NO
 * @property string _TITLE
 */
class Minder2_Model_SysScreen extends Minder2_Model implements Minder2_DataSet_Interface {
    const COMPLETE = '__complete';
    const SELECTION_MODE = "selectionMode";

    protected $_session = null;

    /**
     * @var Minder2_DataSet_Interface
     */
    protected $_dataset = null;
    protected $_datasetRegister = null;

    protected $_selectedRows = array();
    protected $_unselectedRows = array();

    /**
     * @var Minder_SysScreen_ModelCondition
     */
    protected $_dependentConditions = null;
    protected $_searchConditions = null;

    protected $_searchResultRows = null;

    protected $_searchFieldValues = array();
    protected $_searchFieldsDescription = array();

    protected $_customMasterSlaveHandler = false;

    function __construct($fields = array())
    {
        parent::__construct($fields);
        $this->SLAVE_SCREENS = array_keys($this->_getMasterSlaveRelations());
    }


    public function initSearchFields($searchFieldsDescription) {
        $this->_searchFieldsDescription = $searchFieldsDescription;
    }

    protected function _getFieldDefaultValue($fieldDescription) {
        return $fieldDescription['SSV_DROPDOWN_DEFAULT'];
    }

    public function getSearchFields() {
        $result = array();
        foreach ($this->_searchFieldsDescription as $fieldDescription) {
            $fieldName = 'SEARCH_FIELD_' . $fieldDescription['RECORD_ID'];
            $result[$fieldName] = $fieldDescription;

            $result[$fieldName]['SEARCH_VALUE'] = isset($this->_searchFieldValues[$fieldName]) ? $this->_searchFieldValues[$fieldName] : $this->_getFieldDefaultValue($fieldDescription);
        }

        return $result;
    }

    public function hasSearchFields() {
        return !empty($this->_searchFieldsDescription);
    }

    public function saveSearchField($fieldName, $fieldValue) {
        $this->saveSearchFields(array($fieldName => $fieldValue));
    }

    public function saveSearchFields($searchFields) {
        $this->_restoreState();
        foreach($searchFields as $fieldName => $fieldValue) {
            $this->_searchFieldValues[$fieldName] = $fieldValue;
        }
        $this->_saveState();
    }

    /*
     * Model Interafce
     */
    /**
     * @return string
     */
    function getName()
    {
        return 'screenModel_' . $this->SS_NAME;
    }

    function __get($name)
    {
        switch ($name) {
            case 'ssName':
                return $this->_getSSName();
            case 'SS_VIEW_BY_DISPLAY':
            case 'SS_PAGE_NO_DISPLAY':
            case 'SS_TITLE_DISPLAY':
            case 'SS_SELECT_COMPLETE_DISPLAY':
            case 'SS_SELECTED_VISIBLE':
            case 'SS_SUMMARY_VISIBLE':
                return $this->_getBooleanFieldsValue($name);
            default:
                return parent::__get($name);
        }
    }

    protected function _getSSName() {
        return $this->SS_NAME;
    }

    public function getRows() {
        return array(
            array('__rowId' => '5', 'PICK_ORDER' => 'TEST ORDER')
        );
    }

    public function storeFieldValue($name, $value) {
        $this->_restoreState();
        $this->setFieldValue($name, $value);
        $this->_saveState();
    }

    /**
     * @return int
     */
    function count()
    {
        return $this->countRows();
    }

    /**
     * @param null|int $rowOffset
     * @param null|int $itemCountPerPage
     * @param null|Minder_SysScreen_ModelCondition $conditions
     * @return array
     */
    public function getItems($rowOffset = null, $itemCountPerPage = null, $conditions = null)
    {
        $this->_restoreState();
        $rowOffset = is_null($rowOffset) ? $this->_getRowOffset() : $rowOffset;
        $itemCountPerPage = is_null($itemCountPerPage) ? $this->_getShowBy() : $itemCountPerPage;

        $items = $this->_dataset->getItems($rowOffset, $itemCountPerPage, $this->_compileConditionObject($conditions));

        foreach ($items as &$item) {
            $item['__selected'] = isset($this->_selectedRows[$item['__rowId']])
                || (isset($this->_selectedRows[self::COMPLETE]) && !isset($this->_unselectedRows[$item['__rowId']]));
        }

        return $items;
    }

    public function getSelectedItems($rowOffset = null, $itemCountPerPage = null) {
        return $this->getItems($rowOffset, $itemCountPerPage, $this->_getSelectedRowsCondition());
    }

    public function makeSearch($searchFields, $rowOffset = null, $itemCountPerPage = null) {
        $this->saveSearchFields($searchFields);

        $items = $this->getItems($rowOffset, $itemCountPerPage);
        $statistics = $this->getStatistics();

        return array('items' => $items, 'fields' => $statistics);
    }

    public function update($rows) {
        return $this->_dataset->update($rows);
    }

    /**
     * @param $sysScreenName
     * @return Minder2_Model_SysScreen
     */
    protected function _getSysScreen($sysScreenName) {
        return Minder2_SysScreen_BuilderLoader::getScreenBuilder($sysScreenName)->build($sysScreenName);
    }

    public function getDependItems($masterSysScreenName, $rows, $rowOffset = null, $itemCountPerPage = null) {
        $masterSysScreen = $this->_getSysScreen($masterSysScreenName);
        $masterSysScreen->syncRowsSelection($rows);

        $this->_setDependentConditions($masterSysScreen->buildDependentConditions($this->SS_NAME, true));

        $items = $this->getItems($rowOffset, $itemCountPerPage);
        $statistics = $this->getStatistics();

        return array('items' => $items, 'fields' => $statistics);
    }

    public function initDependentConditions($masterSysScreenName) {
        $this->_setDependentConditions($this->_getSysScreen($masterSysScreenName)->buildDependentConditions($this->SS_NAME, true));
    }

    public  function fetchFields($fields, $conditions = null, $rowOffset = null, $itemCountPerPage = null, $distinct = false) {
        return $this->_dataset->fetchFields($fields, $conditions, $rowOffset, $itemCountPerPage, $distinct);
    }

    public function fetchAggregatedValue($field, $conditions = null) {
        return $this->_dataset->fetchAggregatedValue($field, $conditions);
    }

    public function buildDependentConditions($slaveSysScreenName, $selectedOnly = true) {
        $conditions = $this->_compileConditionObject();

        if ($selectedOnly) {
            $selectedConditions = $this->_getSelectedRowsCondition();

            if (!empty($selectedConditions)) {
                $conditions->addConditions(array($selectedConditions), Minder_SysScreen_ModelCondition::SELECTED_ROWS_NAMESPACE);
            }
        }

        $result = array();
        $fieldsToFetch = array();
        foreach ($this->_getRelationFields($slaveSysScreenName) as $fkDescription) {
            $fieldsToFetch[] = $fkDescription['MASTER_TABLE'] . '.' . $fkDescription['MASTER_FIELD'];
        }

        $args = array();
        foreach ($this->_dataset->fetchFields($fieldsToFetch, $conditions) as $resultRow) {
            $filter = array();

            foreach ($this->_getRelationFields($slaveSysScreenName) as $fkDescription) {
                $filter[] = $fkDescription['SLAVE_TABLE'] . '.' . $fkDescription['SLAVE_FIELD'] . ' = ?';
                $args[] = $resultRow[$fkDescription['MASTER_FIELD']];
            }

            $result[] = '(' . implode(' AND ', $filter) . ')';
        }

        if (empty($result)) {
            return new Minder_SysScreen_ModelCondition(array('(1 = 2)' => array()));
        }

        return new Minder_SysScreen_ModelCondition(array('(' . implode(' OR ', $result) . ')' => $args));
    }

    protected function _getMasterSlaveRelations() {
        $sysScreenBuilder = new Minder_SysScreen_Builder();
        return $sysScreenBuilder->getSlaveSysScreens($this->SS_NAME);
    }

    protected function _getRelationFields($slaveSysScreenName) {
        $slaveSysScreens = $this->_getMasterSlaveRelations();

        if (!isset($slaveSysScreens[$slaveSysScreenName]))
            return array();

        return $slaveSysScreens[$slaveSysScreenName];
    }

    public function makeFindConditions($rows, $exclude = false)
    {
        return $this->_dataset->makeFindConditions($rows, $exclude);
    }


    protected function _getSelectedRowsCondition() {
        if (empty($this->_selectedRows))
            return new Minder_SysScreen_ModelCondition(array('1 = 2' => array()), Minder_SysScreen_ModelCondition::SELECTED_ROWS_NAMESPACE);


        if (isset($this->_selectedRows[self::COMPLETE])) {
            if (empty($this->_unselectedRows))
                return null;

            return new Minder_SysScreen_ModelCondition(array($this->makeFindConditions($this->_unselectedRows, true)), Minder_SysScreen_ModelCondition::SELECTED_ROWS_NAMESPACE);
        }

        return new Minder_SysScreen_ModelCondition(array($this->makeFindConditions($this->_selectedRows)), Minder_SysScreen_ModelCondition::SELECTED_ROWS_NAMESPACE);
    }

    protected function _getSearchCondition() {
        $searchFields = array();
        foreach($this->getSearchFields() as $fieldDescription) {
            if (!empty($fieldDescription['SEARCH_VALUE']))
                $searchFields[] = $fieldDescription;
        }

        return $this->makeConditionsFromSearch($searchFields);
    }

    /**
     * @param array $searchFieldsDescription
     * @return Minder_SysScreen_ModelCondition
     */
    public function makeConditionsFromSearch($searchFieldsDescription)
    {
        return $this->_dataset->makeConditionsFromSearch($searchFieldsDescription);
    }


    public function countRows($extraConditions = null)
    {
        return $this->_dataset->countRows($this->_compileConditionObject($extraConditions));
    }

    protected function _compileConditionObject($conditions = null)
    {
        $conditionObject = new Minder_SysScreen_ModelCondition();
        $conditionObject->addConditions(array($this->_getSearchCondition()), Minder_SysScreen_ModelCondition::SEARCH_NAMESPACE);
        $conditionObject->addConditions(array($this->_getDependentConditions()), Minder_SysScreen_ModelCondition::DEPENDENT_NAMESPACE);

        if (!is_null($conditions))
            $conditionObject->addConditions(array($conditions));

        return $conditionObject;
    }

    public function selectComplete($selected = true) {
        if (is_string($selected))
            $selected = ($selected == 'true') ? true : false;

        $this->restoreState();
        if ($selected) {
            $this->_selectedRows[self::COMPLETE] = true;
            $this->_unselectedRows = array();
        } else {
            $this->_selectedRows = array();
            $this->_unselectedRows = array();
        }
        $this->saveState();
    }

    protected function _setSelectionMode($selectionMode) {
        $this->storeFieldValue(self::SELECTION_MODE, $selectionMode);
        return $this;
    }

    protected function _getSelectionMode() {
        $result = $this->_getFieldValue(self::SELECTION_MODE);
        return $result == 'one' ? 'one' : 'all';
    }

    public function selectRows($rows, $selected = true, $selectionMode = 'all') {
        if (empty($rows))
            return;

        if (is_string($selected))
            $selected = ($selected == 'true') ? true : false;

        $selectionMode = $this->_setSelectionMode($selectionMode)->_getSelectionMode();

        $this->_restoreState();
        if ($selectionMode == 'one') {
            $this->_selectedRows = array();
            $this->_unselectedRows = array();
        }

        if ($selected) {
            foreach ($rows as $row) {
                $this->_selectedRows[$row['__rowId']] = $row['__rowId'];
                if (isset($this->_unselectedRows[$row['__rowId']]))
                    unset($this->_unselectedRows[$row['__rowId']]);
            }
        } else {
            foreach ($rows as $row) {
                $this->_unselectedRows[$row['__rowId']] = $row['__rowId'];
                if (isset($this->_selectedRows[$row['__rowId']]))
                    unset($this->_selectedRows[$row['__rowId']]);
            }
        }
        $this->_saveState();
    }

    public function syncRowsSelection($rows) {
        $selectedRows = array();
        $unselectedRows = array();

        foreach ($rows as $row) {
            $selected = isset($row['__selected']) ? (is_bool($row['__selected']) ? $row['__selected'] : (strtolower($row['__selected']) == 'true' ? true : false)) : false;

            if ($selected)
                $selectedRows[] = $row;
            else
                $unselectedRows[] = $row;
        }

        $this->selectRows($selectedRows, true);
        $this->selectRows($unselectedRows, false);
    }

    public function setDataSet(Minder2_DataSet_Interface $dataSet) {
        $this->_dataset = $dataSet;
        return $this;
    }

    /**
     * @return array
     */
    public function getState() {
        return array(
            'selectedRows' => $this->_selectedRows,
            'unselectedRows' => $this->_unselectedRows,
            'fields' => $this->_fields,
            'dependentConditions' => $this->_getDependentConditions(),
            'searchConditions' => $this->_getSearchCondition(),
            'searchFieldValues' => $this->_searchFieldValues
        );
    }

    /**
     * @param array $state
     * @return Minder2_Model_SysScreen
     */
    public function setState($state) {
        $this->_selectedRows   = $state['selectedRows'];
        $this->_unselectedRows = $state['unselectedRows'];
        $this->_dependentConditions = $state['dependentConditions'];
        $this->_searchConditions    = $state['searchConditions'];
        $this->_searchFieldValues   = isset($state['searchFieldValues']) ? $state['searchFieldValues'] : array();
        $this->setFields($state['fields']);
        return $this;
    }

    /**
     * @return string
     */
    public function getStateId()
    {
        return 'SYS_SCREEN-' . $this->SS_NAME;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return intval($this->SS_SEQUENCE, 10);
    }

    protected function _setDependentConditions($conditions)
    {
        $this->_restoreState();
        $this->_dependentConditions = $conditions;
        $this->saveState();
    }

    public function getStatistics()
    {
        return array(
            '_TOTAL_ROWS' => count($this),
            '_SELECTED_ROWS' => $this->getSelectedRowsAmount()
        );
    }

    public function getSelectedRowsAmount()
    {
        return $this->countRows($this->_getSelectedRowsCondition());
    }

    protected function _getRowOffset()
    {
        $pageNo = empty($this->_PAGE_NO) ? 1 : $this->_PAGE_NO;
        return $this->_getShowBy() * ($pageNo - 1);
    }

    protected function _getShowBy() {
        return empty($this->_SHOW_BY) ? 5 : $this->_SHOW_BY;
    }

    protected function _getDependentConditions()
    {
        if (is_null($this->_dependentConditions))
            return new Minder_SysScreen_ModelCondition();

        return $this->_dependentConditions;
    }

    protected function _mapRowId($row) {
        return $row['__rowId'];
    }

    protected function _mapRowsId($rows) {
        return array_map(array($this, '_mapRowId'), $rows);
    }

    public function useCustomMasterSlaveHandler() {
        $this->_customMasterSlaveHandler = true;
    }

    public function hasCustomMasterSlaveHandler() {
        return $this->_customMasterSlaveHandler;
    }
}