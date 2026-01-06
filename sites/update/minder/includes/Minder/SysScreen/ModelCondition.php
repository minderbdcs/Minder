<?php

class Minder_SysScreen_ModelCondition {
    const OPERATOR_AND = 'AND';
    const OPERATOR_OR  = 'OR';

    const DEFAULT_NAMESPACE       = 'default';
    const SELECTED_ROWS_NAMESPACE = 'SELECTED_ROWS_NAMESPACE';
    const SEARCH_NAMESPACE        = 'SEARCH_NAMESPACE';
    const DEPENDENT_NAMESPACE     = 'DEPENDENT_NAMESPACE';

    protected $_operator   = Minder_SysScreen_ModelCondition::OPERATOR_AND;
    protected $_conditions = array();

    function __construct($conditions = array(), $namespace = Minder_SysScreen_ModelCondition::DEFAULT_NAMESPACE, $operator = Minder_SysScreen_ModelCondition::OPERATOR_AND)
    {
        $this->_operator = $operator;
        $this->addConditions($conditions, $namespace);
    }


    public function addConditions($conditions = array(), $namespace = Minder_SysScreen_ModelCondition::DEFAULT_NAMESPACE) {
        if (empty($conditions))
            return $this;

        $this->_conditions[$namespace] = (isset($this->_conditions[$namespace])) ? $this->_conditions[$namespace] : array();
        $this->_conditions[$namespace] = array_merge($this->_conditions[$namespace], $conditions);
        return $this;
    }

    public function addMasterSelectionConditions($conditions = array()) {
        return $this->addConditions($conditions, static::DEPENDENT_NAMESPACE);
    }

    public function getConditions($namespace = null) {
        if (is_null($namespace))
            return $this->_conditions;

        return isset($this->_conditions[$namespace]) ? $this->_conditions[$namespace] : array();
    }

    public function deleteConditions($namespace = null) {
        if (is_null($namespace)) {
            $this->_conditions = array();
            return $this;
        }

        if (isset($this->_conditions[$namespace]))
            unset($this->_conditions[$namespace]);

        return $this;
    }

    public function deleteMasterSelectionConditions() {
        return $this->deleteConditions(static::DEPENDENT_NAMESPACE);
    }

    public function compileWhereString(&$args) {
        $tmpArgs = array();
        $tmpStringArray = array();

        foreach ($this->getConditions() as $conditions) {
            foreach ($conditions as $condition => $conditionArgs) {
                if ($conditionArgs instanceof Minder_SysScreen_ModelCondition) {
                    $innerArgs = array();
                    /**
                     * @var Minder_SysScreen_ModelCondition $conditionArgs
                     */
                    $tmpConditionString = $conditionArgs->compileWhereString($innerArgs);

                    if (!empty($tmpConditionString)) {
                        $tmpStringArray[] = $tmpConditionString;
                        $tmpArgs = array_merge($tmpArgs, $innerArgs);
                    }
                } else {
                    $tmpStringArray[] = $condition;
                    $tmpArgs = array_merge($tmpArgs, $conditionArgs);
                }
            }
        }

        $args = array_merge($args, $tmpArgs);

        if (empty($tmpStringArray))
            return '';
        
        return '(' . implode(' ' . $this->_operator . ' ', $tmpStringArray) . ')';
    }
}