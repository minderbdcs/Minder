<?php

class NetSuite_Synchronizer
{
    private $_cacheFile;

    private $_cacheData;
    private $_soapClient;
    private $_parser;
    private $_minder;

    /**
     * Allow console output
     *
     * @var boolean
     */
    private $_mode;

    private $_userid;
    private $_deviceid;
    private $_ip;

    public $lastError;
    public $lastErrorCode;

    public function __construct(NetSuite_SoapWrapper $client, $userid, $deviceid, $ip = '127.0.0.1', $silentmode = false)
    {
        $this->_soapClient  = $client;
        $this->_parser      = new NetSuite_Parser();
        $this->_minder      = Minder::getInstance();
        $this->_mode        = $silentmode;

        $this->_userid      = $this->_minder->userId   = $userid;
        $this->_deviceid    = $this->_minder->deviceId = $deviceid;
        $this->_ip          = $this->_minder->ip       = $ip;
        $this->_minder->silent  = $silentmode;
        $this->_minder->isAdmin = true;

        $response = $this->_soapClient->login();
        if ($this->_parser->isSuccess($response)) {
            $dataS = $this->__loadCache('S');
            if (false !== $dataS) {
                $this->_cacheData = $dataS;
            } else {
                $this->_cacheData = array();
                $this->__saveCache();
            }
        }
    }

    private function msg($output)
    {
        if (!$this->_mode) {
            echo date('Y-m-d H:i:s', time()) . ': ' . $output . PHP_EOL;
        }
    }

    public function __destruct()
    {
        /*
        if (!$this->_minder->logout('Admin', 'Admin', '127.0.0.1')) {
            $this->msg('Logout unsuccessfull.');
        }
        */
    }

    /**
     * Load data from NetSuite and build local cache of data
     *
     * @return array
     */
    private function __buildCache() {
        $cache    =   array();
        $response = $this->_soapClient->searchTransaction(array('salesOrder', 'purchaseOrder'));
        if ($response) {
            $totalPages = $this->_parser->parsePagesCount($response);
            if(($data = $this->_parser->parseSearch($response)) != false) {
                $cache = $data;
            }
            for($i=0; $i<$totalPages - 1; $i++) {
                $response = $this->_soapClient->searchNext();
                if ($response) {
                    if(($data = $this->_parser->parseNextSearch($response)) != false) {
                        $cache    = array_merge($cache, $data);
                    }
                }
            }
        }
        return $cache;
    }

    /**
     * Get local cache
     *
     * @return array
     */
    public function getCache($id = 'S')
    {
        return $this->_minder->getSoapCache($id);
    }

    public function getCacheDate($id)
    {
        return $this->_minder->getSoapCacheDate($id);
    }

    /**
     * Load stored local cache of data
     *
     * @return array
     */
    private function __loadCache($paramName = 'S')
    {
        return $this->_minder->getSoapCache($paramName);
    }

    /**
     * Store cache
     *
     * @param array $cacheData
     * @return int|false
     */
    private function __saveCache()
    {
        $result = $this->_minder->updateSoapCache('S', $this->_cacheData);
        if ($result) {
            $result = $this->_minder->updateSoapCache('last_update', time());
        }
        return $result;
    }

    /**
     * Completely regenerate local cache of data
     *
     * @return boolean
     */
    public function refresh()
    {
        $cache = $this->__buildCache();
        if (false !== $cache) {
            $this->_cacheData = $cache;
            if (false === $this->__saveCache()) {
                throw new Exception('Can\'t store cache.');
            } else {
                return $this->_cacheData;
            }
        } else {
            return false;
        }
    }

    /**
     * Get record by type and internalId
     *
     * @param string $type
     * @param string $id
     *
     * @return object|string
     */
    public function get($type, $id)
    {
        if (!in_array($type, $this->_soapClient->getAllowedTypeList())) {
            throw new Exception('Unknown type for request.');
        }
        $response = $this->_soapClient->get($type, $id);
        if ($response) {
            switch (strtolower($type)) {
                case 'salesorder':
                    $result = current($response);
                    break;
                case 'purchaseorder':
                    $result = current($response);
                    break;
                break;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Get record List by type and internalId
     *
     * @param array $list List of internalId's
     * @param string $type
     *
     * @return array
     */
    public function getList($type, array $list)
    {
        if (!in_array($type, $this->_soapClient->getAllowedTypeList())) {
            throw new Exception('Unknown type for request.');
        }
        $response = $this->_soapClient->getList($list, $type);
        switch ($type) {
            case 'salesOrder':
                $result = $this->_parser->parseSalesOrder($response);
                break;
            case 'purchaseOrder':
                $result = $this->_parser->parsePurchaseOrder($response);
                break;
            break;
        }
        return $result;
    }



    /**
     * Update local cache
     *
     * @return array List of new transactions
     */
    public function update()
    {
        $countToUpdate = 0; // records to update;
        $countUpdated  = 0; // updated records

        $result = array();
        //$updateDate     = date(DATE_W3C, time());
        $startDate      = time();

        $lastUpdateDate = $this->_soapClient->getFromCache('last_update_date');
        if (!$lastUpdateDate) {
            $lastUpdateDate = 0;
        } else {
            $this->msg($lastUpdateDate);
        }
        //$response   = $this->_soapClient->searchTransaction(array(), $lastUpdateDate);
        $response = $this->_soapClient->searchTransaction(array('salesOrder', 'purchaseOrder'), date(DATE_W3C, $lastUpdateDate));
        $this->msg('---xml--- Synchronizer->update->search');
        $this->msg($this->_soapClient->techResponse);
        $this->msg('---xml---');
        if ($response) {
            $totalPages = $this->_parser->parsePagesCount($response);
            if(($data = $this->_parser->parseSearch($response)) != false) {
                $countToUpdate += count($data);
                $countUpdated += $this->importData($data);
            }
            for($i=0; $i<$totalPages - 1; $i++) {
                $this->msg('Start next ' . $i);
                $response = $this->_soapClient->searchNext();
                if ($response) {
                    if(($data = $this->_parser->parseNextSearch($response)) != false) {
                        $countUpdated += $this->importData($data);
                    }
                } else {
                    $this->msg('Next search page failed : ' . $this->_soapClient->lastError);
                }
            }
            $this->_soapClient->addToCache('last_update_date', $startDate);
        } else {
            /*
            $this->msg('---xml--- Synchronizer->update->search');
            $this->msg($this->_soapClient->techResponse);
            $this->msg('---xml---');
            */
        }
        $result['toUpdate'] = $countToUpdate;
        $result['updated']  = $countUpdated;
        return $result;
    }

    public function importData($data, $checkModifiedDate = true)
    {
        $count = 0;
        $this->lastError = 'Nothing to import';
        foreach ($data as $obj) {
            if (($obj instanceof stdClass) && isset($obj->status)) {
//                if (($lastIdUpdate = $this->getCacheDate($obj->internalId))) {
                if (($cacheObj = $this->getCache($obj->internalId))) {
//                    if (strtotime($lastIdUpdate) < strtotime($obj->lastModifiedDate)) {
                    if ($checkModifiedDate) {
                        if ($cacheObj->lastModifiedDate <= $obj->lastModifiedDate) {
                            $proceed = true;
                        } else {
                            //$this->lastError = 'LastModifiedDate ' . $obj->lastModifiedDate . ' less than Cache date ' . $lastIdUpdate;
                            $this->lastError = 'LastModifiedDate ' . $obj->lastModifiedDate . ' less than LastModifiedDate from Cache ' . $cacheObj->lastModifiedDate;
                            $proceed = false;
                            $result = false;
                        }
                    } else {
                        $proceed = true;
                    }
                } else {
                    $proceed = true;
                }
                if ($proceed) {
                    switch (strtolower($obj->type)) {
                        case 'salesorder':
                            $result = $this->importSalesOrder($obj);
                            break;
                        case 'purchaseorder';
                            $result = $this->importPurchaseOrder($obj);
                            break;
                        default:
                            $result = false;
                            $this->lastError = 'Not allowed transaction type ' . $obj->type;
                            break;
                    }
                }
                if (false == $result) {
                    $this->msg($obj->internalId . ' - ' . $this->getTranId($obj) . ' - import rejected. ' . $this->lastError);
                    $this->lastError = $obj->internalId . ' - ' . $this->getTranId($obj) . ' - import rejected. ' . $this->lastError;
                    $this->_soapClient->mailToCustomer($obj->internalId . ' - ' . $this->getTranId($obj) . ' - import rejected. ', $this->lastError);
                } else {
                    $count++;
                    $this->lastError = 'Import accepted';
                }
                $this->_soapClient->addToCache($obj->internalId, $obj);
            } else {
                $this->msg('Incorrect Object or status not exists : ' . serialize($obj));
                $this->lastError = 'Incorrect Object or status not exists';
            }
        }
        return $count;
    }

    public function importSalesOrder($obj)
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        if (isset($obj->shipDate)) {
            /* if (($obj->status == 'Pending Fulfillment' || $obj->status == 'Partially Fulfilled')) { */
            if ((strtolower($obj->status) == 'pending fulfillment' ||
                strtolower($obj->status) == 'partially fulfilled' )) {
                $proceed = true;
            } else {
                $proceed = false;
                $this->lastError = 'Order with status = \'' . $obj->status . '\' not allowed to import';
                $this->lastErrorCode = -103;
            }
        } else {
            $proceed = false;
            $this->lastError = 'Order doesn\'t have shipDate.';
            $this->lastErrorCode = -104;
        }
/*
	if department id is allowed ie in the company table then allow it
	else not allowed to import
        $this->lastError = 'Wont add department id ' . $obj->department->internalId;
        $this->lastError .= ' department name ' . $obj->department->name;
*/
        if ($proceed) {
            if (isset($obj->department)) {
                if (array_key_exists($obj->department->internalId, $this->_minder->getAllowedLegacyCompanys())) {
                    // an allowed company 
                    // save department id for order values calc
                    $this->_minder->updateSoapCache("DepartmentId", $obj->department->internalId);
                    $proceed = true;
                } else {
                    $proceed = false;
                    $this->lastError = 'Order with Department = ' . $obj->department->internalId . ' \'' . $obj->department->name . '\' not allowed to import';
                    $this->lastErrorCode = -103;
                    $this->_minder->updateSoapCache("DepartmentId", "");
                }
            } else {
                $proceed = false;
                $this->lastError = 'Order doesn\'t have department.';
                $this->lastErrorCode = -104;
                $this->_minder->updateSoapCache("DepartmentId", "");
            }
        }
        if ($proceed) {
            $pickOrder = $this->get('salesOrder', $obj->internalId);
            if ($pickOrder) {
                $log->info('pickOrder:' .  print_r($pickOrder,true));
                // save the current pick item status somewhere and 
                //    set the current status to 'WP' - of lines that can be changed           
                if (!$this->_minder->updatePickItemStatus($pickOrder->pickOrder, 'WP')) {
			$flash = 'Order ' . $pickOrder->pickOrder . ' was not saved to Work in Progress.';
		}
                if (!$this->_minder->pickOrderInsert($pickOrder)) {
                    if ($this->_minder->lastErrorCode) {
                        $flash = $this->_minder->lastError . ' ' . $obj->tranId;
                    } else {
                        $flash = $obj->internalId . '. ' . $this->_minder->lastError;
                    }
                    $this->lastError = $flash;
                    $result = false;
                } else {
                    $flash = 'new Sales Order inserted ' . $obj->tranId . ' ';
                    if (!$this->_minder->pickOrderConfirm($pickOrder->pickOrder)) {
                        $flash .= ' and was not confirmed,';
                    }
                    if (!$this->_minder->pickOrderApprovePick($pickOrder->pickOrder)) {
                        $flash .= ' and was not approved,';
                    }
                    if (!$this->_minder->pickOrderApproveDespatch($pickOrder->pickOrder)) {
                        $flash .= ' was not dispatched,';
                    }
                    $flash  = substr($flash, 0, -1) . '.';
                    $result = true;
                }
                // save the pick items status - of lines that failed the update
                {
                	//    revert status to 'HD' value where the status is 'WP'
                	if (!$this->_minder->updatePickItemStatusBack($pickOrder->pickOrder, 'WP', 'HD' )) {
				if (!isset($flash)) {
					$flash = '';
				}
				$flash .= 'Order ' . $pickOrder->pickOrder . ' was not saved to Held Status.';
			}
                } 

            } else {
                $result = false;
                // code -101: order rejected;
                if ($this->_soapClient->lastErrorCode != -101) {
                    $flash = 'Can\'t retrieve order info from NetSuite ' . $obj->internalId;
                    $flash .= '. last error = ' . $this->_soapClient->lastError;
                    $this->lastError = $flash;
                } else {
                    $flash = $obj->internalId . ': ' . $this->_soapClient->lastError;
                    $this->lastError = $flash;
                    //$this->lastError = 'Order rejected due code 101';
                }
            }
            $this->msg($flash);
        } else {
            $result = false;
        }
	if (!isset($flash)){
		$flash = '';
	}
        $log->info('end importSalesOrder:' . $flash);
        return $result;
    }

    public function importPurchaseOrder($obj)
    {
        if (isset($obj->dueDate)) {
            /* if (($obj->status == 'Pending Receipt' ||
                $obj->status == 'Partially Receipted' ||
                $obj->status == 'Approved By Supervisor/Pending Receipt')) { */
            if ((strtolower($obj->status) == 'pending receipt' ||
                strtolower($obj->status) == 'partially receipted' ||
                strtolower($obj->status) == 'approved by supervisor/pending receipt' ||  
                strtolower($obj->status) == 'pending billing/partially received')) { 
                $proceed = true;
            } else {
                $proceed = false;
                $this->lastError = 'Order with status = \'' . $obj->status . '\' not allowed to import';
                $this->lastErrorCode = -103;
            }
        } else {
            $proceed = false;
            $this->lastError = 'Order doesn\'t have dueDate.';
            $this->lastErrorCode = -105;
        }
/*
	if department id is allowed ie in the company table then allow it
	else not allowed to import
        $this->lastError = 'Wont add department id ' . $obj->department->internalId;
        $this->lastError .= ' department name ' . $obj->department->name;
*/
        if ($proceed) {
            if (isset($obj->department)) {
                if (array_key_exists($obj->department->internalId, $this->_minder->getAllowedLegacyCompanys())) {
                    // an allowed company 
                    // save department id for order values calc
                    $this->_minder->updateSoapCache("DepartmentId", $obj->department->internalId);
                    $proceed = true;
                } else {
                    $proceed = false;
                    $this->lastError = 'Order with Department = ' . $obj->department->internalId . ' \'' . $obj->department->name . '\' not allowed to import';
                    $this->lastErrorCode = -103;
                }
            } else {
                $proceed = false;
                $this->lastError = 'Order doesn\'t have department.';
                $this->lastErrorCode = -104;
            }
        }
        if ($proceed) {
            $purchaseOrder = $this->get('purchaseOrder', $obj->internalId);
            if ($purchaseOrder) {
                if (!$this->_minder->addPurchaseOrder($purchaseOrder)) {
                    $flash = $obj->internalId . '. ' . $this->_minder->lastError;
                    $this->lastError = $this->_minder->lastError;
                    $result = false;
                } else {
                    if($this->_minder->lastErrorCode == -201) {
                        $flash = $this->_minder->lastError;
                        $this->lastError = $this->_minder->lastError;
                        $result = true;
                    } else {
                        $flash = 'new Purchase Order inserted ' . $obj->tranId;
                        $result = true;
                    }
                }
            } else {
                $result = false;
                // code -101: order rejected;
                if ($this->_soapClient->lastErrorCode != -101) {
                    $flash = 'Can\'t retrieve order info from NetSuite ' . $obj->internalId;
                    $flash .= '. last error = ' . $this->_soapClient->lastError;
                } else {
                    $flash = $obj->internalId . ': ' . $this->_soapClient->lastError;
                    $this->lastError = $flash;
                }
            }
            $this->msg($flash);
        } else {
            $result = false;
        }
        return $result;
    }

/*
    public function update() {
        $countToUpdate = 0; // records to update;
        $countUpdated  = 0; // updated records

        $result = array();
        $oldcache = $this->_cacheData;
        if ($this->refresh()) {
            $newcache = $this->_cacheData;
            foreach ($newcache as $obj) {
                if (($obj instanceof stdClass) && isset($obj->status)) {
                    $add     = true;
                    $proceed = false;
                    switch ($obj->type) {
                        case 'SalesOrder':
                            if (isset($obj->shipDate)) {
                                if (($obj->status == 'Pending Fulfillment' || $obj->status == 'Partially Fulfilled')) {
                                    $proceed = true;
                                } else {
                                    $proceed = false;
                                    $whyNotProceed = 'Order with status = \'' . $obj->status . '\' not allowed to import';
                                }
                            } else {
                                $proceed = false;
                                $whyNotProceed = 'Order doesn\'t have shipDate.';
                            }
                            break;
                        case 'PurchaseOrder':
                            if (isset($obj->dueDate)) {
                                -* if (($obj->status == 'Pending Receipt' ||
                                    $obj->status == 'Partially Receipted' ||
                                    $obj->status == 'Approved By Supervisor/Pending Receipt')) { *-
                                if ((strtolower($obj->status) == 'pending receipt' ||
                                     strtolower($obj->status) == 'partially receipted' ||
                                     strtolower($obj->status) == 'approved by supervisor/pending receipt')) {
                                    $proceed = true;
                                } else {
                                    $proceed = false;
                                    $whyNotProceed = 'Order with status = \'' . $obj->status . '\' not allowed to import';
                                }
                            } else {
                                $proceed = false;
                                $whyNotProceed = 'Order doesn\'t have dueDate.';
                            }
                            break;
                    }
                    if ($proceed) {
                        foreach ($oldcache as $oldobj) {
                            if ($obj == $oldobj) {
                                $add = false;
                                break;
                            }
                        }
                        if ($add) {
                            $countToUpdate++;
                            $result[] = $obj;
                        }
                    } else {
                        $this->msg($obj->internalId . ' - ' . $obj->tranId . ' - import rejected. ' . $whyNotProceed);
                    }
                } else {
                    $this->msg('Cache update - strange non-stdClass ' . serialize($obj));
                }
            }
            $listOfNotUpdated = array();
            $flag = false;
            foreach ($result as  $obj) {
                $st = strtolower($obj->type);
                switch ($st) {
                    case 'salesorder':
                        $pickOrder = $this->get('salesOrder', $obj->internalId);
                        if ($pickOrder) {
                            $flag = true;
                            if (!$this->_minder->pickOrderInsert($pickOrder)) {
                                if ($this->_minder->lastErrorCode) {
                                    $flash = $this->_minder->lastError . ' ' . $obj->tranId;
                                } else {
                                    $flash = $obj->internalId . '. ' . $this->_minder->lastError;
                                    $listOfNotUpdated[] = $obj->internalId;
                                }
                            } else {
                                $flash = 'new Sales Order inserted ' . $obj->tranId . ' ';

                                if (!$this->_minder->pickOrderConfirm($pickOrder->pickOrder)) {
                                    $flash .= ' and was not confirmed,';
                                }
                                if (!$this->_minder->pickOrderApprovePick($pickOrder->pickOrder)) {
                                    $flash .= ' and was not approved,';
                                }
                                if (!$this->_minder->pickOrderApproveDespatch($pickOrder->pickOrder)) {
                                     $flash .= ' was not dispatched,';
                                }

                                $flash  = substr($flash, 0, -1) . '.';
                                $countUpdated++;
                            }
                        } else {
                            // code -101: order rejected;
                            if ($this->_soapClient->lastErrorCode != -101) {
                                $listOfNotUpdated[] = $obj->internalId;
                                $flash = 'Can\'t retrieve order info from NetSuite ' . $obj->internalId;
                                $flash .= '. last error = ' . $this->_soapClient->lastError;
                            }
                        }
                    break;
                    case 'purchaseorder':
                        $purchaseOrder = $this->get('purchaseOrder', $obj->internalId);
                        if ($purchaseOrder) {
                            $flag = true;
                            if (!$this->_minder->addPurchaseOrder($purchaseOrder)) {
                                    $flash = $obj->internalId . '. ' . $this->_minder->lastError;
                            } else {
                                    if($this->_minder->lastErrorCode == -201) {
                                        $flash = $this->_minder->lastError;
                                    } else {
                                        $flash = 'new Purchase Order inserted ' . $obj->tranId;
                                    }
                                    $countUpdated++;
                            }
                        } else {
                            // code -101: order rejected;
                            if ($this->_soapClient->lastErrorCode != -101) {
                                $listOfNotUpdated[] = $obj->internalId;
                                $flash = 'Can\'t retrieve order info from NetSuite ' . $obj->internalId;
                                $flash .= '. last error = ' . $this->_soapClient->lastError;
                            } else {
                                $flash = $obj->internalId . ': ' . $this->_soapClient->lastError;
                            }
                        }
                    break;
                    default:
                        $flash = $obj->type . ' skipped ' . $obj->internalId;
                    break;
                }
                $this->msg($flash);
            }
            if (count($result) > 0 && $flag) {
                ibase_commit();
            }
            if (count($listOfNotUpdated) > 0) {
                $msg = count($listOfNotUpdated) . ' to clear from cache. (' .
                       implode(', ', $listOfNotUpdated) . ')';
                $this->msg($msg);
                foreach ($newcache as $keyInCache => $obj) {
                    $key = array_search($obj->internalId, $listOfNotUpdated);
                    if (false !== $key) {
                        $this->msg('Cleared from cache' . $obj->internalId);
                        unset($newcache[$keyInCache]);
                    }
                }
            }
            $this->_cacheData = $newcache;
            $this->__saveCache();
            $result = array();
        }
        $result['toUpdate'] = $countToUpdate;
        $result['updated']  = $countUpdated;
        return $result;
    }
*/

    public function clearSoapCache()
    {
        $this->_soapClient->clearCache();
    }

    /**
     * Commit local changes to NetSuite
     *
     * @return boolean
     */
    public function commit() {
        //$this->_soapClient->
    }

    /**
    * @descto deblock a transaction if it was blocked
    *
    * @param void
    * @return boolean
    */
    public function unlockSoapTransaction() {

       return $this->_soapClient->unlockSoapTransaction();
    }
    public function lockSoapTransaction() {

        return $this->_soapClient->lockSoapTransaction();
    }
    public function getSilentMode() {
        return $this->_mode;
    }

    public function setSilentMode($mode) {
        $this->_minder->silent = (bool)$mode;
        $this->_mode = (bool)$mode;
    }

    public function getTranId($obj)
    {
        return (isset($obj->tranId) ? $obj->tranId : 'NO tranId');
    }
}
