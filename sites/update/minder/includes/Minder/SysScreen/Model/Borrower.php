<?php

class Minder_SysScreen_Model_Borrower extends Minder_SysScreen_Model {

    public function __construct()
    {
        $this->useDistinct = true;
        parent::__construct();
    }

    protected function _makeConditionForLastAuditedDate($fieldDescription) {
        switch ($fieldDescription['SSV_ALIAS']) {
            case 'LAST_AUDITED_FROM':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' >= ZEROTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            case 'LAST_AUDITED_TILL':
                $conditionString = $this->__getFieldExpression($fieldDescription) . ' <= MAXTIME(?)';
                $conditionArgs   = array($fieldDescription['SEARCH_VALUE']);
                break;
            default:
                return parent::makeConditionsFromSearchField($fieldDescription);
        }
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    protected  function _makeConditionForLockId($fieldDescription) {
        $tmpInputMethod = explode('|', $fieldDescription['SSV_INPUT_METHOD']);
        if (in_array($tmpInputMethod[0], array('IN', 'dl'))) {

            $conditionString = $this->__getFieldExpression($fieldDescription) . " LIKE " . $this->_quote($fieldDescription['SEARCH_VALUE']);
            $conditionArgs   = array();

            return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
        }

        return parent::makeConditionsFromSearchField($fieldDescription);
    }

    protected function makeConditionsFromSearchField($fieldDescription)
    {
        $conditionString = '';
        $conditionArgs   = array();

        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            switch ($fieldDescription['SSV_NAME']) {
                case 'LOCN_ID':
                    return $this->_makeConditionForLockId($fieldDescription);
                case 'LAST_AUDITED_DATE':
                    return $this->_makeConditionForLastAuditedDate($fieldDescription);

                case 'MOVEABLE_LOCN':
                    if ($fieldDescription['SEARCH_VALUE'] == 'EXCLUDE') {
                        $conditionString = $this->__getFieldExpression($fieldDescription) . ' = ?';
                        $conditionArgs   = array('F');
                    }
                    break;

                default:
                    switch ($fieldDescription['SSV_ALIAS']) {
                        case 'EMPTY_LOCATION':
                            if ($fieldDescription['SEARCH_VALUE'] == 'EXCLUDE') {
                                $conditionString = '(ISSN.CURRENT_QTY IS NOT NULL AND ISSN.CURRENT_QTY > 0)';
                                $conditionArgs   = array();
                            }
                            break;
                        default:
                            return parent::makeConditionsFromSearchField($fieldDescription);
                    }
            }
        }
        
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }


    public function selectLocnIdAndWhId($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT LOCATION.LOCN_ID, LOCATION.WH_ID');
    }

    protected function getLocationLabelData($locnId, $whId) {
        $tmpFilters  = $this->_buildExpressionLimit('LOCN_ID', $locnId);
        $tmpFilters += $this->_buildExpressionLimit('WH_ID', $whId);

        $sql  = 'SELECT * FROM LOCATION WHERE (' . implode(' AND ', array_keys($tmpFilters)) . ')';
        $args = array_reduce(array_values($tmpFilters), array($this, '_reduceHelper'), null);

        array_unshift($args, $sql);

        return call_user_func_array(array(Minder::getInstance(), 'fetchAllAssocExt'), $args);
    }

    /**
     * @param Minder_Printer_Abstract $printer
     * @return Minder_JSResponse
     */
    public function printLabel($printer) {
        $printResult = new Minder_JSResponse();
        $rowsAmount = count($this);
        if ($rowsAmount < 1) {
            $printResult->warnings[] = 'Now rows to print.';
            return $printResult;
        }

        $borrowerLabelPrinter = new Minder_LabelPrinter_Borrower();
        return $borrowerLabelPrinter->doPrint(
            $this->selectArbitraryExpression(0, $rowsAmount, 'DISTINCT LOCATION.LOCN_ID, LOCATION.WH_ID'),
            $printer
        );
    }
}
