<?php
class Minder_SysScreen_Model_Grn extends Minder_SysScreen_Model
{
    public function selectGrnNo($rowOffset, $itemCountPerPage) {
        $grnNo = array();
        
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT GRN.GRN');
        if (is_array($result) && count($result) > 0)
            $grnNo = array_map(create_function('$item', 'return $item["GRN"];'), $result);
        
        return $grnNo;
    }
    
    public function selectGrnLineObject($rowOffset, $itemCountPerPage) {
        $grnObjects = array();
        $grnNos     = $this->selectGrnNo($rowOffset, $itemCountPerPage);
        
        if (count($grnNos) < 1) {
            return $grnObjects;
        }
        
        $sql    = 'SELECT * FROM GRN WHERE GRN IN (' . substr(str_repeat('?, ', count($grnNos)), 0, -2) . ')';
        $args   = array_values($grnNos);
        array_unshift($args, $sql);
        
        $minder = Minder::getInstance();
        
        if (false !== ($result = call_user_func_array(array($minder, 'fetchAllAssoc'), $args))) {
            foreach ($result as $grnRow) {
                foreach($grnRow as $strKey => $strValue){
                    if($minder->isValidDate($strValue)) {
                        $grnRow[$strKey] = $minder->getFormatedDate($strValue);
                    }
                }

                $tpGrnObject  = new GrnLine($grnRow);
                $grnObjects[] = $tpGrnObject;
            }
        }
        
        return $grnObjects;
    }
    
    /**
    * Select data for printing GRN labels.
    * For now just returns all collumns from GRN System Screen.
    * 
    * @param array $rowOffset
    * @param array $itemCountPerPage
    * 
    * @return array
    */
    public function selectGrnLabelData($rowOffset, $itemCountPerPage) {
        return $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'GRN.GRN');
    }
}

class Minder_SysScreen_Model_Grn_Exception extends Minder_SysScreen_Model_Exception {}
