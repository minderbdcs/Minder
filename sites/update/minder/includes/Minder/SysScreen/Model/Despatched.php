<?php

class Minder_SysScreen_Model_Despatched extends Minder_SysScreen_Model {
    protected function _makeConditionForPickedExit($fieldDescription) {
        switch ($fieldDescription['SSV_ALIAS']) {
            case 'PICKD_EXIT_FROM':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' >= ZEROTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            case 'PICKD_EXIT_TILL':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' <= MAXTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            default:
                return parent::makeConditionsFromSearchField($fieldDescription);
        }
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    protected function makeConditionsFromSearchField($fieldDescription)
    {
        $conditionString = '';
        $conditionArgs   = array();

        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            switch ($fieldDescription['SSV_NAME']) {
                case 'PICKD_EXIT':
                    return $this->_makeConditionForPickedExit($fieldDescription);

                default:
                    return parent::makeConditionsFromSearchField($fieldDescription);
            }
        }

        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

}