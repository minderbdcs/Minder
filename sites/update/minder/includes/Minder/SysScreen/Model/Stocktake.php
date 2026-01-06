<?php

class Minder_SysScreen_Model_Stocktake extends Minder_SysScreen_Model {
    protected function makeConditionsFromSearchField($fieldDescription)
    {
        $conditionString = '';
        $conditionArgs   = array();

        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            switch ($fieldDescription['SSV_NAME']) {
                case 'ST_AUDIT_DATE':
                    return $this->_makeConditionForLastAuditedDate($fieldDescription);
                default:
                    return parent::makeConditionsFromSearchField($fieldDescription);
            }
        }

        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    private function _makeConditionForLastAuditedDate($fieldDescription)
    {
        switch ($fieldDescription['SSV_ALIAS']) {
            case 'START_DATE':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' >= ZEROTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            case 'END_DATE':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' <= MAXTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            default:
                return parent::makeConditionsFromSearchField($fieldDescription);
        }
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    public function getOriginalSsnIds($rowOffset, $itemsCountPerPage) {
        $result = $this->selectArbitraryExpression($rowOffset, $itemsCountPerPage, 'DISTINCT ISSN.ORIGINAL_SSN');

        return array_map(create_function('$item', 'return $item["ORIGINAL_SSN"];'), $result);
    }

}