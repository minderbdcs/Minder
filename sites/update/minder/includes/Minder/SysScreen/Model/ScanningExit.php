<?php
class Minder_SysScreen_Model_ScanningExit extends Minder_SysScreen_Model
{
    public function acceptDespatch() {
        $statusMessages = array();
        
        $connoteToAccept = $this->selectArbitraryExpression(0, count($this), 'DISTINCT AWB_CONSIGNMENT_NO');
        
        $dsdxlTransaction = new Transaction_DSDXL();
        $minder = Minder::getInstance();
        
        foreach ($connoteToAccept as $row) {
            $dsdxlTransaction->reference = $row['AWB_CONSIGNMENT_NO'];
            
            if (false === ($result = $minder->doTransactionResponse($dsdxlTransaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                throw new Minder_SysScreen_Model_ScanningExit_Exception('Error while ' . $dsdxlTransaction->transCode . $dsdxlTransaction->transClass . ' transaction: ' . $minder->lastError);
            }
            
            $statusMessages[] = 'DSDX L Transaction: ' . $result;
        }
        
        return $statusMessages;
    }
    
    public function cancelDespatch() {
        $statusMessages = array();
        
        $despatchIds = $this->selectArbitraryExpression(0, count($this), 'DISTINCT PICK_DESPATCH.DESPATCH_ID');
        
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $despatchIds));
        if (count($despatchIds) < 1)
            return $statusMessages;
            
        $minder = Minder::getInstance();

        $sql = "
            SELECT DISTINCT 
                PICK_ITEM.PICK_ORDER 
            FROM 
                PICK_ITEM_DETAIL 
                LEFT JOIN PICK_ITEM ON PICK_ITEM_DETAIL.PICK_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
            WHERE 
                PICK_ITEM_DETAIL.DESPATCH_ID IN (" . substr(str_repeat('?, ', count($despatchIds)), 0, -2)  .  ")";
        $args = array_map(create_function('$item', 'return $item["DESPATCH_ID"];'), $despatchIds);
        array_unshift($args, $sql);
        
        $ordersToCancelDespatch = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $ordersToCancelDespatch));
        if (count($ordersToCancelDespatch) < 1)
            return $statusMessages;
        
        $sql = "SELECT VALID_SO FROM UPDATE_SALE_ORDER_STATUS(?, 'DS', 'DA', ?)";
        
        foreach ($ordersToCancelDespatch as $row) {
            if ($minder->findValue($sql, $row['PICK_ORDER'], $minder->userId) == 'T') {
                $statusMessages[] = 'Sales Order "' . $row['PICK_ORDER'] . '": Despatch canceled.';
            } else {
                $statusMessages[] = 'Sales Order "' . $row['PICK_ORDER'] . '": Error canceling despatch.';
            }
        }
        
        return $statusMessages;
    }

    /**
     * @param Minder_Printer_Abstract $printerObject
     * @return Minder_JSResponse
     */
    public function reprintLabels($printerObject) {
        $result = new Minder_JSResponse();

        $despatchIds = $this->selectArbitraryExpression(0, count($this), 'DISTINCT PICK_DESPATCH.DESPATCH_ID');

        if (count($despatchIds) < 1)
            return $result;

        $printedTotal = 0;
        $printTool    = new Minder_PackIdPrintTools();

        foreach ($despatchIds as $row) {
            $printResult = $printTool->printLabels($row['DESPATCH_ID'], $printerObject);
            $printedTotal += $printResult->printedTotal;

            $result->warnings = array_merge($result->warnings, $printResult->warnings);
            $result->messages = array_merge($result->messages, $printResult->messages);
            $result->errors   = array_merge($result->errors, $printResult->errors);

            if (count($result->errors) > 0)
                break;
        }

        if ($printedTotal > 0)
            $result->messages[] = 'Print request sent.';

        return $result;
    }
}

class Minder_SysScreen_Model_ScanningExit_Exception extends Minder_SysScreen_Model_Exception {}