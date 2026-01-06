<?php

/**
 * @throws Minder_SysScreen_Model_Exception
 * @property string mainTable
 */
class Minder_SysScreen_Model implements Minder_SysScreen_Model_Interface
{
    const MAX_RECORDS = 10000;
    const NULL_UUID = 'CSDQQmPVEeOLbZTegGUa9g'; //UUID('0920d042-63d5-11e3-8b6d-94de80651af6')

    protected static $pKeySeparator = '{-}';
    protected $pkeysExpressionAlias = 'PKEY_EXPR';
    
//    protected $minder               = null;

    protected $useDistinct          = true;
    protected $conditions           = array();

    /**
     * @var Minder_SysScreen_ModelCondition
     */
    protected $_conditionObject     = null;
    
    protected $staticConditions     = array();
    protected $fields               = array();
    protected $tables               = array();
    protected $pkeys                = array();
    protected $order                = array();
    protected $_customOrder         = array();
    protected $group                = array();
    protected $colorFields          = array();
    protected $summary              = array();
    protected $whTable              = array();
    protected $companyTable         = array();

    protected $_limitCompanyId      = null;
    protected $_limitWhId           = null;

    /**
     * @var Minder
     */
    protected $_minder = null;

    /**
     * @var Minder_SysScreen_DataSource_Sql
     */
    protected $_dataSource = null;

    /**
     * @var Minder_SysScreen_DataSource_SystemParameterProvider
     */
    protected $_parameterProvider = null;

    /**
     * @var Minder_SysScreen_ModelCondition
     */
    protected $_tmpMSConditions;

    public function __construct() {
//        $this->minder = Minder::getInstance();
    }

    /**
     * Add extra initialization here
     */
    public function init() {}
    
    public function __set($key, $value) {
        switch ($key) {
            case 'fields': 
            case 'pkeys': 
            case 'order': 
            case 'group': 
            case 'colorFields':
            case 'tables':
                $this->$key = $value;
                break;
            case 'mainTable':
                if ($tableDesc = $this->_tableExists($value)) {
                    $minder = Minder::getInstance();
                    $fieldList = $minder->getFieldList($value);

                    if (isset($fieldList['WH_ID'])) {
                        $this->whTable[$tableDesc['SST_ALIAS']] = $tableDesc['SST_ALIAS'];
                    }

                    if (isset($fieldList['COMPANY_ID'])) {
                        $this->companyTable[$tableDesc['SST_ALIAS']] = $tableDesc['SST_ALIAS'];
                    }
                }
                break;

        }
        
        return $value;
    }
    
    public function getPKeyAlias() {
        return $this->pkeysExpressionAlias;
    }
    
    /**
    * Set conditions for query. Replaces existent conditions.
    * 
    * @param array   $conditions - conditions array({CONDITION => array({CONDITION_ARGS})})
    *                              Example:  array(
    *                                           'WH_ID = ?'                 => array('ZW'),
    *                                           'PICK_ORDER_TYPE IN (?, ?)' => array('SO', 'TO')
    *                                        )
    * @return Minder_SysScreen_Model_Interface
    */
    public function setConditions($conditions = array()) {
        if (!is_array($conditions))
            throw new Minder_SysScreen_Model_Exception('Bad conditions array.');
        
        $minder = Minder::getInstance();
        if($minder->isNewDateCalculation() == false){
            foreach($conditions as $strCondition => $arrValues){

                if (strpos(strtolower($strCondition), "zerotime(") !== false || strpos(strtolower($strCondition), "maxtime(") !== false) {
                    foreach($arrValues as $intKey => $strValue){
                        /*if(DateTime::createFromFormat('Y-m-d H:i:s', $strValue)!== false  || DateTime::createFromFormat('Y-m-d',$strValue)!== false) {*/
                        if($minder->isValidDate($strValue)) {
                            $arrValues[$intKey] = $minder->getFormatedDateToDb($strValue, "", false);
                        }
                        else{
                            $arrValues[$intKey] = $strValue;
                        }
                    }
                    $conditions[$strCondition] = $arrValues;
                }
            }
        }

        //echo "<pre>"; die(print_r($conditions));

        $this->conditions = $conditions;
        
        return $this;
    }
    
    /**
    * Add conditions for query.
    * 
    * @param array   $conditions - conditions array({CONDITION => array({CONDITION_ARGS})}).
    *                              Example:  array(
    *                                           'WH_ID = ?'                 => array('ZW'),
    *                                           'PICK_ORDER_TYPE IN (?, ?)' => array('SO', 'TO')
    *                                        )
    * @return Minder_SysScreen_Model_Interface
    */
    public function addConditions($conditions = array()) {
        if (!is_array($conditions))
            throw new Minder_SysScreen_Model_Exception('Bad conditions array.');
            
        $this->conditions += $conditions;
        
        return $this;
    }
    
    /**
    * Set static conditions for query. Replaces existent conditions.
    * 
    * @param array   $conditions - conditions array({CONDITION => array({CONDITION_ARGS})})
    *                              Example:  array(
    *                                           'WH_ID = ?'                 => array('ZW'),
    *                                           'PICK_ORDER_TYPE IN (?, ?)' => array('SO', 'TO')
    *                                        )
    * @return Minder_SysScreen_Model_Interface
    */
    public function setStaticConditions($conditions = array()) {
        if (!is_array($conditions))
            throw new Minder_SysScreen_Model_Exception('Bad static conditions array.');

        $this->staticConditions = $conditions;
        
        return $this;
    }
    
    /**
    * Add static conditions for query.
    * 
    * @param array   $conditions - conditions array({CONDITION => array({CONDITION_ARGS})}).
    *                              Example:  array(
    *                                           'WH_ID = ?'                 => array('ZW'),
    *                                           'PICK_ORDER_TYPE IN (?, ?)' => array('SO', 'TO')
    *                                        )
    * @return Minder_SysScreen_Model_Interface
    */
    public function addStaticConditions($conditions = array()) {
        if (!is_array($conditions))
            throw new Minder_SysScreen_Model_Exception('Bad static conditions array.');
            
        if (!is_array($this->staticConditions))
            $this->staticConditions = array();
            
            
        $this->staticConditions += $conditions;
        
        return $this;
    }
    
    /**
    * Remove conditions for query.
    * 
    * @param array   $conditions - conditions array({CONDITION => array({CONDITION_ARGS})}). If empty remove all conditions.
    *                              Example:  array(
    *                                           'WH_ID = ?'                 => array('ZW'),
    *                                           'PICK_ORDER_TYPE IN (?, ?)' => array('SO', 'TO')
    *                                        )
    * @return Minder_SysScreen_Model_Interface
    */
    public function removeConditions($conditions = array()) {
        if (!is_array($conditions))
            throw new Minder_SysScreen_Model_Exception('Bad conditions array.');
            
        if (count($conditions) < 1) {
            $this->conditions = array();
        } else {
            foreach ($conditions as $condition => $value) {
                if (isset($this->conditions[$condition]))
                    unset($this->conditions[$condition]);
            }
        }

        return $this;
    }
    
    public function getConditions() {
        return $this->conditions;
    }

    /**
     * @param Minder_SysScreen_ModelCondition $conditionObject
     * @return Minder_SysScreen_Model
     */
    public function setConditionObject($conditionObject) {
        $this->_conditionObject = $conditionObject;

        return $this;
    }

    /**
     * @return Minder_SysScreen_ModelCondition
     */
    public function getConditionObject() {
        return is_null($this->_conditionObject) ? new Minder_SysScreen_ModelCondition() : $this->_conditionObject;
    }

    public function setLimitCompanyId($val = null) {
        $this->_limitCompanyId = $val;
        return $this;
    }

    public function setLimitWhId($val = null) {
        $this->_limitWhId = $val;
        return $this;
    }

    protected function _getMinderCompanyLimit() {
        $result = array();
        $minder = $this->_getMinder();

        if ($minder->limitCompany != 'all') {
            $result[$minder->limitCompany] = $minder->limitCompany;
        } else {
            if (!($minder->isAdmin || $minder->isInventoryOperator)) {
                $result = array_merge($result, $minder->getCompanyListLimited());
                if (!is_array($result))
                    $result = array();
            }
        }

        return $result;
    }

    protected function _getCompanyLimit() {
        return empty($this->_limitCompanyId) ? $this->_getMinderCompanyLimit() : array($this->_limitCompanyId => $this->_limitCompanyId);
    }

    protected function _getMinderWhLimit() {
        $result = array();
        $minder = $this->_getMinder();

        if ($minder->limitWarehouse != 'all') {
            $result[$minder->limitWarehouse] = $minder->limitWarehouse;
        } else {
            $result = array_keys($minder->getWarehouseListLimited());
        }

        return $result;
    }

    protected function _getWhLimit() {
        return empty($this->_limitWhId) ? $this->_getMinderWhLimit() : array($this->_limitWhId => $this->_limitWhId);
    }

    /**
     * @return array
     */
    protected function __getWhAndCompanyConditions() {
        $companyLimit = $this->_getCompanyLimit();
        $whLimit      = $this->_getWhLimit();
        $whAndCompanyConditions = array();

        //user can see record if record WH_ID and COMPANY_ID in limited list or
        //WH_ID and COMPANY_ID is null
        //(if we dont'n check for NULL, User wan't see rows from dataset combined from
        //several tables, as there is often only part of data available and other part is NULL)
        $companyLimitCount = count($companyLimit);
        if ($companyLimitCount > 0) {
            foreach ($this->companyTable as $tableAlias) {
                $tmpCond = '(' . $tableAlias . '.COMPANY_ID IS NULL OR ' . $tableAlias . '.COMPANY_ID = \'\' OR ' . $tableAlias . '.COMPANY_ID in (' . substr(str_repeat('?, ', $companyLimitCount), 0, -2) . '))';
                $whAndCompanyConditions[$tmpCond] = $companyLimit;
            }
        }

        $whLimitCount      = count($whLimit);
        if ($whLimitCount > 0) {
            foreach ($this->whTable as $tableAlias) {
                $tmpCond = '(' . $tableAlias . '.WH_ID IS NULL OR ' . $tableAlias . '.WH_ID = \'\' OR ' . $tableAlias . '.WH_ID in (' . substr(str_repeat('?, ', $whLimitCount), 0, -2) . '))';
                $whAndCompanyConditions[$tmpCond] = $whLimit;
            }
        }

        return $whAndCompanyConditions;
    }

    protected function _getParameters($expression) {
        $result = array();
        $foundMatches = array();
        if (preg_match_all('/(%\w+%)/', $expression, $foundMatches)) {
            $result = $foundMatches[1];
        }

        return $result;
    }

    protected function _formatStaticConditions($conditions) {
        $result = array();

        foreach ($conditions as $condition => $args) {
            $parameters = $this->_getParameters($condition);
            $tmpCondition = $this->_formatExpressionWithParameters($condition, $parameters);
            $result[$tmpCondition] = $args;
        }

        return $result;
    }
    
    /**
    * Return where string and arguments array
    * 
    * @return array(string $conditionString, array $conditionArgs)
    */
    protected function __getWhereAndArgs() {
        $conditionObject = $this->getConditionObject();
        $tmpConditions = array();
        $tmpArgs = array();
        $tmpString = $conditionObject->compileWhereString($tmpArgs);
        if (!empty($tmpString))
            $tmpConditions[$tmpString] = $tmpArgs;

        $tmpConditions       = array_merge($tmpConditions, $this->conditions, $this->_formatStaticConditions($this->staticConditions), $this->__getWhAndCompanyConditions());
//        $tmpConditions       = array_merge($this->conditions, $this->staticConditions);
        $tmpMergeFunction    = create_function('$data, $item', '$data = is_array($data) ? $data : array(); return array_merge($data, array_values($item));');
        $filters             = array_keys($tmpConditions);
        $conditionString     = '';
        if (count($filters) > 0) {
            $conditionString = ' WHERE ' . implode(' AND ', $filters);
        }
            
        $conditionArgs       = array_reduce($tmpConditions, $tmpMergeFunction, null);
        $conditionArgs       = (is_array($conditionArgs)) ? $conditionArgs : array();
        
        return array($conditionString, $conditionArgs);
    }

    public static function getFullName($field) {
        //if no SSV_NAME raise an error
        if (empty($field['SSV_NAME']))
            throw new Minder_SysScreen_Model_Exception("Sys Screen Var #" . $field['RECORD_ID'] . " at " . $field['SS_NAME'] . " has no SSV_NAME.");

        if (empty($field['SSV_TABLE'])) {
            return strtoupper($field['SSV_NAME']);
        } else {
            return strtoupper($field['SSV_TABLE'] . '.' . $field['SSV_NAME']);
        }
    }
    
    protected function __getFieldName($field) {
        return static::getFullName($field);
    }

    protected function __getFieldExpression($field) {
        if (empty($field['SSV_EXPRESSION'])) {
            
            //if no SSV_EXPRESSION and no SSV_NAME raise an error
            if (empty($field['SSV_NAME']))
                throw new Minder_SysScreen_Model_Exception("Sys Screen Var #" . $field['RECORD_ID'] . " at " . $field['SS_NAME'] . " has no SSV_NAME and no SSV_EXPRESSION.");
            
            if (empty($field['SSV_TABLE'])) {
                return strtoupper($field['SSV_NAME']);
            } else {
                return strtoupper($field['SSV_TABLE'] . '.' . $field['SSV_NAME']);
            }
        } else {
            return $this->_formatExpressionWithParameters($field['SSV_EXPRESSION'], $field['EXPRESSION_PARAMS']);
        }
    }
    
    protected function __getPKeyExpression($field) {
        if (empty($field['SSV_EXPRESSION'])) {
            
            //if no SSV_EXPRESSION and no SSV_NAME raise an error
            if (empty($field['SSV_NAME']))
                throw new Minder_SysScreen_Model_Exception("Sys Screen Var #" . $field['RECORD_ID'] . " at " . $field['SS_NAME'] . " has no SSV_NAME and no SSV_EXPRESSION.");
            
            if (empty($field['SSV_TABLE'])) {
                return strtoupper($field['SSV_NAME']);
            } else {
                return strtoupper($field['SSV_TABLE'] . '.' . $field['SSV_NAME']);
            }
        } else {
            return "COALESCE((" . $field['SSV_EXPRESSION'] . "), (" . $field['SSV_EXPRESSION'] . "), '" . static::NULL_UUID . "')";
        }
    }

    public static function getFieldAlias($field) {
        if (empty($field['SSV_ALIAS'])) {

            //if no SSV_ALIAS and no SSV_NAME raise an error
            if (empty($field['SSV_NAME']))
                throw new Minder_SysScreen_Model_Exception("Sys Screen Var #" . $field['RECORD_ID'] . " at " . $field['SS_NAME'] . " has no SSV_NAME and no SSV_ALIAS.");

            return strtoupper($field['SSV_NAME']);
        } else {
            return strtoupper($field['SSV_ALIAS']);
        }
    }
    
    protected function __getFieldAlias($field) {
        return static::getFieldAlias($field);
    }
    
    protected function __getFieldExpressionWithAlias($field) {
//        if (empty($field['SSV_ALIAS'])) {
//            
            //if no SSV_ALIAS and no SSV_NAME raise an error
//            if (empty($field['SSV_NAME']))
//                throw new Minder_SysScreen_Model_Exception("Sys Screen Var #" . $field['RECORD_ID'] . " at " . $field['SS_NAME'] . " has no SSV_NAME and no SSV_ALIAS.");
//            
//            return $this->__getFieldExpression($field) . ' AS ' . strtoupper($field['SSV_NAME']);
//        } else {
//            return $this->__getFieldExpression($field) . ' AS ' . strtoupper($field['SSV_ALIAS']);
//        }
        return $this->__getFieldExpression($field) . ' AS ' . $this->__getFieldAlias($field);
    }

    protected function _getExpressionParameterValue($paramName) {
        $paramName = strtoupper($paramName);

        $minder = Minder::getInstance();
        switch ($paramName) {
            case 'COMPANY_FILTER_STRING':
            case '%COMPANY_FILTER_STRING%':
                return $minder->getCompanyFilterString();
            case 'WH_FILTER_STRING':
            case '%WH_FILTER_STRING%':
                return $minder->getWarehouseFilterString();
            case 'CURRENT_USER_ID':
            case '%CURRENT_USER_ID%':
                return $minder->userId;
            default:
                return $paramName;
//                throw new Minder_SysScreen_Model_Exception('Unsupported Table Parameter: "' . $paramName . '".');
        }
    }

    protected function _fillExpressionParameters($parametersList) {
        $result = array();

        foreach ($parametersList as $paramName) {
            $result[$paramName] = $this->_quote($this->_getExpressionParameterValue($paramName));
        }

        return $result;
    }
    
    protected function _formatExpressionWithParameters($expression, $parametersList) {
        $filledParametersList = $this->_fillExpressionParameters($parametersList);
        return str_replace(array_keys($filledParametersList), array_values($filledParametersList), $expression);
    }

    protected function __getTableExpression($table) {
        if ($table['SST_PROCEDURE'] == 'T') {
            $tmpTable = $table['SST_TABLE'] . '(' . implode(', ', $this->_fillExpressionParameters($table['TABLE_PARAMS'])) . ')';
        } else {
            $tmpTable = $table['SST_TABLE'];
        }

        return ' ' . $table['SST_JOIN'] . ' ' . $tmpTable . ' AS ' . $table['SST_ALIAS'] . ' ' . $table['SST_VIA'];
    }
    
    public function getPrimaryIdExpression() {          
        return '(' . implode(" || '" . self::$pKeySeparator . "' || ", array_map(array($this, '__getPKeyExpression'), $this->pkeys)) . ')';
    }
    
    protected function _sortCallback($a, $b) {
        return $a[$a["ORDER_BY_FIELD_NAME"]] - $b[$b["ORDER_BY_FIELD_NAME"]];
    }

    protected function _getDistinct() {
        return ($this->useDistinct) ? 'DISTINCT' : '';
    }


    public function hasRecords() {
        $log = Minder_Registry::getLogger()->startDetailedLog(__METHOD__);
        $sql = 'SELECT FIRST 1 * FROM ' . $this->getFromExpression();
        list($where, $args) = $this->__getWhereAndArgs();

        $sql .= $where;

        $start = microtime(true);
        array_unshift($args, $sql);

        $minder = Minder::getInstance();
        try {
            $result = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
            $end = microtime(true);
            $this->_logQuery(array_shift($args), $args, $end - $start);
            $log->info(__METHOD__);
        } catch (Exception $e) {
            $this->_logQuery(array_shift($args), $args, 0);
            $log->error(__METHOD__);
            throw $e;
        }
        return count($result) > 0;
    }
    
    /**
    * Return elements count.
    * 
    * @return int
    */
    public function count() {
//        $tmpTables = $this->tables;
//        usort($tmpTables, array($this, '_sortCallback'));
        $log = Minder_Registry::getLogger()->startDetailedLog(__METHOD__);

        $countSQL = 'SELECT count(' . $this->_getDistinct() . ' ' . $this->getPrimaryIdExpression() . ') FROM ' . $this->getFromExpression();
        list($where, $args) = $this->__getWhereAndArgs();
        
        $countSQL .= $where;

        $start = microtime(true);
        array_unshift($args, $countSQL);

        $minder = Minder::getInstance();
        try {
            $result = call_user_func_array(array($minder, 'findValue'), $args);
            $end = microtime(true);
            $this->_logQuery(array_shift($args), $args, $end - $start);
            $log->info(__METHOD__);
        } catch (Exception $e) {
            $this->_logQuery(array_shift($args), $args, 0);
            $log->error(__METHOD__);
            throw $e;
        }
        return $result;
    }
    
    public function getFromExpression() {
        $tmpTables = $this->tables;
        usort($tmpTables, array($this, '_sortCallback'));
        
        return implode(array_map(array($this, '__getTableExpression'), $tmpTables));
    }
    
    protected function _getLimitExpression($rowOffset, $itemCountPerPage) {
        $rowOffset        = (int)$rowOffset;
        //TODO: get limits from options table
        $tmpSkipExpr      = (empty($rowOffset) ||  $rowOffset < 0) ? '' : ' SKIP ' . $rowOffset;
        $itemCountPerPage = (int)$itemCountPerPage;
        $tmpFirstExpr     = (empty($itemCountPerPage) || $itemCountPerPage < 0) ? ' FIRST 20' : ' FIRST ' . $itemCountPerPage;

        return $tmpFirstExpr . $tmpSkipExpr;
    }

    protected function _orderSortHelper($a, $b) {
        return $a["SSO_SEQUENCE"] - $b["SSO_SEQUENCE"];
    }

    protected function _orderByMapper($order) {
        return ltrim(str_ireplace('order by ', '', $order['SSO_ORDER']), ', ');
    }

    protected function _getOrderByExpression() {
        usort($this->order, array($this, '_orderSortHelper'));
        $result = implode(', ', array_merge($this->_customOrder, array_map(array($this, '_orderByMapper'), $this->order)));
        return empty($result) ? '' : 'ORDER BY ' . $result;
    }

    protected function _groupSortHelper($a, $b) {
        return $a["SSG_SEQUENCE"] - $b["SSG_SEQUENCE"];
    }

    protected function _groupByMapper($group) {
        return ltrim(str_ireplace('group by ', '', $group['SSG_GROUP']), ', ');
    }

    protected function _getGroupByExpression() {
        usort($this->group, array($this, '_groupSortHelper'));
        $result = implode(', ', array_map(array($this, '_groupByMapper'), $this->group));
        return empty($result) ? '' : 'GROUP BY ' . $result;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Rows offset
     * @param  integer $itemCountPerPage Number of items per page
     * @param  boolean $getPKeysOnly get all fields or primary key expression only
     * @return array
     */
    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false) {
        $log = Minder_Registry::getLogger()->startDetailedLog(__METHOD__);
        $tmpFieldsSet =  array($this->getPrimaryIdExpression() . ' AS ' . $this->pkeysExpressionAlias);
        
        $colorArgs = array();
        
        if ($getPKeysOnly)
            $tmpFieldsSet = array_merge($tmpFieldsSet, array_map(array($this, '__getFieldExpressionWithAlias'), $this->pkeys));
        else {
            $tmpFieldsSet = array_merge($tmpFieldsSet, array_map(array($this, '__getFieldExpressionWithAlias'), $this->fields), array_map(array($this, '__getFieldExpressionWithAlias'), $this->colorFields));
            $colorArgs    = array_reduce(array_map(create_function('$el', 'return $el["ARGS"];'), $this->colorFields), create_function('$res, $item', '$res = (is_array($res)) ? $res : array(); return array_merge($res, $item);'), null);
            $colorArgs    = (is_array($colorArgs)) ? $colorArgs : array();
        }
//        $tmpTables = $this->tables;
//        usort($tmpTables, array($this, '_sortCallback'));

        list($where, $args) = $this->__getWhereAndArgs();
        $args             = array_merge($colorArgs, $args);

        $selectSQL = 'SELECT ' . $this->_getLimitExpression($rowOffset, $itemCountPerPage) . ' ' . $this->_getDistinct() . ' ' . implode(', ', $tmpFieldsSet) . ' FROM ' . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression() . ' ' . $this->_getOrderByExpression();
        $start = microtime(true);
        array_unshift($args, $selectSQL);

        $minder = Minder::getInstance();

        try {
            $fetchedRows = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
            $end = microtime(true);
            $this->_logQuery(array_shift($args), $args, $end - $start);
        } catch (Exception $e) {
            $this->_logQuery(array_shift($args), $args, 0);
            $log->error(__METHOD__);
            throw $e;
        }
        $result = array();
        
        if (is_array($fetchedRows)) {
            foreach ($fetchedRows as $row){
                foreach($row as $key => $value){
                    if (DateTime::createFromFormat('Y-m-d H:i:s', $value) !== FALSE || DateTime::createFromFormat('Y/m/d H:i:s', $value) !== FALSE) {
                        $row[$key] = $minder->getFormatedDate($value);                           
                    }
                    if (DateTime::createFromFormat('Y-m-d', $value) !== FALSE || DateTime::createFromFormat('Y/m/d', $value) !== FALSE) {
                        $row[$key] = $minder->getFormatedDate($value, "Y-m-d");                             
                    }
                }
                $result[$row[$this->pkeysExpressionAlias]] = $row;
            }
        }
        $log->info(__METHOD__);
        return $result;
    }
    
    public function selectArbitraryExpression($rowOffset, $itemCountPerPage, $expression) {
        list($where, $args) = $this->__getWhereAndArgs();
        $selectSQL = 'SELECT ' . $this->_getLimitExpression($rowOffset, $itemCountPerPage) . ' ' . $expression . ' FROM ' . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression() . ' ' . $this->_getOrderByExpression();
        array_unshift($args, $selectSQL);

        $minder = Minder::getInstance();
        $fetchedRows = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
        $result = array();
        
        if (is_array($fetchedRows)) {
            foreach ($fetchedRows as $row)
                $result[] = $row;
        }
        
        return $result;
    }

    public function setCustomOrderFields($sortFields = array()) {
        $this->_customOrder = array();
        foreach ($sortFields as $sortField) {
            $alias = $this->__getFieldExpression($sortField['sortField']);
            $this->_customOrder[] = $alias . ' ' . $sortField['sortOrder'];
        }

        return $this;
    }

    public function setOrder(array $value = array())
    {
        return $this->order = $value;
    }

    /**
    * Select arbitrary fieldset from model tables with tables names.
    * Can be used for printing labels
    * 
    * @param mixed $rowOffset
    * @param mixed $itemCountPerPage
    * @param mixed $expression
    * 
    * @return array
    */
    public function selectArbitraryDataExt($rowOffset, $itemCountPerPage, $expression) {
        list($where, $args) = $this->__getWhereAndArgs();
        $selectSQL = 'SELECT ' . $this->_getLimitExpression($rowOffset, $itemCountPerPage) . ' ' . $expression . ' FROM ' . $this->getFromExpression() . $where . ' ' . $this->_getGroupByExpression() . ' ' . $this->_getOrderByExpression();
        array_unshift($args, $selectSQL);
        $minder = Minder::getInstance();
        $fetchedRows = call_user_func_array(array($minder, 'fetchAllAssocExt'), $args);
        $result = array();
        
        if (is_array($fetchedRows)) {
            foreach ($fetchedRows as $row)
                $result[] = $row;
        }
        
        return $result;
    }
    
    /**
    * Experimental. Don't use.
    * 
    * @return array - deleted records ID's
    */
    public function delItems() {
        if (count($this->tables) > 1)
            throw new Minder_SysScreen_Model_Exception('Method is unimplemented.');
            
        list($where, $args) = $this->__getWhereAndArgs();
        $selectQuery = 'SELECT ' . $this->getPrimaryIdExpression() . ' AS ' . $this->pkeysExpressionAlias . ' FROM ' . implode(', ', $this->tables) . $where;
        array_unshift($args, $selectQuery);
        
        $minder = Minder::getInstance();
        $selectedRows = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
        
        $deletedRows  = array();
        foreach ($selectedRows as $row) {
            $deletedRows[$row[$this->pkeysExpressionAlias]] = $row;
        }
        
        $deleteQuery = 'DELETE FROM ' . implode(', ', $this->tables) . $where;
        if (false === ($query = ibase_prepare($deleteQuery)))
            throw new Minder_SysScreen_Model_Exception('Error deleting records. Reason: ' . ibase_errmsg());
            
        array_shift($args);
        array_unshift($args, $query);
        if (false === call_user_func_array('ibase_execute', $args)) {
            $errmsg = ibase_errmsg();
            ibase_free_query($query);
            throw new Minder_SysScreen_Model_Exception('Error deleting records. Reason: ' . $errmsg);
        }

        return $deletedRows;
    }
    
    public function getSearchFields() {
        return $this->fields;
    }

    protected function _quote($value) {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $value = str_replace("'", "''", $value);
        return "'" . $value . "'";
    }

    protected function addWildcardsToLikeSearchParams($originalValue, $wildcardType = null) {
        if (!isset($wildcardType)) {
            return '%' . trim($originalValue, '%') . '%';
        }
        else {
            switch($wildcardType) {
                case Minder_Page_FormBuilder_InputMethod::WILDCARD_OFF:
                    return $originalValue;
                    break;

                case Minder_Page_FormBuilder_InputMethod::WILDCARD_RIGHT:
                    return rtrim($originalValue, '%') . '%';
                    break;

                case Minder_Page_FormBuilder_InputMethod::WILDCARD_LEFT:
                    return '%' . ltrim($originalValue, '%');
                    break;

                case Minder_Page_FormBuilder_InputMethod::WILDCARD_BOTH:
                    return '%' . trim($originalValue, '%'). '%';
                    break;

                default:
                    return '%' . trim($originalValue, '%'). '%';
            }
        }
    }
    
    /**
    * Creates model condition from single search field description
    * 
    * @param array $fieldDescription
    * 
    * @return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs)
    */
    protected function makeConditionsFromSearchField($fieldDescription) {
        $conditionString = '';
        $conditionArgs   = array();
        
        if (isset($fieldDescription['PICK_PARAM'])) {
            
            $minder = Minder::getInstance();

            $mode           = $fieldDescription['PICK_PARAM']['PICK_PROC'];
            $order_types    = $fieldDescription['PICK_PARAM']['PICK_PARAM_TYPE'];
            $order_modes    = $fieldDescription['PICK_PARAM']['PICK_PARAM_MODE'];
            $orders         = $fieldDescription['PICK_PARAM']['PICK_PARAM_ORDNO'];
            $order_statuses = $fieldDescription['PICK_PARAM']['PICK_PARAM_STATUS'];
            $order_prioritys= $fieldDescription['PICK_PARAM']['PICK_PARAM_PRIORITY'];
            $ids            = $fieldDescription['PICK_PARAM']['PICK_PARAM_ID'];
            $one_or_accept  = $fieldDescription['PICK_PARAM']['ONE_OR_ACCEPT'];
            
            $pickModes     = $minder->getPickModes();
            foreach ($pickModes['data'] as $pickMode) {
                $pickModesList[$pickMode['PICK_MODE_NO']] = $pickMode['DESCRIPTION'];
            }
            $order_types = 'SO';
            $order_modes = array_search($order_modes, $pickModesList);
            
            if ($orders =='' ) {
                $orders = 'GETALL';
            }
            if ($order_statuses =='' ) {
                $order_statuses = 'GETALL';
            }
            if ($order_prioritys =='' ) {
                $order_prioritys = 'GETALL';
            }
            if ($ids =='' ) {
                $ids = 'GETALL';
            }
            
            // #416
            foreach($pickModes['data'] as $pickMode){
                if($pickMode['PICK_MODE_NO'] == $mode){
                    $mode = $pickMode['PROCEDURE_NAME'];
                    break;
                }
            }
            $filterdIds = $minder->pickMode($mode, $order_types, $order_modes, $orders, $order_statuses, $order_prioritys, $ids);
            $ordersList = array();

            $counter = 0;
            foreach($filterdIds['data'] as $value ) {
                $ordersList[$value['WK_ORDER']] = $value['WK_ORDER'];
                $counter++;
                if ($counter>1450) {
                    break;    
                }
            }
            
            if (count($ordersList) > 0) {
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' IN (' . substr(str_repeat('?, ', count($ordersList)), 0, -2) . ')';
                $conditionArgs   = $ordersList;
            } else {
                $conditionString = '1=2';
                $conditionArgs   = array();
            }
        } else {
            if (!empty($fieldDescription['SEARCH_VALUE'])) {
                $inputParser = new Minder_Page_FormBuilder_InputMethodParcer();
                $parseResult = $inputParser->parse($fieldDescription['SSV_INPUT_METHOD']);

                switch ($parseResult->inputMethod) {
                    case Minder_Page_FormBuilder_InputMethod::INPUT :
                    case Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_VALUESLIST:
                        $tmpCond = $this->__getFieldExpression($fieldDescription) . " LIKE " .
                                   $this->_quote($this->addWildcardsToLikeSearchParams($fieldDescription['SEARCH_VALUE'], $parseResult->wildcardType));
                        break;
                    case Minder_Page_FormBuilder_InputMethod::DATE_PICKER :
                        switch ($parseResult->dateType) {
                            case Minder_Page_FormBuilder_InputMethod::DATE_FROM:
                                $tmpCond = $this->__getFieldExpression($fieldDescription) . ' >= ZEROTIME(?)';
                                break;
                            case Minder_Page_FormBuilder_InputMethod::DATE_TILL:
                                $tmpCond = $this->__getFieldExpression($fieldDescription) . ' <= MAXTIME(?)';
                                break;
                            default:
                                $tmpCond = $this->__getFieldExpression($fieldDescription) . ' BETWEEN ZEROTIME(?) AND MAXTIME(?)';
                        }
                        break;
                    case Minder_Page_FormBuilder_InputMethod::DROP_DOWN :
                    case Minder_Page_FormBuilder_InputMethod::RADIO_GROUP :
                        $tmpCond = $this->__getFieldExpression($fieldDescription) . ' = ?';
                        break;
                    case Minder_Page_FormBuilder_InputMethod::GLOBAL_INPUT :
                        $tmpCond = $this->__getFieldName($fieldDescription) . ' = ?';
                        break;
                    case Minder_Page_FormBuilder_InputMethod::READ_ONLY :
                    case Minder_Page_FormBuilder_InputMethod::NONE :
                    case Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_NAME:
                        $tmpCond = '';
                        break;
                    default:
                        throw new Minder_SysScreen_Model_Exception("Unsupported SSV_INPUT_METHOD='" . $fieldDescription['SSV_INPUT_METHOD'] . "' in Sys Screen Var #" . $fieldDescription['RECORD_ID'] . ".");
                }
        
                if (is_array($fieldDescription['SEARCH_VALUE'])) {
                    $conditionString = $tmpCond;
                    
                    if ($parseResult->inputMethod == Minder_Page_FormBuilder_InputMethod::INPUT || $parseResult->inputMethod == Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_VALUESLIST )
                        $conditionArgs   = array();
                    else 
                        $conditionArgs   = $fieldDescription['SEARCH_VALUE'];
                } else {
                    $conditionString = $tmpCond;
                    if ($parseResult->inputMethod == Minder_Page_FormBuilder_InputMethod::INPUT || $parseResult->inputMethod == Minder_Page_FormBuilder_InputMethod::COMPLEX_DROPDOWN_VALUESLIST )
                        $conditionArgs   = array();
                    elseif ($parseResult->inputMethod == Minder_Page_FormBuilder_InputMethod::DATE_PICKER)
                        $conditionArgs   = array($fieldDescription['SEARCH_VALUE'], $fieldDescription['SEARCH_VALUE']);
                    else 
                        $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                }
            }
            
        }
        
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }
    
    /**
    * Create conditions array which can be used with model from search fields.
    * 
    * @param array $searchFields - array of search fields to use for conditions
    * 
    * @return array - model conditions
    */
    public function makeConditionsFromSearch($searchFields = array()) {
        $conditions = array();
        foreach ($searchFields as $fieldDescription) {
            list($tmpCondStr, $tmpCondArgs) = $this->makeConditionsFromSearchField($fieldDescription);
            if (!empty($tmpCondStr))
                $conditions[$tmpCondStr] = $tmpCondArgs;
        }
        
        return $conditions;
    }
    
    protected function _mapPrimaryIdValue($value) {
        $tmpValueArray = explode(self::$pKeySeparator, $value);
        
        if (count($tmpValueArray) != count($this->pkeys)) 
            throw new Minder_SysScreen_Model_Exception('Screen Model expexts ' . count($this->pkeys) . ' primary key values but ' . count($tmpValueArray) . ' given.');
            
        $map = array();
        reset($tmpValueArray);
        foreach ($this->pkeys as $pkeyDescription) {
            list($key, $val) = each($tmpValueArray);
            $map[$this->__getFieldExpression($pkeyDescription)] = $val;
        }
        
        return $map;
    }
    
    protected function _makePrimaryIdConditionFromMap($map) {
        $tmpConditionArray = array();
        $tmpConditionArgs  = array();
        foreach ($map as $key => $value) {
            if ($value === static::NULL_UUID) {
                $tmpConditionArray[] = '(' . $key . ' IS NULL)';
            } else {
                $tmpConditionArray[] = '(' . $key . ' = ?)';
                $tmpConditionArgs[] = $value;
            }
        }
        
        $tmpConditionString = implode(' AND ', $tmpConditionArray);
        
        return array($tmpConditionString => $tmpConditionArgs);
    }
    
    public function makeConditionsFromId($ids = '', $exlude = false) {
        if (!is_array($ids))
            $ids = array($ids);
        
        $tmpConditionArray  = array_map(array($this, '_makePrimaryIdConditionFromMap'), array_map(array($this, '_mapPrimaryIdValue'), $ids));
        $tmpConditionString = "(" . implode(' OR ', array_map(create_function('$item', 'list($key, $val) = each($item); return $key;'), array_values($tmpConditionArray))) . ")";
        $tmpConditionArgs   = array_reduce(array_values($tmpConditionArray), create_function('$res, $item', '$res = is_array($res) ? $res : array(); list($key, $val) = each($item); return array_merge($res, $val);'), null);
        
        if ($exlude) {
            $tmpConditionString = ' NOT ' . $tmpConditionString;
        }
        
        return array($tmpConditionString => $tmpConditionArgs);
    }
    
    public function getFields() {
        return $this->fields;
    }
    
    /**
    * Experimental
    * 
    */
    public function getNewRecord() {
        $record = array();
        
        foreach ($this->fields as $fieldDescription) {
            $record[$fieldDescription['NAME']] = '';
        }
        
        return array($record);
    }
    
    /**
    * Calculates default values to use in new records for model
    * 
    */
    public function getRecordDefaults() {
        $defaults = array();
        
        foreach ($this->fields as $fieldDesc) {
            $tmpFieldAlias            = $this->__getFieldAlias($fieldDesc);
            $defaults[$tmpFieldAlias] = '';
            
            if (empty($fieldDesc['SSV_DROPDOWN_DEFAULT']))
                continue;
            
            try {
                $defaults[$tmpFieldAlias] = $this->_getFieldDefaultValue($fieldDesc);
            } catch (Exception $e) {
                throw new Minder_SysScreen_Model_Exception('Error geting default value for Sys Screen Var #' . $fieldDesc['RECORD_ID'] . ': ' . $e->getMessage());
            }
        }
        
        return $defaults;
    }

    /**
     * @return Minder
     */
    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getFieldDefaultValue($fieldDesc)
    {
        $dataSource = $this->_getDataSource();
        $dataSource->sql = $fieldDesc['SSV_DROPDOWN_DEFAULT'];

        return $dataSource->fetchOne($this->_getParameterProvider());
    }

    /**
     * @return Minder_SysScreen_DataSource_Sql
     */
    protected function _getDataSource() {
        if (is_null($this->_dataSource))
            $this->_dataSource = new Minder_SysScreen_DataSource_Sql();

        return $this->_dataSource;
    }

    /**
     * @return Minder_SysScreen_DataSource_SystemParameterProvider
     */
    protected function _getParameterProvider() {
        if (is_null($this->_parameterProvider))
            $this->_parameterProvider = new Minder_SysScreen_DataSource_SystemParameterProvider();

        return $this->_parameterProvider;
    }

    protected function _tableExists($tableName) {
        if (!is_array($tableName)) {
            $tableName = array($tableName);
        }
        
        foreach ($this->tables as $table) {
            if (in_array($table['SST_TABLE'], $tableName)) {
                return $table;
            }
        }
        return false;
    }

    protected function _tableAliasExists($tableAlias) {
        if (!is_array($tableAlias)) {
            $tableAlias = array($tableAlias);
        }
        
        foreach ($this->tables as $table) {
            if (in_array($table['SST_ALIAS'], $tableAlias)) {
                return $table;
            }
        }
        return false;
    }

    protected function _aliasExists($aliasName) {
        if (!is_array($aliasName)) {
            $aliasName = array($aliasName);
        }
        foreach ($this->fields as $field) {
            if (in_array($field['SSV_ALIAS'], $aliasName)) {
                return $field;
            }
        }
        return false;
    }

    protected function _fieldExists($fieldName) {
        if (!is_array($fieldName)) {
            $fieldName = array($fieldName);
        }
        foreach ($this->fields as $field) {
            if (in_array($field['SSV_NAME'], $fieldName)) {
                return $field;
            }
        }
        return false;
    }

//---------------------------------- 
//Some service methods
//---------------------------------- 
    
    public function getOrderLinesCount() {
        if ((($poFieldDesc = $this->_fieldExists('PICK_ORDER')) === false) ||  (($polnFieldDesc = $this->_fieldExists('PICK_ORDER_LINE_NO')) === false))
            return 0;
        
//        $tmpTables = $this->tables;
//        usort($tmpTables, array($this, '_sortCallback'));
        
        $poField = $poFieldDesc['SSV_NAME'];
        if (!empty($poFieldDesc['SSV_TABLE'])) {
            $poField = $poFieldDesc['SSV_TABLE']  . '.' . $poField;
        }

        $polnField = $polnFieldDesc['SSV_NAME'];
        if (!empty($polnFieldDesc['SSV_TABLE'])) {
            $polnField = $polnFieldDesc['SSV_TABLE'] . '.' . $polnField;
        }

        $countSQL = 'SELECT count(' . $this->_getDistinct() . ' ' . $poField . ' || ' . $polnField. ') FROM ' . $this->getFromExpression();
        list($where, $args) = $this->__getWhereAndArgs();
        
        $countSQL .= $where;
        
        array_unshift($args, $countSQL);
        $minder = Minder::getInstance();
        $result = call_user_func_array(array($minder, 'findValue'), $args);
        return $result;
    }
    
    public function getProductCodesCount() {
        if ((($fieldDesc = $this->_fieldExists('PROD_ID')) === false))
            return 0;

        $fieldName = $fieldDesc['SSV_NAME'];
        if (!empty($fieldDesc['SSV_TABLE'])) {
            $fieldName = $fieldDesc['SSV_TABLE'] . '.' . $fieldName;
        }

//        $tmpTables = $this->tables;
//        usort($tmpTables, array($this, '_sortCallback'));

        $countSQL = 'SELECT count(' . $this->_getDistinct() . ' ' . $fieldName . ') FROM ' . $this->getFromExpression();
        list($where, $args) = $this->__getWhereAndArgs();
        $countSQL .= $where;
        
        array_unshift($args, $countSQL);
        $minder = Minder::getInstance();
        $result = call_user_func_array(array($minder, 'findValue'), $args);
        return $result;
    }
    
    public function getISSNsCount() {
        if ((($fieldDesc = $this->_fieldExists('SSN_ID')) === false))
            return 0;

//        $tmpTables = $this->tables;
//        usort($tmpTables, array($this, '_sortCallback'));

        $fieldName = $fieldDesc['SSV_NAME'];
        if (!empty($fieldDesc['SSV_TABLE'])) {
            $fieldName = $fieldDesc['SSV_TABLE'] . '.' . $fieldName;
        }

        $countSQL = 'SELECT count(' . $this->_getDistinct() . ' ' . $fieldName . ') FROM ' . $this->getFromExpression();
        list($where, $args) = $this->__getWhereAndArgs();
        
        $countSQL .= $where;
        
        array_unshift($args, $countSQL);
        $minder = Minder::getInstance();
        $result = call_user_func_array(array($minder, 'findValue'), $args);
        return $result;
    }
    
    public function getAggregateValue($aggregateExpression = '') {
        $aggregateSQL = 'SELECT ' . $aggregateExpression . ' FROM ' . $this->getFromExpression();
        list($where, $args) = $this->__getWhereAndArgs();
        
        $aggregateSQL .= $where;
        
        array_unshift($args, $aggregateSQL);
        $minder = Minder::getInstance();
        $result = call_user_func_array(array($minder, 'findValue'), $args);
        return $result;
    }
    
    public function selectSummary($summaryList = array()) {
        $summaryList = is_array($summaryList) ? $summaryList : array($summaryList);
        
        $summaryToFetch = array();
        
        foreach ($this->summary as $id => $desc) {
            if (empty($summaryList) || in_array($desc['SSS_NAME'], $summaryList))
                $summaryToFetch[$desc['SSS_NAME']] = '(' . $desc['SSS_EXPRESSION'] . ') AS ' . $desc['SSS_NAME'];
        }
        
        $result = array();
        
        if (empty($summaryToFetch))
            return $result;
            
        $summarySQL = 'SELECT ' . implode(', ', $summaryToFetch) . ' FROM ' . $this->getFromExpression();
        
        list($where, $args) = $this->__getWhereAndArgs();
        
        $summarySQL .= $where;
        
        array_unshift($args, $summarySQL);
        $minder = Minder::getInstance();
        $result = call_user_func_array(array($minder, 'fetchAssoc'), $args);
        return $result;
    }

    protected function _reduceHelper($initial, $current) {
        $initial = is_array($initial) ? $initial : array();
        return array_merge($initial, $current);
    }

    protected function _buildExpressionLimit($expression, $value) {
        if (empty($value))
            return array("($expression IS NULL OR $expression = '')" => array());
         else
            return array("($expression = ?)" => array($value));
    }

    public function getSelectedIdList($rowOffset, $selectedCount)
    {
        return $this->selectArbitraryExpression($rowOffset, $selectedCount, 'PICK_DESPATCH.DESPATCH_ID');
    }

    protected function _logQuery($query, $arguments, $time) {
        $this->_getQueryLog()->logQuery($query, $arguments, $time);
        return $this;
    }

    /**
     * @return Minder_SysScreen_QueryLog
     */
    protected function _getQueryLog () {
        return Minder_SysScreen_QueryLog::getInstance();
    }

    public function removeMasterSelectionConditions() {
        $conditions = $this->getConditionObject();
        $conditions->deleteMasterSelectionConditions();
        $this->setConditionObject($conditions);
        return $this;
    }

    public function setEmptyMasterSelectionConditions() {
        $conditions = $this->getConditionObject();
        $conditions->addMasterSelectionConditions(array('1 = 2' => array()));
        $this->setConditionObject($conditions);
        return $this;
    }

    public function createEmptyMasterSelectionConditions()
    {
        return array('1 = 2' => array());
    }

    public function addMasterSelectionConditions($conditions) {
        $conditionObject = $this->getConditionObject();
        $conditionObject->addMasterSelectionConditions($conditions);
        $this->setConditionObject($conditionObject);
        return $this;
    }

    public function createMasterSelectionConditions($relation, $filterValues) {
        $slaveExpression  = (empty($relation['SLAVE_TABLE']))  ? $relation['SLAVE_FIELD']  : $relation['SLAVE_TABLE']  . '.' . $relation['SLAVE_FIELD'];
        return array($slaveExpression . ' IN (' . substr(str_repeat('?, ', count($filterValues)), 0, -2) . ')' => $filterValues);
    }

    public function selectForeignKeyValues($relation, $offset, $limit) {
        $masterExpression = (empty($relation['MASTER_TABLE'])) ? $relation['MASTER_FIELD'] : $relation['MASTER_TABLE'] . '.' . $relation['MASTER_FIELD'];
        return $this->selectArbitraryExpression($offset, $limit, 'DISTINCT ' . $masterExpression);
    }

    /**
     * @param string $mode
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function startMasterSelectionConditions($mode = Minder_SysScreen_ModelCondition::OPERATOR_OR) {
        $this->_tmpMSConditions = new Minder_SysScreen_ModelCondition(array(), Minder_SysScreen_ModelCondition::DEFAULT_NAMESPACE, $mode);
        return $this;
    }

    /**
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function applyMasterSelectionConditions()
    {
        $conditionObject = $this->getConditionObject();
        $conditionObject->deleteMasterSelectionConditions();

        if (!empty($this->_tmpMSConditions)) {
            $conditionObject->addMasterSelectionConditions(array($this->_tmpMSConditions));
        }

        $this->setConditionObject($conditionObject);
        $this->_tmpMSConditions = null;
        return $this;
    }

    /**
     * @param $relation
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function addEmptyMasterSelectionConditions($relation)
    {
        $this->_tmpMSConditions->addConditions(array('1 = 2' => array()));
        return $this;
    }

    /**
     * @param $relation
     * @param $filterValues
     * @return Minder_SysScreen_Model_MasterSlaveInterface
     */
    public function createAndAddMasterSelectionConditions($relation, $filterValues)
    {
        $conditions = $this->createMasterSelectionConditions($relation, $filterValues);
        $this->_tmpMSConditions->addConditions($conditions, 'RELATION_' . $relation['RECORD_ID']);
        return $this;
    }


}

class Minder_SysScreen_Model_Exception extends Minder_Exception {}