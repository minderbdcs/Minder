<?php
class Minder_SysScreen_Model_WaitingPickingLine extends Minder_SysScreen_Model_AbstractProduct implements Minder_OrderAllocator_ItemProvider_Interface
{
    public function getProductCodesCount() {
        $fieldSQL = "
            COUNT (DISTINCT
                case when (PROD_PROFILE.PROD_ID IS NULL OR PROD_PROFILE.PROD_ID = '')
                then (
                    case when (ISSN.PROD_ID IS NULL OR ISSN.PROD_ID = '')
                    then (
                        PICK_ITEM.PROD_ID
                    ) else (
                        ISSN.PROD_ID
                    ) end
                ) else (
                    PROD_PROFILE.PROD_ID
                ) end 
            ) AS PROD_ID_COUNT        
        ";
        
        return $this->getAggregateValue($fieldSQL);
    }
    
    public function selectProdId($rowOffset, $itemCountPerPage) {
        $prodId = array();

        $fieldSQL = "
            DISTINCT
                case when (PROD_PROFILE.PROD_ID IS NULL OR PROD_PROFILE.PROD_ID = '')
                then (
                    case when (ISSN.PROD_ID IS NULL OR ISSN.PROD_ID = '')
                    then (
                        PICK_ITEM.PROD_ID
                    ) else (
                        ISSN.PROD_ID
                    ) end
                ) else (
                    PROD_PROFILE.PROD_ID
                ) end 
            AS PROD_ID
        ";
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, $fieldSQL);
        if (is_array($result) && count($result) > 0)
            foreach ($result as $resultRow) {
                if (!empty($resultRow['PROD_ID']))
                    $prodId[] = $resultRow['PROD_ID'];
            }
        
        return $prodId;
    }
    
    public function selectProdIdAndPickLabelNo($rowOffset, $itemCountPerPage) {
        $rows = array();
        
        $fieldsSQL = "
            DISTINCT
            case when (PROD_PROFILE.PROD_ID IS NULL OR PROD_PROFILE.PROD_ID = '')
            then (
                case when (ISSN.PROD_ID IS NULL OR ISSN.PROD_ID = '')
                then (
                    PICK_ITEM.PROD_ID
                ) else (
                    ISSN.PROD_ID
                ) end
            ) else (
                PROD_PROFILE.PROD_ID
            ) end AS PROD_ID,
            PICK_ITEM.PICK_LABEL_NO
        ";
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, $fieldsSQL);
        if (is_array($result))
            $rows = $result;
        
        return $rows;
    }
    
    public function selectNonOPStatus($rowOffset, $itemCountPerPage) {
        $statuses = array();
        
        $originalConditions = $this->getConditions();
        $this->addConditions(array('NOT PICK_ITEM.PICK_LINE_STATUS = ?' => array('OP')));
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PICK_ITEM.PICK_LINE_STATUS ');
        if (false !== ($result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, ' DISTINCT PICK_ITEM.PICK_LINE_STATUS '))) 
            $statuses = array_map(create_function('$item', 'return $item["PICK_LINE_STATUS"];'), $result);
        
        $this->setConditions($originalConditions);
        
        return $statuses;
    }

    /**
     * @param int $rowOffset
     * @param int $itemCountPerPage
     * @return array
     */
    protected function _selectPickLabelNo($rowOffset, $itemCountPerPage) {
        $result = array();

        foreach ($this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT PICK_ITEM.PICK_LABEL_NO') as $resultRow) {
            $result[] = $resultRow['PICK_LABEL_NO'];
        }

        return $result;
    }

    function selectProdIdToAllocate($productLimit)
    {
        $result = array();

        $totalLines = count($this);

        if ($totalLines < 1 ) return $result;

        $args = $this->_selectPickLabelNo(0, $totalLines);

        $sql = "
            SELECT FIRST $productLimit DISTINCT
                CASE
                    WHEN PICK_ITEM.PROD_ID IS NULL THEN ISSN.PROD_ID
                    ELSE PICK_ITEM.PROD_ID
                END AS PROD_ID
                FROM
                    PICK_ITEM
                    LEFT OUTER JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
                WHERE
                    PICK_ITEM.PICK_LINE_STATUS = 'OP'
                    AND PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($args)), 0, -2) . ")
        ";

        array_unshift($args, $sql);

        if (false !== ($queryResult = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args))) {
            foreach ($queryResult as $resultRow) {
                if (empty($resultRow['PROD_ID'])) continue;
                $result[$resultRow['PROD_ID']] = $resultRow['PROD_ID'];
            }
        }

        return $result;
    }

    function selectProdIdAndPickLabelNoToAllocate()
    {
        $result = array();

        $totalLines = count($this);

        if ($totalLines < 1 ) return $result;

        $args = $this->_selectPickLabelNo(0, $totalLines);

        $sql = "
            SELECT DISTINCT
                PICK_ITEM.PICK_ORDER,
                PICK_ITEM.PICK_LABEL_NO,
                CASE
                    WHEN PICK_ITEM.PROD_ID IS NULL THEN ISSN.PROD_ID
                    ELSE PICK_ITEM.PROD_ID
                END AS PROD_ID
                FROM
                    PICK_ITEM
                    LEFT OUTER JOIN ISSN ON PICK_ITEM.SSN_ID = ISSN.SSN_ID
                WHERE
                    PICK_ITEM.PICK_LINE_STATUS = 'OP'
                    AND PICK_ITEM.PICK_LABEL_NO IN (" . substr(str_repeat('?, ', count($args)), 0, -2) . ")
        ";

        array_unshift($args, $sql);

        if (false !== ($queryResult = call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $args))) {
            $result = Minder_ArrayUtils::mapKey($queryResult, 'PICK_LABEL_NO');
        }

        return $result;
    }

    function selectPickOrderToAllocate($orderLimit)
    {
        return array(); //not used now
    }


    public function fetchPickLabelNoForPickLabels($rowOffset, $itemCountPerPage) {
        return $this->_selectPickLabelNo($rowOffset, $itemCountPerPage);
    }
}

class Minder_SysScreen_Model_WaitingPickingLine_Exception extends Minder_SysScreen_Model_Exception {}