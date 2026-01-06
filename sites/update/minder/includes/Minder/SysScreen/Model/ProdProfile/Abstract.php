<?php
/**
* Minder_SysScreen_Model_ProdProfile_Abstract
* provides common methods for models which works with PROD_PROFILE table
*/
abstract class Minder_SysScreen_Model_ProdProfile_Abstract extends Minder_SysScreen_Model_Editable
{
    protected static $trnasactionsMap = array(
                                            'PROD_ID'               => 'PPPD',
                                            'SHORT_DESC'            => 'PPPD',
                                            'ALTERNATE_ID'          => 'PPAI',
                                            'PROD_TYPE'             => 'PPPD',
                                            'STOCK'                 => 'PPPD',
                                            'SPECIAL_INSTR'         => 'PPSI',
                                            'HOME_LOCN_ID'          => 'PPAI',
                                            'SUPPLIER_NO1'          => 'PPSN1',
                                            'SUPPLIER_NO2'          => 'PPSN2',
                                            'SUPPLIER_NO3'          => 'PPSN3',
                                            'SUPPLIER_NO1_PROD'     => 'PPSN1',
                                            'SUPPLIER_NO2_PROD'     => 'PPSN2',
                                            'SUPPLIER_NO3_PROD'     => 'PPSN3',
                                            'SUPPLIER_PREFER'       => 'PPSN1',
                                            'UOM'                   => 'PPPD',
                                            'ISSUE_UOM'             => 'PPPK',
                                            'ORDER_UOM'             => 'PPPK',
                                            'ISSUE_PER_ORDER_UNIT'  => 'PPPK',
                                            'PALLET_CFG_C'          => 'PPPK',
                                            'PERM_LEVEL'            => 'PPPK',
                                            'SSN_TRACK'             => 'PPPD',
                                            'TOG_C'                 => 'PPPK',
                                            'DEFAULT_ISSUE_QTY'     => 'PPSP',
                                            'MAX_QTY'               => 'PPSP',
                                            'MIN_QTY'               => 'PPSP',
                                            'REORDER_QTY'           => 'PPSP',
                                            'MAX_ISSUE_QTY'         => 'PPSP',
                                            'STANDARD_COST'         => 'PPPD',
                                            'NET_WEIGHT'            => 'PPSP',
                                            'SALE_PRICE'            => 'PPSP',        
                                            'PROD_RETRIEVE_STATUS'  => 'PPCP',
                                            'COMPANY_ID'            => 'PPCP',
                                            'ISSUE_PER_INNER_UNIT'  => 'PPPK',
                                            'ORDER_WEIGHT'          => 'PPPK',
                                            'NET_WEIGHT_UOM'        => 'PPPK',
                                            'ORDER_WEIGHT_UOM'      => 'PPPK',
                                            'ISSUE'                 => 'PPPK',
                                            'INNER_UOM'             => 'PPPK',
                                            'INNER_WEIGHT'          => 'PPPK',
                                            'INNER_WEIGHT_UOM'      => 'PPPK',
                                            'PALLER_CFG_INNER'      => 'PPPK'
    );
    
    /**
    * Used to select PROD_ID from model rows.
    * Each child model should implement this method, as it needed
    * by several common methods
    * 
    * @param mixed $rowOffset
    * @param mixed $itemCountPerPage
    */
    abstract public function selectProdId($rowOffset, $itemCountPerPage);

    abstract protected function _selectProdIdAndCompanyId($rowOffset, $itemCountPerPage);
    
    /**
    * Returns all fields from PROD_PROFILE table
    * 
    * @param int $rowOffset
    * @param int $itemCountPerPage
    * 
    * @return array
    */
    public function selectCompleteProdProfile($rowOffset, $itemCountPerPage) {
        $prodProfile = array();
        
        $prodId = $this->selectProdId($rowOffset, $itemCountPerPage);
        if (count($prodId) < 1) 
            return $prodProfile;
        
        $limitExpression = $this->_getLimitExpression($rowOffset, $itemCountPerPage);
        $sql  = 'SELECT ' . $limitExpression . ' * FROM PROD_PROFILE WHERE PROD_ID IN (' . substr(str_repeat('?, ', count($prodId)), 0, -2) . ')';
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $prodId));
        $args = array_values($prodId);
        array_unshift($args, $sql);
        
        $minder = Minder::getInstance();
        $prodProfile = call_user_func_array(array($minder, 'fetchAllAssoc'), $args);
        
        return $prodProfile;
    }
    
    /**
    * Create corresponding transaction object depending on Transaction type
    * 
    * @param string $transactionType - PPPD | PPSI | PPAI | PPSP | PPSN1 | PPSN2 | PPSN3 | PPCP | PPPK
    * @return Transaction
    */
    protected function createTransaction($transactionType) {
        switch (strtoupper($transactionType)) {
            case 'PPPD' :
                return new Transaction_PPPDP();
            case 'PPSI' :
                return new Transaction_PPSIP();
            case 'PPAI' :
                return new Transaction_PPAIP();
            case 'PPSP' :
                return new Transaction_PPSPP();
            case 'PPSN1':
                $tmpTransaction             = new Transaction_PPSNP();
                $tmpTransaction->supplierNo = 1;
                return $tmpTransaction;
            case 'PPSN2':
                $tmpTransaction             = new Transaction_PPSNP();
                $tmpTransaction->supplierNo = 2;
                return $tmpTransaction;
            case 'PPSN3':
                $tmpTransaction             = new Transaction_PPSNP();
                $tmpTransaction->supplierNo = 3;
                return $tmpTransaction;
            case 'PPCP':
                return new Transaction_PPCPP();
            case 'PPPK':
                return new Transaction_PPPKP();
            default:
                throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Unsupported transaction type "' . $transactionType . '"');
        }
    }
    
    public function updateRecords($dataset) {
        $this->validateData($dataset, 'update');
        $pkeys = array();
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $dataset));
        $oldConditions  = $this->getConditions();
        $updatedRecords = array();
        
        $minder = Minder::getInstance();
        
        foreach ($dataset as $tmpRowId => $dataRow) {
            $tmpConditions = $this->makeConditionsFromId($tmpRowId);
            $this->setConditions($tmpConditions);
            
            $originalRow   = $this->selectCompleteProdProfile(0, 1);
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $originalRow));
            if (empty($originalRow))
                throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Row #' . $tmpRowId . ' does not exists.');
                
            $originalRow = current($originalRow); //as we get array of rows
            $transactionsToPerform = array();
            $directUpdateFields    = array();
            
            //walk throw datarow and test for changed fields
            foreach ($dataRow as $dataField) {
                $fieldName = $dataField['name'];
                if (!array_key_exists($fieldName, $originalRow))
                    throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Unknown field PROD_PROFILE.' . $fieldName . '.');
                    
                if ($originalRow[$fieldName] == $dataField['value'])
                    continue;
                    
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $originalRow[$fieldName], $dataField['value']));

                if (isset(self::$trnasactionsMap[$fieldName])) {
                    $transactionType = self::$trnasactionsMap[$fieldName];
                
                    if (!isset($transactionsToPerform[$transactionType]))
                        $transactionsToPerform[$transactionType] = $this->createTransaction($transactionType);
                } else {
                    $directUpdateFields[$fieldName] = $fieldName;
                }
                    
                $originalRow[$fieldName] = $dataField['value']; //replace oroginal data with new data to pass into transaction
            }
            
            if (empty($transactionsToPerform) && empty($directUpdateFields))
                continue; //no updatable fields

            foreach ($transactionsToPerform as $transaction) {
                $transaction->fillFromProdProfileRow($originalRow);
                if (false === $minder->doTransactionResponse($transaction, 'Y', 'SSBKKKKSK', '', 'MASTER    ')) {
                    if ($transaction->transCode == 'PPPD' && $minder->lastError == 'Unknown error') {
                        //todo: this should be fixed in run_transaction_pppd
                        //ADD_TRAN_RESPONSE should return RESPONSE_TEXT with 'success' string on success
                        //or error description, but PPPDP transaction returns nothing
                        //untill this is fixed, I add such workaround
                    } else {
                        throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $minder->lastError);
                    }
                }
            }
            
            $updateSql = 'UPDATE PROD_PROFILE SET' . PHP_EOL;
            $queryArgs = array();
            foreach ($directUpdateFields as $fieldName) {
                $updateSql   .= $fieldName . ' = ?, ';
                $queryArgs[]  = trim($originalRow[$fieldName]);
            }
            
            $updateSql = substr($updateSql, 0, -2);
            
            if (count($directUpdateFields) > 0) {
                $updateSql   .= 'WHERE PROD_ID = ?';
                $queryArgs[]  = $originalRow['PROD_ID'];

                if (false === $minder->execSQL($updateSql, $queryArgs)) 
                    throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Errors updating PROD_PROFILE: ' . $minder->lastError);
                    
            }
            
            $updatedRecords[] = $tmpRowId;
        }
        
        $this->setConditions($oldConditions);
        
        return $updatedRecords;
    }
    
    public function createRecords($dataset) {
        $this->validateData($dataset, 'create');
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $dataset));
        $minder            = Minder::getInstance();
        $prodProfileFields = $minder->getFieldList('PROD_PROFILE');
        
        $createdRecords    = 0;
        
        foreach ($dataset as $datarow) {
            $transactionsToPerform         = array();
            
            //always using PPPDP transaction to create new records
            $transaction                   = $this->createTransaction('PPPD');
            $transactionsToPerform['PPPD'] = $transaction;
            $newRow                        = array_fill_keys(array_values($prodProfileFields), '');

            //now walk throw datarow and fill fields for new record
            foreach ($datarow as $dataField) {
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $dataField));
                $fieldName  = $dataField['name'];
                $fieldValue = trim($dataField['value']);
                if (!array_key_exists($fieldName, $prodProfileFields))
                    throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Unknown field PROD_PROFILE.' . $fieldName . '.');
                    
                if (empty($fieldValue))
                    continue;
                    
                if (!isset(self::$trnasactionsMap[$fieldName]))
                    continue;
                    
                $transactionType = self::$trnasactionsMap[$fieldName];
                
                if (!isset($transactionsToPerform[$transactionType]))
                    $transactionsToPerform[$transactionType] = $this->createTransaction($transactionType);
                    
                $newRow[$fieldName] = $fieldValue;
            }
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $newRow));
            if (!isset($newRow['PROD_ID']) || empty($newRow['PROD_ID'])) 
                throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Error creating record: PROD_ID cannot be null.');

            if (!isset($newRow['SHORT_DESC']) || empty($newRow['SHORT_DESC'])) 
                throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Error creating record: SHORT_DESC cannot be null.');
                
            if (!isset($newRow['STANDARD_COST']) || empty($newRow['STANDARD_COST'])) 
                $newRow['STANDARD_COST'] = 0; //set default price to 0
            
            foreach ($transactionsToPerform as $transaction) {
                $transaction->fillFromProdProfileRow($newRow);
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $transaction));
                if (false === $minder->doTransactionResponse($transaction, 'Y', 'SSBKKKKSK', '', 'MASTER    ')) {
                    throw new Minder_SysScreen_Model_ProdProfile_Abstract_Exception('Errors executing transaction ' . $transaction->transCode . $transaction->transClass . ': ' . $minder->lastError);
                }
            }
            
            $createdRecords++;
        }
        
        return $createdRecords;
    }
    
    /**
    * Use this method when you need data to print PRODUCT LABEL
    * 
    * @param integer $rowOffset
    * @param integer $itemCountPerPage
    * 
    * @return array - label data rows
    */
    public function selectProductLabelData($rowOffset, $itemCountPerPage) {
        $labelData = array();
        
        $prodIds   = $this->selectProdId($rowOffset, $itemCountPerPage);

        $ids = $this->_selectProdIdAndCompanyId($rowOffset, $itemCountPerPage);
        
        if (empty($prodIds))
            return $labelData;
            
        $minder    = Minder::getInstance();

        foreach ($ids as $id) {
            $labelData[] = $minder->selectProdLabelData($id['PROD_ID'], $id['COMPANY_ID']);
        }

        return $labelData;
    }
}

class Minder_SysScreen_Model_ProdProfile_Abstract_Exception extends Minder_SysScreen_Model_Exception {}
