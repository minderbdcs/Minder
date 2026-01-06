<?php

class Casper_SoapWrapper
{

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
    public $_soapClient;
    public static $client;

    public function __construct( $userid, $deviceid, $ip = '127.0.0.1', $silentmode = false)
    {
        //$this->_soapClient  = $client;
        $this->_minder      = Minder::getInstance();
        $this->_mode        = $silentmode;

        $this->_userid      = $this->_minder->userId   = $userid;
        $this->_deviceid    = $this->_minder->deviceId = $deviceid;
        $this->_ip          = $this->_minder->ip       = $ip;
        $this->_minder->silent  = $silentmode;
        $this->_minder->isAdmin = true;
        //$this->getSoapClient();

    }

    private function msg($output)
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        if (!$this->_mode) {
            echo date('Y-m-d H:i:s', time()) . ': ' . $output . PHP_EOL;
        }
    }

    public function __destruct()
    {
    }

    /**
     * convert date from Netsuite to Minder
     *
     * @param string $date
     * @return string|false
     */
    private function __dateConvert($date, $format = 'Y-m-d H:i:s') {
        return ($date ? date($format, strtotime($date)) : false);
    }

    public static function to_long_xml($longVal) {
        return '<long>' . $longVal . '</long>';
    }

    public static function from_long_xml($xmlFragmentString) {
        return (string)strip_tags($xmlFragmentString);
    }

    public function getLastRequest() {
	//return  self::$client->getLastRequest() ;
	return  self::$client->__getLastRequest() ;
    }
    public function getLastResponse() {
	//return  self::$client->getLastResponse() ;
	return  self::$client->__getLastResponse() ;
    }

    public function getSoapClient()
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        try
        {
            echo 'try to get legacy_url' . PHP_EOL;
            $log->info('try to get legacy_url');
            // get the wsdl location to use
            $wsdlLocation = $this->_minder->fetchOne('SELECT LEGACY_URL FROM CONTROL ');
            if ($wsdlLocation == "") {
                echo 'try to get legacy_url2' . PHP_EOL;
                $log->info('try to get legacy_url2');
                $wsdlLocation = $this->_minder->fetchOne('SELECT LEGACY_URL2 FROM CONTROL ');
            }
            if ($wsdlLocation == "") {
                echo 'No WSDL ' . PHP_EOL;
                $log->info('No WSDL ');
                die();
            }
            $wsdlLocation .= "?WSDL";
            echo 'Using WSDL Location:' . $wsdlLocation . PHP_EOL;
            $log->info('Using WSDL Location:' . $wsdlLocation ) ;
            // now read in the wsdl
            // update long to float in lines with EANNumber
            // save to a flat file in /tmp
            // use that file as the wsdllocation
            /* $this->_soapClient = new Zend_Soap_Client($wsdlLocation, array(
                'soap_version' => SOAP_1_1,
                'trace'      => 1,
                'exceptions' => 1,
                'encoding' => 'ISO-8859-1',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS
                )); */
            /* $this->_soapClient = new Zend_Soap_Client($wsdlLocation, array(
                'soap_version' => SOAP_1_1,
                'encoding' => 'ISO-8859-1',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS
                )); */
            self::$client = new SoapClient($wsdlLocation, array(
                'soap_version' => SOAP_1_1,
                'trace'      => 1,
                'encoding' => 'ISO-8859-1',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                'typemap' => array(
                             array(
                            'type_ns' => 'http://www.w3.org/2001/XMLSchema',
                            'type_name' => 'long',
                            'to_xml' => array('Casper_SoapWrapper', 'to_long_xml'),
                            'from_xml' => array('Casper_SoapWrapper', 'from_long_xml'),
                            ),
                )
                ));
            //var_dump(self::$client->getTypes());
        
        
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in creating soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in creating soap client ' );
            $log->info( $e);
            exit;
        }
    }

    public function waitForData()
    {
	    echo 'in waitForData:' . PHP_EOL;
           $wkEvent = $this->_minder->getDBEvent('MESSAGE_READY_TOGO', 'MESSAGE_SHUTDOWN');
	   echo 'got DB Event:' . $wkEvent . PHP_EOL;
            if ($wkEvent == 'MESSAGE_SHUTDOWN') {
                return False;
            } else {
                return True;
            }
    }


/*
    function getWKRequest ()
    {
        while retrieving records from web_requests that are status 'WK'
              while retrieving the transactions4 for the web request that are 'S' type
                     calc the soap functin to call
                     call the process to handle that soap message
                          this must update the transactions4 message to complete when have a response
                          a good response and an error response
              if all the transactions4 were good then update the web_requests request_status
              otherwise set as error in request_status
    }
*/

    public function getWKRequest ( )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $messageId = '';
        $clause = array('WEB_REQUESTS.REQUEST_STATUS = ?' => 'WK');
        //$clause = array('WEB_REQUESTS.REQUEST_STATUS = ?' => 'WS');
        $wkRequest = $this->_minder->getWebRequests ($clause, 0, 1);
        $log->info('requests : ' . print_r($wkRequest, True) );
	$result2 = False;
        if (is_array($wkRequest)) {
            foreach ($wkRequest as $line) {
                $messageId = $line['MESSAGE_ID'];
                // get records for this message that are to send
        	$tranReplyDate = date("Y-M-d H:i:s");
                if ( $this->getWKTransactions4 ($messageId, 'S', 'F') ) {
			// soap sent
            		$result     =   $this->_minder->updateWebRequests($messageId, 'SS');
			// soap received
                	$result     =   $this->_minder->updateWebRequests($messageId, 'SR', $tranReplyDate);
		} else {
        		$result     =   $this->_minder->updateWebRequests($messageId, 'E1', $tranReplyDate);
		}
                // now must commit this web requests work so that it is visible to others
        	$this->_minder->commitTransaction(True);
		$result2 = True;
            }
        }
	return $result2;
    }


    // get waiting pick orders - every 3 minutes
    public function sendGPLD ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGPLD:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        //try
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['PickingMode'] = 1;
		$wkRequest['WareHouseID'] = $this->_minder->whId;
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
            //$wkResponse = self::$client->GetPickingListDetail($wkRequest);
	    //$wkResponse = self::$client->GetPickingListDetail(array('PickingMode'=>1, 'WareHouseID'=>'FH'));  
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GPLD to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GPLD soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	$wkPicksStatus = (bool) $wkResponse->PickingListDetailRetrieveStatus; 
	$wkPicksWarehouseID = $wkResponse->WarehouseID;
	$wkPicksMode = $wkResponse->PickingMode;
	//$wkPicksOrders = $wkResponse->PickingListDetail;
    	$wkPicksOrders  = isset($wkResponse->PickingListDetail) ? $wkResponse->PickingListDetail : array();
	foreach ($wkPicksOrders as $wkPickOrderKey => $wkPickOrder) {
		$wkPickOrderId = $wkPickOrder->ContactInstanceID;
		$wkPickOrderDueDateW3C = $wkPickOrder->OrderDateTime;
            	//$wkPickOrderDueDate = $this->__dateConvert($wkPickOrderDueDateW3C);
		$wkSupplierListFlag = (bool) $wkPickOrder->SupplierListFlag;
		$wkSpecialMailoutFlag = (bool) $wkPickOrder->SpecialMailoutFlag;
		$wkPickOrderPriority = $wkPickOrder->OrderPriority;
		//$wkPickLines = $wkPickOrder->PickingLineDetail;
    		$wkPickLines  = isset($wkPickOrder->PickingLineDetail) ? $wkPickOrder->PickingLineDetail : array();
		$wkLineCnt = 0;
		$wkHasLines = False;
		// must write a transaction for these lines
		$transaction = new Transaction_GPLDC();
        	$transaction->transClass = 'C'; /* response from soap transaction */
		$transaction->reference = "<ContactInstanceID>".$wkPickOrderId;
		if ($wkPicksStatus) {
			$transaction->reference .= '|<PickingListDetailRetrieveStatus>true'   ;
		} else {
			$transaction->reference .= '|<PickingListDetailRetrieveStatus>false'   ;
		}
		$transaction->reference .= '|<WareHouseID>' . $wkPicksWarehouseID  ;
		$transaction->reference .= '|<PickingMode>' . $wkPicksMode  ;
		if ($wkSpecialMailoutFlag) {
			$transaction->reference .= '|<SupplierListFlag>true';
		} else {
			$transaction->reference .= '|<SupplierListFlag>false';
		}
		if ($wkSupplierListFlag) {
			$transaction->reference .= '|<SpecialMailoutFlag>true';
		} else {
			$transaction->reference .= '|<SpecialMailoutFlag>false';
		}
		$transaction->reference .= '|<OrderDateTime>' . $wkPickOrderDueDateW3C;
		$transaction->reference .= '|<OrderPriority>' . $wkPickOrderPriority  ;
		// save the transaction reference
		$wkTranReference = $transaction->reference;
		foreach ($wkPickLines as $wkPickLineKey => $wkPickLine) {
			$wkPickLineNo = $wkPickLine->PickLineNumber;
			$wkPickLineProdID = '' ;
			if (isset($wkPickLine->EANNumber)) {
				$wkPickLineProdID = $wkPickLine->EANNumber;
			}
			$wkPickLineProdExt = '' ;
			if (isset($wkPickLine->EANExtension)) {
				$wkPickLineProdExt = $wkPickLine->EANExtension;
			}
			//$wkPickLineQty = 0 ;
			$wkPickLineQty = 1 ;
			if (isset($wkPickLine->Quantity)) {
				$wkPickLineQty = $wkPickLine->Quantity;
			}
			$wkLineCnt++;
			$transaction->reference .= '|<PickLineNumber>' . $wkPickLineNo;
			$transaction->reference .= '|<EANNumber>' . $wkPickLineProdID;
			$transaction->reference .= '|<EANExtension>' . $wkPickLineProdExt;
			$transaction->reference .= '|<Quantity>' . $wkPickLineQty;
			if ($wkLineCnt == 10) {
				$this->_minder->doTransactionV4Response($transaction, $messageId);
				$wkLineCnt = 0;
				$wkHasLines = True;
				$transaction->reference = $wkTranReference;
			}
		}
		if ($wkLineCnt == 0) {
			if (!$wkHasLines) {
				// a noprod order
				$wkPickLineNo = '';
				$wkPickLineProdID = '' ;
				$wkPickLineProdExt = '' ;
				//$wkPickLineQty = 0 ;
				$wkPickLineQty = 1 ;
				$wkLineCnt++;
				$transaction->reference .= '|<PickLineNumber>' . $wkPickLineNo;
				$transaction->reference .= '|<EANNumber>' . $wkPickLineProdID;
				$transaction->reference .= '|<EANExtension>' . $wkPickLineProdExt;
				$transaction->reference .= '|<Quantity>' . $wkPickLineQty;
			}
		}
		if ($wkLineCnt != 0) {
			// must write a transaction for the remaining lines
			$this->_minder->doTransactionV4Response($transaction, $messageId);
		}

	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
	// now from this response must generate a transaction4 set of records
	// splitting by the limits in each transaction
	// note that the transaction will add a pick order with update_id = message id
        // then update the initial transactions4 record to be complete 
        $result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // get lines for a pick order
    public function sendGCLO ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGCLO:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['ContactInstanceID'] = '2101098';
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GCLO to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GCLO soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkPicksOrders = $wkResponse->ContactInstance;
    	$wkPicksOrders  = isset($wkResponse->ContactInstance) ? $wkResponse->ContactInstance : array();
	foreach ($wkPicksOrders as $wkPickOrderKey => $wkPickLine) {
		// must write a transaction for these lines
		$transaction = new Transaction_GCLOC();
        	$transaction->transClass = 'C'; /* response from soap transaction */
		$wkPicksStatus = (bool) $wkPickLine->ContactInstanceRetrieveStatus; 
		$wkPickOrderId = $wkPickLine->ContactInstanceID;
		$wkPickLineProdID = '' ;
		if (isset($wkPickLine->EANNumber)) {
			$wkPickLineProdID = $wkPickLine->EANNumber;
		}
		$wkPickLineProdExt = '' ;
		if (isset($wkPickLine->EANExtension)) {
			$wkPickLineProdExt = $wkPickLine->EANExtension;
		}
		//$wkPickLineQty = 0 ;
		$wkPickLineQty = 1 ;
		if (isset($wkPickLine->Quantity)) {
			$wkPickLineQty = $wkPickLine->Quantity;
		}
		$transaction->reference = "<ContactInstanceID>".$wkPickOrderId;
		$transaction->reference .= '|<EANNumber>' . $wkPickLineProdID;
		$transaction->reference .= '|<EANExtension>' . $wkPickLineProdExt;
		$transaction->reference .= '|<Quantity>' . $wkPickLineQty;
		if ($wkPicksStatus) {
			$transaction->reference .= '|<ContactInstanceRetrieveStatus>true'   ;
		} else {
			$transaction->reference .= '|<ContactInstanceRetrieveStatus>false'   ;
		}
		// must write a transaction for the remaining lines
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        $result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    //get revised pick lines for a started order
    public function sendGCLM ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGCLM:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['ContactInstanceID'] = '2101098';
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GCLM to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GCLM soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkPicksOrders = $wkResponse->ContactInstance;
    	$wkPicksOrders  = isset($wkResponse->ContactInstance) ? $wkResponse->ContactInstance : array();
	foreach ($wkPicksOrders as $wkPickOrderKey => $wkPickLine) {
		// must write a transaction for these lines
		$transaction = new Transaction_GCLMC();
        	$transaction->transClass = 'C'; /* response from soap transaction */
		$wkPicksStatus = (bool) $wkPickLine->ContactInstanceRetrieveStatus; 
		$wkPickOrderId = $wkPickLine->ContactInstanceID;
		$wkPickLineProdID = '' ;
		if (isset($wkPickLine->EANNumber)) {
			$wkPickLineProdID = $wkPickLine->EANNumber;
		}
		$wkPickLineProdExt = '' ;
		if (isset($wkPickLine->EANExtension)) {
			$wkPickLineProdExt = $wkPickLine->EANExtension;
		}
		//$wkPickLineQty = 0 ;
		$wkPickLineQty = 1 ;
		if (isset($wkPickLine->Quantity)) {
			$wkPickLineQty = $wkPickLine->Quantity;
		}
		$transaction->reference = "<ContactInstanceID>".$wkPickOrderId;
		$transaction->reference .= '|<EANNumber>' . $wkPickLineProdID;
		$transaction->reference .= '|<EANExtension>' . $wkPickLineProdExt;
		$transaction->reference .= '|<Quantity>' . $wkPickLineQty;
		if ($wkPicksStatus) {
			$transaction->reference .= '|<ContactInstanceRetrieveStatus>true'   ;
		} else {
			$transaction->reference .= '|<ContactInstanceRetrieveStatus>false'   ;
		}
		// must write a transaction for the remaining lines
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        $result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // get list of current campaigns
    public function sendGCPD ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGCPD:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - " );  
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GCPD to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GCPD soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkCampaignsOrders = $wkResponse->CampaignGroup;
    	$wkCampaignsOrders = isset($wkResponse->CampaignGroup) ? $wkResponse->CampaignGroup : array();
	foreach ($wkCampaignsOrders as $wkCampaignOrderKey => $wkCampaignLine) {
		// must write a transaction for these lines
		$transaction = new Transaction_GCPDC();
 	      	$transaction->transClass = 'C'; /* response from soap transaction */
		$wkCampaignId = $wkCampaignLine->CampaignID;
		$wkCampaignTitle = $wkCampaignLine->CampaignTitle;
		$transaction->reference = "<CampaignID>".$wkCampaignId;
		$transaction->reference .= '|<CampaignTitle>' . $wkCampaignTitle   ;
		// must write a transaction for the line
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // get details for a campaign
    public function sendGCID ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGCID:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['CampaignID'] = '1';
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GCID to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GCID soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkCampaignsOrders = $wkResponse->CampaignGroup;
    	$wkCampaignsOrders = isset($wkResponse->CampaignGroup) ? $wkResponse->CampaignGroup : array();
	$wkTranReference = "";
	$wkLineCnt = 0;
	$transaction = new Transaction_GCIDC();
       	$transaction->transClass = 'C'; /* response from soap transaction */
	$transaction->reference = "";
	foreach ($wkCampaignsOrders as $wkCampaignOrderKey => $wkCampaignLine) {
		$wkCampaignsStatus = (bool) $wkCampaignLine->CampaignContactInstanceRetrieveStatus; 
		$wkCampaignId = $wkCampaignLine->CampaignID;
		// must write a transaction for these lines
		if ($wkTranReference == "") {
			$transaction->reference = "<CampaignID>".$wkCampaignId;
			if ($wkCampaignsStatus) {
				$transaction->reference .= '|<CampaignContactInstanceRetrieveStatus>true'   ;
			} else {
				$transaction->reference .= '|<CampaignContactInstanceRetrieveStatus>false'   ;
			}
			// save the transaction reference
			$wkTranReference = $transaction->reference;
		}
		if (isset( $wkCampaignLine->ContactInstanceID )) {
			$wkPickOrders = $wkCampaignLine->ContactInstanceID;
			foreach ($wkPickOrders as $wkPickOrderKey => $wkPickLine) {
				$wkPickOrderId = $wkPickLine;
				$wkLineCnt++;
				$transaction->reference .= "|<ContactInstanceID>".$wkPickOrderId;
				if ($wkLineCnt == 34) {
					$this->_minder->doTransactionV4Response($transaction, $messageId);
					$wkLineCnt = 0;
					$transaction->reference = $wkTranReference;
				}
			}
		} else {
			// must force a transaction record for this false status
			$wkLineCnt++;
		}

	}
	if ($wkLineCnt != 0) {
		// must write a transaction for the remaining lines
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // get address for an Order
    public function sendGCNA ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGCNA:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['ContactInstanceID'] = '2102040';
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GCNA to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GCNA soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkPicksOrders = $wkResponse->ContactInstanceNameAndAddressGroup;
    	$wkPicksOrders  = isset($wkResponse->ContactInstanceNameAndAddressGroup) ? $wkResponse->ContactInstanceNameAndAddressGroup : array();
	$wkTranReference = "";
	$wkLineCnt = 0;
	$transaction = new Transaction_GCNAC();
       	$transaction->transClass = 'C'; /* response from soap transaction */
	$transaction->reference = "";
	foreach ($wkPicksOrders as $wkPickOrderKey => $wkPickLine) {
		$wkPickOrdersStatus = (bool) $wkPickLine->ContactInstanceNameAndAddressReceiveStatus; 
		$wkPickOrderId = $wkPickLine->ContactInstanceID;
		// must write a transaction for these lines
		if ($wkTranReference == "") {
			$transaction->reference = "<ContactInstanceID>".$wkPickOrderId;
			if ($wkPickOrdersStatus) {
				$transaction->reference .= '|<ContactInstanceNameAndAddressRetrieveStatus>true'   ;
			} else {
				$transaction->reference .= '|<ContactInstanceNameAndAddressRetrieveStatus>false'   ;
			}
			// save the transaction reference
			$wkTranReference = $transaction->reference;
		}
		$wkTitle = "";
		$wkFirstName = "";
		$wkLastName = "";
		$wkPost4StateId = "";
		$wkPostLine1 = "";
		$wkPostLine2 = "";
		$wkPostLine3 = "";
		$wkPostLine4 = "";
		if (isset( $wkPickLine->Salutation )) {
			$wkTitle = $wkPickLine->Salutation;
		}
		if (isset( $wkPickLine->FirstName )) {
			$wkFirstName = $wkPickLine->FirstName;
		}
		if (isset( $wkPickLine->LastName )) {
			$wkLastName = $wkPickLine->LastName;
		}
		if (isset( $wkPickLine->AustPost4StateID )) {
			$wkPost4StateId = $wkPickLine->AustPost4StateID;
		}
		if (isset( $wkPickLine->Address )) {
			//$wkPickAddress = $wkPickLine->Address;
    			$wkPickAddress = isset($wkPickLine->Address) ? $wkPickLine->Address : array();
			foreach ($wkPickAddress as $wkPickAddressKey => $wkPickAddressLine) {
				//echo("Address Key:" . $wkPickAddressKey . PHP_EOL);
				//echo("Address Data:" . $wkPickAddressLine . PHP_EOL);
				if ($wkPickAddressKey == 'Line1') {
					$wkPostLine1 = $wkPickAddressLine;
				}
				if ($wkPickAddressKey == 'Line2') {
					$wkPostLine2 = $wkPickAddressLine;
				}
				if ($wkPickAddressKey == 'Line3') {
					$wkPostLine3 = $wkPickAddressLine;
				}
				if ($wkPickAddressKey == 'Line4') {
					$wkPostLine4 = $wkPickAddressLine;
				}
			}
		}
		$transaction->reference .= "|<Salutation>".$wkTitle;
		$transaction->reference .= "|<FirstName>".$wkFirstName;
		$transaction->reference .= "|<LastName>".$wkLastName;
		$transaction->reference .= "|<Line1>".$wkPostLine1;
		$transaction->reference .= "|<Line2>".$wkPostLine2;
		$transaction->reference .= "|<Line3>".$wkPostLine3;
		$transaction->reference .= "|<Line4>".$wkPostLine4;
		$transaction->reference .= "|<AustPost4StateID>".$wkPost4StateId;
	}
	// must write a transaction for this address
	$this->_minder->doTransactionV4Response($transaction, $messageId);

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // get comment for an Order
    public function sendGSMD ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGSMD:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				$wkRequest[$wkField[0]] = $wkField[1];
			}
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['ContactInstanceID'] = '2102040';
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GSMD to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GSMD soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkPicksOrders = $wkResponse->SpecialMailoutGroup;
    	$wkPicksOrders  = isset($wkResponse->SpecialMailoutGroup) ? $wkResponse->SpecialMailoutGroup : array();
	$wkTranReference = "";
	$wkLineCnt = 0;
	$transaction = new Transaction_GSMDC();
       	$transaction->transClass = 'C'; /* response from soap transaction */
	$transaction->reference = "";
	foreach ($wkPicksOrders as $wkPickOrderKey => $wkPickLine) {
		$wkPickOrdersStatus = (bool) $wkPickLine->SpecialMailoutRetrieveStatus; 
		$wkPickOrderId = $wkPickLine->ContactInstanceID;
		$wkPickOrderComment = "";
		if (isset( $wkPickLine->InstructionText )) {
			$wkPickOrderComment = $wkPickLine->InstructionText;
		}
		// must write a transaction for these lines
		if ($wkTranReference == "") {
			$transaction->reference = "<ContactInstanceID>".$wkPickOrderId;
			if ($wkPickOrdersStatus) {
				$transaction->reference .= '|<SpecialMailoutRetrieveStatus>true'   ;
			} else {
				$transaction->reference .= '|<SpecialMailoutRetrieveStatus>false'   ;
			}
			// save the transaction reference
			$wkTranReference = $transaction->reference;
		}
		if (strlen($wkPickOrderComment) > 255) {
			$wkPickOrderComment = substr($wkPickOrderComment, 0, 255);
		}
		$transaction->reference .= "|<InstructionText>".$wkPickOrderComment;
	}
	// must write a transaction for this address
	$this->_minder->doTransactionV4Response($transaction, $messageId);

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }

    // product details
    public function sendGLTD ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGLTD:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
	$wkEANgroups = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				if ($wkField[0] == 'GetAllLiterature' ) {
					$wkRequest[$wkField[0]] = $wkField[1];
				} else {
					if ($wkField[0] == 'EANNumber' ) {
						if (isset($wkEANgroup)) {
							$wkEANgroups[] = $wkEANgroup;
						}
						$wkEANgroup = array();
					}
					$wkEANgroup[$wkField[0]] = $wkField[1];
				}
			}
		}
		if (isset($wkEANgroup)) {
			$wkEANgroups[] = $wkEANgroup;
		}
		if (count($wkEANgroups) > 0) {
			$wkRequest['EANgroup'] = $wkEANgroups;
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['GetAllLiterature'] = 'true';
		//$wkRequest['GetAllLiterature'] = 'false';
		//$wkEANgroup = array();
		//$wkEANgroup['EANNumber'] = '9320075041700';
		//$wkEANgroups[] = $wkEANgroup;
		//$wkRequest['EANgroup'] = $wkEANgroups;
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GLTD to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GLTD soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkProducts = $wkResponse->EANgroup;
    	$wkProducts = isset($wkResponse->EANgroup) ? $wkResponse->EANgroup : array();
	foreach ($wkProducts as $wkProductKey => $wkProductLine) {
		$transaction = new Transaction_GLTDC();
     	  	$transaction->transClass = 'C'; /* response from soap transaction */
		$wkProductsStatus = (bool) $wkProductLine->GetLiteratureDetailStatus; 
		$wkProdId = $wkProductLine->EANNumber;
		$wkProdExtension = "";
		$wkBusinessUnitNameId = "";
		$wkBusinessUnitName = "";
		$wkExternalTitle = "";
		$wkOwnersNameId = "";
		$wkOwnersName = "";
		$wkShortDesc = "";
		$wkProdType = "";
		$wkProdStatus = "";
		$wkPackQty = 0;
		$wkInnerPackQty = 0;
		$wkUnitWeight = 0;
		$wkUnitWeightUOM = "";
		$wkInnerPackUOM = "";
		$wkPackUOM = "";
		$wkPackWeight = 0;
		$wkPackWeightUOM = "";
		if (isset( $wkProductLine->EANextension )) {
			$wkProdExtension = $wkProductLine->EANextension;
		}
		// add slashes for fields with quotes in them
		if (isset( $wkProductLine->BusinessUnitNameID )) {
			$wkBusinessUnitNameId = str_replace ( "'", "", $wkProductLine->BusinessUnitNameID);
		}
		if (isset( $wkProductLine->BusinessUnitName )) {
			$wkBusinessUnitName = str_replace ("'", "", $wkProductLine->BusinessUnitName);
		}
		if (isset( $wkProductLine->LiteratureExternalTitle )) {
			$wkExternalTitle = str_replace ("'", "", $wkProductLine->LiteratureExternalTitle);
		}
		if (isset( $wkProductLine->LiteratureOwnersNameID )) {
			$wkOwnersNameId = str_replace ("'", "", $wkProductLine->LiteratureOwnersNameID);
		}
		if (isset( $wkProductLine->LiteratureOwnersName )) {
			$wkOwnersName = str_replace ("'", "", $wkProductLine->LiteratureOwnersName);
		}
		if (isset( $wkProductLine->LiteratureShortDescription )) {
			$wkShortDesc = str_replace ("'", "", $wkProductLine->LiteratureShortDescription);
		}

		if (isset( $wkProductLine->LiteratureType )) {
			$wkProdType = $wkProductLine->LiteratureType ;
		}
		if (isset( $wkProductLine->LiteratureStatus )) {
			$wkProdStatus = $wkProductLine->LiteratureStatus ;
		}
		if (isset( $wkProductLine->LiteraturePackQuantity )) {
			$wkPackQty = $wkProductLine->LiteraturePackQuantity ;
		}
		if (isset( $wkProductLine->LiteratureInnerPackQuantity )) {
			$wkInnerPackQty = $wkProductLine->LiteratureInnerPackQuantity ;
		}
		if (isset( $wkProductLine->LiteratureUnitWeight)) {
			$wkUnitWeight = $wkProductLine->LiteratureUnitWeight ;
		}
		if (isset( $wkProductLine->LiteratureUnitWeightUOM)) {
			$wkUnitWeightUOM = $wkProductLine->LiteratureUnitWeightUOM ;
		}
		if (isset( $wkProductLine->LiteratureInnerPackUOM)) {
			$wkInnerPackUOM = $wkProductLine->LiteratureInnerPackUOM ;
		}
		if (isset( $wkProductLine->LiteraturePackUOM)) {
			$wkPackUOM = $wkProductLine->LiteraturePackUOM ;
		}
		if (isset( $wkProductLine->LiteraturePackWeight)) {
			$wkPackWeight = $wkProductLine->LiteraturePackWeight ;
		}
		if (isset( $wkProductLine->LiteraturePackWeightUOM)) {
			$wkPackWeightUOM = $wkProductLine->LiteraturePackWeightUOM ;
		}
		// must write a transaction for this product
		$transaction->reference = "<EANNumber>".$wkProdId;
		if ($wkProductsStatus) {
			$transaction->reference .= '|<GetLiteratureDetailStatus>true'   ;
		} else {
			$transaction->reference .= '|<GetLiteratureDetailStatus>false'   ;
		}
		$transaction->reference .= "|<EANextension>".$wkProdExtension;
		$transaction->reference .= "|<BusinessUnitNameID>".$wkBusinessUnitNameId;
		$transaction->reference .= "|<BusinessUnitName>".$wkBusinessUnitName;
		$transaction->reference .= "|<LiteratureExternalTitle>".$wkExternalTitle ;
		$transaction->reference .= "|<LiteratureOwnersNameID>".$wkOwnersNameId;
		$transaction->reference .= "|<LiteratureOwnersName>".$wkOwnersName;
		$transaction->reference .= "|<LiteratureShortDescription>".$wkShortDesc;
		$transaction->reference .= "|<LiteratureType>".$wkProdType;
		$transaction->reference .= "|<LiteratureStatus>".$wkProdStatus;
		$transaction->reference .= "|<LiteraturePackQuantity>".$wkPackQty;
		$transaction->reference .= "|<LiteratureInnerPackQuantity>".$wkInnerPackQty;
		$transaction->reference .= "|<LiteratureUnitWeight>".$wkUnitWeight;
		$transaction->reference .= "|<LiteratureUnitWeightUOM>".$wkUnitWeightUOM;
		$transaction->reference .= "|<LiteratureInnerPackUOM>".$wkInnerPackUOM;
		$transaction->reference .= "|<LiteraturePackUOM>".$wkPackUOM;
		$transaction->reference .= "|<LiteraturePackWeight>".$wkPackWeight;
		$transaction->reference .= "|<LiteraturePackWeightUOM>".$wkPackWeightUOM;
		echo("reference:". $transaction->reference . PHP_EOL);
		$log->info("reference:". $transaction->reference );
		// must write a transaction for this product
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // product prepacks
    public function sendGLPP ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendGLPP:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
	$wkEANgroups = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				if ($wkField[0] == 'GetAllPrePacks' ) {
					$wkRequest[$wkField[0]] = $wkField[1];
				} else {
					if ($wkField[0] == 'EANNumber' ) {
						if (isset($wkEANgroup)) {
							$wkEANgroups[] = $wkEANgroup;
						}
						$wkEANgroup = array();
					}
					$wkEANgroup[$wkField[0]] = $wkField[1];
				}
			}
		}
		if (isset($wkEANgroup)) {
			$wkEANgroups[] = $wkEANgroup;
		}
		if (count($wkEANgroups) > 0) {
			$wkRequest['EANgroup'] = $wkEANgroups;
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkRequest['GetAllPrePacks'] = 'true';
		//$wkRequest['GetAllPrePacks'] = 'false';
		//$wkEANgroup = array();
		//$wkEANgroup['EANNumber'] = '9320075041700';
		//$wkEANgroups[] = $wkEANgroup;
		//$wkRequest['EANgroup'] = $wkEANgroups;
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending GLPP to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in GLPP soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkParentProducts = $wkResponse->ParentEANgroup;
    	$wkParentProducts = isset($wkResponse->ParentEANgroup) ? $wkResponse->ParentEANgroup : array();
	foreach ($wkParentProducts as $wkPProductKey => $wkPProductLine) {
		$wkParentProductsStatus = (bool) $wkPProductLine->ParentRetrievePrePackStatus; 
		$wkParentProdId = $wkPProductLine->ParentEANNumber;
		$wkParentProdExtension = "";
		if (isset( $wkPProductLine->EANextension )) {
			$wkParentProdExtension = $wkPProductLine->EANextension;
		}
		$wkLineCnt = 0;
		// must write a transaction for these lines
		$transaction = new Transaction_GLPPC();
     	  	$transaction->transClass = 'C'; /* response from soap transaction */
		$transaction->reference = "<ParentEANNumber>".$wkParentProdId;
		if ($wkParentProductsStatus) {
			$transaction->reference .= '|<ParentRetrievePrePackStatus>true'   ;
		} else {
			$transaction->reference .= '|<ParentRetrievePrePackStatus>false'   ;
		}
		$transaction->reference .= "|<ParentEANextension>".$wkParentProdExtension;
		// save the transaction reference
		$wkTranReference = $transaction->reference;
		if (isset( $wkPProductLine->ChildEANgroup )) {
			//$wkChildProducts = $wkPProductLine->ChildEANgroup;
    			$wkChildProducts = isset($wkPProductLine->ChildEANgroup) ? $wkPProductLine->ChildEANgroup : array();
			foreach ($wkChildProducts as $wkCProductKey => $wkCProductLine) {
				$wkChildProdId = $wkCProductLine->ChildEANnumber;
				$wkChildProdExt = "";
				$wkChildQty = 0;
				if (isset( $wkCProductLine->ChildEANextension )) {
					$wkChildProdExt = $wkCProductLine->ChildEANextension;
				}
				if (isset( $wkCProductLine->ChildQuantity )) {
					$wkChildQty = $wkCProductLine->ChildQuantity;
				}
				$wkLineCnt++;
				$transaction->reference .= '|<ChildEANnumber>' . $wkChildProdId;
				$transaction->reference .= '|<ChildEANextension>' . $wkChildProdExt;
				$transaction->reference .= '|<ChildQuantity>' . $wkChildQty;
				if ($wkLineCnt == 10) {
					echo("reference:". $transaction->reference . PHP_EOL);
					$log->info("reference:". $transaction->reference );
					$this->_minder->doTransactionV4Response($transaction, $messageId);
					$wkLineCnt = 0;
					$transaction->reference = $wkTranReference;
				}
			}
		}
		if ($wkLineCnt != 0) {
			echo("reference:". $transaction->reference . PHP_EOL);
			$log->info("reference:". $transaction->reference );
			// must write a transaction for the remaining lines
			$this->_minder->doTransactionV4Response($transaction, $messageId);
		}
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // update stock level
    public function sendUASL ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendUASL:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
	$wkEANgroups = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				if ($wkField[0] == 'EANNumber' ) {
					if (isset($wkEANgroup)) {
						$wkEANgroups[] = $wkEANgroup;
					}
					$wkEANgroup = array();
				}
				$wkEANgroup[$wkField[0]] = $wkField[1];
			}
		}
		if (isset($wkEANgroup)) {
			$wkEANgroups[] = $wkEANgroup;
		}
		if (count($wkEANgroups) > 0) {
			$wkRequest['EANgroup'] = $wkEANgroups;
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkEANgroup = array();
		$wkEANgroup['EANNumber'] = '9320075041700';
		$wkEANgroup['StockLevel'] = '1';
		$wkEANgroup['LiteratureStockStatus'] = 'ST';
		$wkEANgroup['UpdateDateTime'] = date(DATE_W3C, time());
		$wkEANgroups[] = $wkEANgroup;
		$wkRequest['EANgroup'] = $wkEANgroups;
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending UASL to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in UASL soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkProducts = $wkResponse->EANgroupUpdated;
    	$wkProducts = isset($wkResponse->EANgroupUpdated) ? $wkResponse->EANgroupUpdated : array();
	foreach ($wkProducts as $wkProductKey => $wkProductLine) {
		$wkProductsStatus = (bool) $wkProductLine->UpdateAvailableStock; 
		$wkProdId = $wkProductLine->EANNumber;
		$wkProdExtension = "";
		if (isset( $wkProductLine->EANextension )) {
			$wkProdExtension = $wkProductLine->EANextension;
		}
		// must write a transaction for these lines
		$transaction = new Transaction_UASLC();
     	  	$transaction->transClass = 'C'; /* response from soap transaction */
		$transaction->reference = "<EANNumber>".$wkProdId;
		if ($wkProductsStatus) {
			$transaction->reference .= '|<UpdateAvailableStock>true'   ;
		} else {
			$transaction->reference .= '|<UpdateAvailableStock>false'   ;
		}
		$transaction->reference .= "|<EANextension>".$wkProdExtension;
		echo("reference:". $transaction->reference . PHP_EOL);
		$log->info("reference:". $transaction->reference );
		// warning is returning 2147483647 as the prod id
		// this is a bug - probably only an int allowed at the remote soap server - but data is 9(13) - 9(13+6)
		// must write a transaction for the line
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // update order status
    public function sendUCIS ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendUCIS:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
	$wkUpdateOrders = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				if ($wkField[0] == 'ContactInstanceID' ) {
					if (isset($wkOrder)) {
						$wkUpdateOrders[] = $wkOrder;
					}
					$wkOrder = array();
				}
				$wkOrder [$wkField[0]] = $wkField[1];
			}
		}
		if (isset($wkOrder)) {
			$wkUpdateOrders[] = $wkOrder;
		}
		if (count($wkUpdateOrders) > 0) {
			$wkRequest['UpdateContactInstance'] = $wkUpdateOrders ;
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkOrder = array();
		$wkOrder['ContactInstanceID'] = '2101040';
		$wkOrder['ContactInstanceStatusCode'] = 'DA';
		$wkOrder['UpdateDateTime'] = date(DATE_W3C, time());
		$wkUpdateOrders[] = $wkOrder;
		$wkRequest['UpdateContactInstance'] = $wkUpdateOrders;
	}
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending UCIS to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in UCIS soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkOrders = $wkResponse->UpdateContactInstanceFeedback ;
    	$wkOrders  = isset($wkResponse->UpdateContactInstanceFeedback ) ? $wkResponse->UpdateContactInstanceFeedback : array();
	foreach ($wkOrders as $wkOrderKey => $wkOrderLine) {
		$wkOrdersStatus = (bool) $wkOrderLine->UpdateContactInstanceStatus; 
		$wkOrderId = $wkOrderLine->ContactInstanceID;
		// must write a transaction for these lines
		$transaction = new Transaction_UCISC();
     	  	$transaction->transClass = 'C'; /* response from soap transaction */
		$transaction->reference = "<ContactInstanceID>".$wkOrderId;
		if ($wkOrdersStatus) {
			$transaction->reference .= '|<UpdateContactInstanceStatus>true'   ;
		} else {
			$transaction->reference .= '|<UpdateContactInstanceStatus>false'   ;
		}
		echo("reference:". $transaction->reference . PHP_EOL);
		$log->info("reference:". $transaction->reference );
		// must write a transaction for the line
		$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    // request a label print 
    public function sendPRCL ( $soapMethod , $line , $messageId )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
	echo 'sendPRCL:' . PHP_EOL;
	echo("Method:" . $soapMethod . PHP_EOL);
	$log->info('Method:' . print_r($soapMethod, true));
	echo('line:' . print_r($line, true));
	$t4RecordId = $line['RECORD_ID'];
	$log->info('line:' . print_r($line, true));
	$log->info('line:recordId:' . $t4RecordId);
	echo ('line:recordId:' . $t4RecordId . PHP_EOL);
	echo("messageId:" . $messageId . PHP_EOL);
	$log->info('messageId:' . print_r($messageId, true));
	$wkRequest = array();
	$wkReportOrders = array();
	$wkPages = array();
        $wkResponse =  array(); 
	$trnData = explode($line['TRN_DELIMITER'], $line['TRN_DATA']);
	$wkField = array();
        {
		foreach ($trnData as $trnField) {
			if (substr($trnField,0,1) == '<') {
				// have '<sss>value'
				$wkField = explode('>', substr($trnField,1));
				if ($wkField[0] == 'ContactInstanceID' ) {
					if (isset($wkOrder)) {
						// do I have a cover letter flag
						if (!isset($wkOrder['CoverLetterFlag'])) {
							$wkOrder['CoverLetterFlag'] = false;
						}
						// do I have a supplier flag
						if (!isset($wkOrder['SupplierListFlag'])) {
							$wkOrder['SupplierListFlag'] = false;
						}
						// do I have a printer name
						if (!isset($wkOrder['PrinterName'])) {
							$wkOrder['PrinterName'] = 'printerName';
						}
						$wkReportOrders[] = $wkOrder;
					}
					$wkOrder = array();
				}
				$wkOrder [$wkField[0]] = $wkField[1];
			}
		}
		if (isset($wkOrder)) {
			// do I have a cover letter flag
			if (!isset($wkOrder['CoverLetterFlag'])) {
				$wkOrder['CoverLetterFlag'] = false;
			}
			// do I have a supplier flag
			if (!isset($wkOrder['SupplierListFlag'])) {
				$wkOrder['SupplierListFlag'] = false;
			}
			// do I have a printer name
			if (!isset($wkOrder['PrinterName'])) {
				$wkOrder['PrinterName'] = 'printerName';
			}
			$wkReportOrders[] = $wkOrder;
		}
		if (count($wkReportOrders) > 0) {
			$wkPages['PrintSeparatingPages'] = true;
			$wkPages['ReportGroup'] = $wkReportOrders;
			$wkRequest['SeparatingPageGroup'] = $wkPages;
		}
	}

	if (count($wkRequest) == 0) {
		$log->info("no passed params - using default" );  
		$wkOrder = array();
		$wkOrder['ContactInstanceID'] = '2101040';
		$wkOrder['CoverLetterFlag'] = true;
		$wkOrder['SupplierListFlag'] = true;
		$wkOrder['PrinterName'] = 'printers name'; 
		$wkReportOrders[] = $wkOrder;
		$wkPages['PrintSeparatingPages'] = true;
		$wkPages['ReportGroup'] = $wkReportOrders;
		$wkRequest['SeparatingPageGroup'] = $wkPages;
	}
// have Request             
// 		SeparatingPageGroup
//			ReportGroup
//				long ContactInstanceID
//				boolean CoverLetterFlag
//				boolean SupplierListFlag
//				string PrinterName
//				string TemplateToUse
//			boolean PrintSeparatingPages
	$log->info("request2:" . print_r( $wkRequest, true));  
	echo("request2:" . print_r( $wkRequest, true));  
    
        try
        {
            $wkResponse =  self::$client->$soapMethod($wkRequest);
        } catch (SoapFault $e) {
            echo 'SoapFault ERROR in sending PRCL to soap client ' . PHP_EOL;
            echo $e;
            $log->info('SoapFault ERROR in PRCL soap client ' );
            $log->info( $e);
            return False;
        }

	echo 'back from client:' . PHP_EOL;
	$log->info( 'back from client:' );
	// what if no response in a time limit - have set time limit for this 
	// then set as in error and return 
	// split the response into transactions4 results that do the processing 

	echo("response2:" . print_r( $wkResponse, true));  
	$log->info("response2:" . print_r( $wkResponse, true));  
	
	//$wkOrders = $wkResponse->ReportGroupFeedback ;
    	$wkOrders  = isset($wkResponse->ReportGroupFeedback ) ? $wkResponse->ReportGroupFeedback : array();
	foreach ($wkOrders as $wkOrderKey => $wkOrderLine) {
		$wkOrdersStatus = (bool) $wkOrderLine->PrintStatus; 
		$wkOrderId = $wkOrderLine->ContactInstanceID;
		// must write a transaction for these lines
		$transaction = new Transaction_PRCLC();
     	  	$transaction->transClass = 'C'; /* response from soap transaction */
		$transaction->reference = "<ContactInstanceID>".$wkOrderId;
		if ($wkOrdersStatus) {
			$transaction->reference .= '|<PrintStatus>true'   ;
		} else {
			$transaction->reference .= '|<PrintStatus>false'   ;
		}
		echo("reference:". $transaction->reference . PHP_EOL);
		$log->info("reference:". $transaction->reference );
		// must write a transaction for the line
		//$this->_minder->doTransactionV4Response($transaction, $messageId);
	}

	//var_dump( $wkResponse);  
	echo("<pre>\n");
	//echo("Request:". self::$client->getLastRequest() . PHP_EOL);
	echo("Request:". $this->getLastRequest() . PHP_EOL);
	//echo("Response:". self::$client->getLastResponse() . PHP_EOL);
	echo("Response:". $this->getLastResponse() . PHP_EOL);
	echo("</pre>\n");
	//$log->info("Request:". self::$client->getLastRequest() );
	$log->info("Request:". $this->getLastRequest() );
        //$log->info("Response:". self::$client->getLastResponse() );
        $log->info("Response:". $this->getLastResponse() );
        //$result     =   $this->_minder->updateTransactions4($t4RecordId , array('COMPLETE' => 'T'));
        return True;
    }


    public function getWKTransactions4 ( $messageId , $messageType , $messageComplete )
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $clause = array();
        $clause['TRANSACTIONS4.MESSAGE_ID = ?'] = $messageId;
        $clause['TRANSACTIONS4.TRN_CLASS = ?'] = $messageType;
        $clause['TRANSACTIONS4.COMPLETE = ?'] = $messageComplete;
	$result = True;
        $wkRequest = $this->_minder->getTransactions4 ($clause, 0, 1);
        if (is_array($wkRequest)) {
            foreach ($wkRequest as $line) {
                $tranType = $line['TRN_TYPE'];
		$soapMethod = '';
		$soapProcedure = '';
		$soapInfo = $this->_minder->getSoapMethodForTransaction($tranType) ;
		$log->info('soapInfo:' . print_r($soapInfo, True));
		$soapMethod = $soapInfo['SOAP_METHOD'];
		$soapProcedure = $soapInfo['SOAP_PROCEDURE'];
                // get soap method to call
                if ( ($soapProcedure != '') && ($soapMethod != '')) {
                    //call_user_func($soapProcedure, $soapMethod, $line, $messageId);
                    $result = $result && call_user_func_array(array($this, $soapProcedure), array($soapMethod, $line, $messageId));
                } else {
                    echo ("Transaction " . $tranType . " needs both a soap method and soap_procedure ");
                    $log->info("Transaction " . $tranType . " needs both a soap method and soap_procedure ");
		    $result = False;
                } 
            }
        }
	return $result;
    }







}
