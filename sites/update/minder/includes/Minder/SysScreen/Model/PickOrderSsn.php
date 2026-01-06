<?php
class Minder_SysScreen_Model_PickOrderSsn extends Minder_SysScreen_Model
{
    public function __construct() {
        parent::__construct();

        if (!is_array($this->summary))
            $this->summary = array();
            
        $this->summary = array_merge(
            $this->summary, 
            array(
                'STATIC_1' => array(
                    'SSS_NAME' => 'TOTAL_QTY',
                    'SSS_EXPRESSION' => 'SUM(COALESCE(ISSN.CURRENT_QTY, 0))'
                )
            )
        );
        
        if (!is_array($this->staticConditions))
            $this->staticConditions = array();
            
        $this->staticConditions = array_merge(
            $this->staticConditions,
            array(
                '(ISSN.ISSN_STATUS IN (?, ?, ?))' => array('ST', 'PA', 'UP')
            )
        );
        
    }

    public function initServiceFields() {
        if ($this->_tableExists('ISSN')) {
            $this->fields['__CURRENT_QTY'] = array(
                'RECORD_ID' => '__CURRENT_QTY',
                'SSV_NAME' => 'CURRENT_QTY',
                'SSV_ALIAS' => 'FIELD__CURRENT_QTY',
                'SSV_TABLE' => 'ISSN',
                'SSV_INPUT_METHOD' => 'NONE'
            );
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

        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $fieldDescription));
        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            switch ($fieldDescription['SSV_NAME']) {
                case 'SSN_DESCRIPTION':
                    $parserObj =    new Parser($fieldDescription['SEARCH_VALUE'], 'SSN_DESCRIPTION', 'SSN', '');
                    break;
                case 'ISSN_DESCRIPTION':
                    $parserObj =    new Parser($fieldDescription['SEARCH_VALUE'], 'ISSN_DESCRIPTION', 'ISSN', '');
                    break;
                default:
                    return parent::makeConditionsFromSearchField($fieldDescription);
            }
        
            $conditionString = '(' . $parserObj->parse() . ')';
            $conditionArgs   = array(); //empty array as we have no args
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $conditionString, $conditionArgs));
        } else {
            return parent::makeConditionsFromSearchField($fieldDescription);
        }
        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    /**
    * Select some data from ISSN and SSN tables for adding PICK_ITEM into PICK_ORDER
    * 
    * @returns array
    */
    public function getIssnDataForPickItem() {
        $result = array();
        $totalRows = count($this);
        
        if ($totalRows < 1)
            return $result;

        $issnIds = $this->selectSsnId(0, $totalRows);

        $sql = "
            SELECT 
                ISSN.SSN_ID,
                ISSN.PROD_ID,
                ISSN.CURRENT_QTY,
                PROD_PROFILE.SALE_PRICE,
                SSN.SSN_SALE_PRICE 
            FROM 
                ISSN 
                LEFT JOIN SSN ON ISSN.ORIGINAL_SSN = SSN.SSN_ID
                LEFT JOIN PROD_PROFILE ON ISSN.PROD_ID = PROD_PROFILE.PROD_ID
            WHERE 
                ISSN.SSN_ID IN (" . substr(str_repeat('?, ', count($issnIds)), 0, -2) . ")";
        
        array_unshift($issnIds, $sql);
        
        $minder = Minder::getInstance();
        
        $issns = call_user_func_array(array($minder, 'fetchAllAssoc'), $issnIds);
        
        foreach ($issns as $issnDetails) {
            $result[$issnDetails['SSN_ID']] = $issnDetails;
        }
        return $result;
    }


    public function selectSsnId($rowOffset, $totalRows)
    {
        return array_map(create_function('$item', 'return $item["SSN_ID"];'), $this->selectArbitraryExpression($rowOffset, $totalRows, 'DISTINCT ISSN.SSN_ID'));
    }
}

class Minder_SysScreen_Model_PickOrderSsn_Exception extends Minder_SysScreen_Model_Exception {}
