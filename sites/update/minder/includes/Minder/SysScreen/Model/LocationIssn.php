<?php

class Minder_SysScreen_Model_LocationIssn extends Minder_SysScreen_Model_ProdProfile_Abstract {
    const PRODUCT_CONDITION = 'PRODUCT_CONDITION';

    public function __construct()
    {
        $this->useDistinct = false;
        parent::__construct();
    }

    /**
     * Used to select PROD_ID from model rows.
     * Each child model should implement this method, as it needed
     * by several common methods
     *
     * @param mixed $rowOffset
     * @param mixed $itemCountPerPage
     */
    public function selectProdId($rowOffset, $itemCountPerPage)
    {
        $prodId = array();

        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ISSN.PROD_ID');
        if (is_array($result) && count($result) > 0)
            $prodId = array_map(create_function('$item', 'return $item["PROD_ID"];'), $result);

        return $prodId;
    }


    protected function _buildLocationLimit($locnId, $whId) {
        $tmpFilters = array();
        $tmpFilters += $this->_buildExpressionLimit('ISSN.LOCN_ID', $locnId);
        $tmpFilters += $this->_buildExpressionLimit('ISSN.WH_ID', $whId);

        return array('(' . implode(' AND ', array_keys($tmpFilters)) . ')' => array_reduce(array_values($tmpFilters), array($this, '_reduceHelper'), null));
    }

    public function setLocationLimit($locations) {
        $tmpConditions = array();
        $tmpArgs       = array();
        foreach ($locations as $tmpRow) {
            $tmpLimit        = $this->_buildLocationLimit($tmpRow['LOCN_ID'], $tmpRow['WH_ID']);
            $tmpConditions[] = key($tmpLimit);
            $tmpArgs         = array_merge($tmpArgs, current($tmpLimit));
        }

        $this->setConditions(array('(' . implode(' OR ', $tmpConditions) . ')' => $tmpArgs));
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->conditions));
    }

    protected function _getSsnIds($rowOffset, $itemCountPerPage) {
        $ssnIds = array();

        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT ISSN.SSN_ID');
        if (is_array($result) && count($result) > 0)
            $ssnIds = array_map(create_function('$item', 'return $item["SSN_ID"];'), $result);

        return $ssnIds;
    }

    /**
     * @param Minder_Printer_Abstract $printerObj
     * @return stdClass
     */
    public function printIssnLabel($printerObj) {
        $result = new stdClass();
        $result->messages = array();
        $result->errors   = array();

        $ssns = $this->_getSsnIds(0, count($this));

        if (count($ssns) < 1)
            return $result;

        $issnLabelPrinter = new Minder_LabelPrinter_Issn();
        $result = $issnLabelPrinter->doPrint($ssns, $printerObj);
        return $result;
    }

    protected function _makeProductSearch($fieldDescription) {
        $conditionString = '';
        $conditionArgs   = array();

        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            $conditionString = 'ISSN.PROD_ID = ?';
            $conditionArgs[] = $fieldDescription['SEARCH_VALUE'];
        }

        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }

    public function makeProductSearch($searchFields) {
        $conditions = array();
        foreach ($searchFields as $fieldDescription) {
            if ($fieldDescription['SSV_NAME'] == 'PROD_ID') {
                list($tmpCondStr, $tmpCondArgs) = $this->_makeProductSearch($fieldDescription);
            }

            if (!empty($tmpCondStr))
                $conditions[$tmpCondStr] = $tmpCondArgs;
        }
       
        return $conditions;
    }

    public function addProductCondition($condition) {
        $productFilter = $this->getConditionObject()->addConditions($condition, self::PRODUCT_CONDITION);
        $this->setConditionObject($productFilter);
    }

    public function removeProductCondition() {
        $this->getConditionObject()->deleteConditions(self::PRODUCT_CONDITION);
    }

    protected function _selectProdIdAndCompanyId($rowOffset, $itemCountPerPage)
    {
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, "
            DISTINCT
                ISSN.PROD_ID,
                (
                    SELECT FIRST 1
                        COMPANY_ID
                    FROM
                        PROD_PROFILE
                    WHERE
                        PROD_PROFILE.PROD_ID = ISSN.PROD_ID
                        AND (
                            PROD_PROFILE.COMPANY_ID = ISSN.COMPANY_ID
                            OR (
                                PROD_PROFILE.COMPANY_ID = 'ALL'
                                AND NOT EXISTS (SELECT PROD_ID FROM PROD_PROFILE AS SUB_PP WHERE SUB_PP.PROD_ID = PROD_PROFILE.PROD_ID AND SUB_PP.COMPANY_ID = ISSN.COMPANY_ID)
                            )
                        )

                ) AS COMPANY_ID
        ");

        return is_array($result) ? $result : array();
    }
}