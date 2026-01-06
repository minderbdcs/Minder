<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * PurchaseOrderController
 *
 * @category  Minder
 * @package   Minder
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 * 
 * @deprecated use Orders_PurchaseOrderController
 */
class PurchaseOrderController extends Minder_Controller_Action
{
    
	protected $_showBy = 10;
	/**
	 * The default action - show the home page
	 */
     
    public function init(){
        
        parent::init();

        if ((!$this->minder->isAdmin) && $this->minder->isInventoryOperator) {
            $this->_redirector->setCode(303)
                              ->goto('index', 'index', '', array());
            return;
        }
        
        $this->_setupShortcuts();
    }
     
	public function indexAction ()
	{
		if (($from = $this->getRequest()->getPost('from')) && $from == 'purchase-lines') {
            $this->_action = 'purchase-lines';
            $this->_preProcessNavigation();
            $this->_action = 'index';
            
            if(!empty($_POST['action'])){
                $this->_forward('purchase-lines');    
            }    
        } elseif(($from = $this->getRequest()->getPost('from')) && $from = 'purchase-lines-details'){
            $this->_action = 'purchase-lines-details';
            $this->_preProcessNavigation();
            $this->_action = 'index';
            
            if(!empty($_POST['action'])){
                $this->_forward('purchase-lines-details');
            }
        } else {
            $this->_preProcessNavigation();
        }

		 
		// PURCHASE_ORDER table
		$poTableFields = array(
    		'PURCHASE_ORDER',
			'PERSON_ID',
			'REQUISITION_NO',
			'PO_DATE',
			'PO_REVISION_NO',
			'COMPANY_ID',
			'DIVISION_ID',
			'PO_STATUS',
			'COMMENTS',
			'PO_PRINTED',
			'USER_ID',
			'ORDER_TYPE',
			'PO_LEGACY_DATE',
			'PO_CURRENCY',
			'PO_DUE_DATE',
			'PO_LEGACY_INTERNAL_ID',
			'PO_RECEIVE_WH_ID',
			'PO_LEGACY_MEMO',
			'PO_LEGACY_STATUS',
			'PO_LEGACY_RECVD_DATE',
			'PO_SHIP_TO_ADDRESS3',
			'PO_SHIP_TO_ADDRESS4',
			'PO_SHIP_TO_ADDRESS5',
			'PO_CREATED_BY_NAME',
			'PO_RECEIVE_WH_NAME',
			'PO_LEGACY_STATUS_ID',
			'PO_SHIP_TO_ATTENSION',
			'PO_SHIP_TO_ADDRESSEE',
			'PO_SHIP_TO_PHONE',
			'PO_SHIP_TO_SUBURB',
			'PO_SHIP_TO_STATE',
			'PO_SHIP_TO_POSTCODE',
			'PO_SHIP_TO_COUNTRY',
			'PO_LEGACY_CREATED_BY_NAME',
			'PO_SHIP_TO_ADDRESS1',
			'PO_SHIP_TO_ADDRESS2',
			'PO_LEGACY_CONSIGNMENT',
			'PO_LEGACY_OWNER',
			'PO_LEGACY_OWNER_ID',
			'PO_LEGACY_RECV_ID',
			'PO_RECEIVER',
			'EARLIEST_DATE',
			'COST_CENTER',
			'PO_LEGACY_RECVD_QTY',
			'SUPPLIED_BY_ID',
			'SUPPLIER_CONTACT',
			'PO_SHIP_VIA',
			'PO_SHIP_VIA_SERVICE',
			'PO_SHIPPING_METHOD',
			'PO_FREIGHT_COST',
			'PO_CUSTOM_FEES',
			'PO_STORAGE_FEES',
			'PO_INSURANCE',
			'PO_AMOUNT_PAID',
			'PO_CONTAINER_FEES',
			'PO_UNLOADING_FEES',
			'PO_ADMIN_FEES',
			'PO_OTHER_FEES',
			'PO_TAX_AMOUNT',
			'PO_TAX_RATE',
			'PO_AMOUNT_DUE',
			'PO_LINE_EXTERNAL_NOTES',
			'PO_CONTAINER_NO',
			'PO_VESSEL_NAME',
			'PO_VESSEL_NO',
			'LAST_UPDATE_DATE',
			'LAST_UPDATE_BY',
			'PO_LEGACY_RECEIVE_WH_ID',
			'PO_LEGACY_RECEIVE_WH_NAME',
			'PO_VOYAGE_NO',
			'PO_GRN',
    		'PO_RECEIVE_LOCN_ID'
    		
    		);
    		 
    		$optionFields = $this->minder->getOptionsList('SCN_PORDER');

    		$fields = array();
    		$descFields = array();
    		 
    		foreach ($optionFields as $key=>$val) {
    			$key = explode('|', $key);
    			$index = $key[0];
    			$val = explode('|', $val);
    			$fields[$index] = $val[0];
    			$descFields[$index] = $val[1];

    			if(!in_array($fields[$index], $poTableFields)) {
    				die('Field \''.$fields[$index].'\' from OPTIONS table does not exist in PURCHASE_ORDER table');
    			}
    		}
    		 
    		$fieldsNew = array ();
    		for($i=0;$i<200;$i++) {
    			if(isset($fields[$i])) {
    				$fieldsNew[] = $fields[$i];
    			}
    		}

    		$descFieldsNew = array ();
    		for($i=0;$i<200;$i++) {
    			if(isset($descFields[$i])) {
    				$descFieldsNew[] = $descFields[$i];
    			}
    		}
    		 
    		$fields = $fieldsNew; unset($fieldsNew);
    		$descFields = $descFieldsNew; unset($descFieldsNew);
    		 
    		$this->view->pageTitle = 'SEARCH PURCHASE ORDER:';

    		$this->view->headers = array_combine($fields, $descFields);
    		 
    		// search fields

    		$optionSearchFields = $this->minder->getOptionsList('SER_PORDER');

    		/*
    		 * 	Fields types
    		 *
    		 DP� = Date Picker, �DD� = Drop down
    		 �IN� = Integer, �FP� = Floating Point

    		 */

    		$searchFields       = array();
    		$descSearchFields   = array();
    		$typesSearchFields  = array();
    		$listSearchFields	= array();
    		 
    		foreach ($optionSearchFields as $key=>$val) {
    			$key = explode('|', $key);
    			$index = $key[0];
    			$val = explode('|', $val);
    			$searchFields[$index] = $val[0];
    			$descSearchFields[$index] = $val[1];
    			$typesSearchFields[$index] = $val[2];

    			if (strstr($typesSearchFields[$index], 'DD')!=false) {
    				$result = array();
    				 
    				if ($valueSearchFields[$index]=='') {
    					preg_match_all("(='(.+)')", $typesSearchFields[$index], $result);
    					$valueSearchFields[$index] = $result[1][0];

    					//	    			var_dump($typesSearchFields[$index], $result[1][0]);
    				}
    				$typesSearchFields[$index] = 'DD';
    			}
    		}

    		for($i=1;$i<=9;$i++) {
    			if ($this->getRequest()->getParam($searchFields[$i])!=null) {
    				$valueSearchFields[$i] = $this->getRequest()->getParam($searchFields[$i]);
    			}
    		}
            
            
    		 
    		if(!in_array($searchFields[$index], $poTableFields)) {
    			die('Field '.$searchFields[$index].' from OPTIONS table does not exist in PURCHASE_ORDER table');
    		}
    		 
    		$listSearchFields[2] = minder_array_merge(array(''=>''), $this->minder->getPersonNamesList(array('CO', 'CS', 'IN')));
    		$listSearchFields[5] = $this->minder->getOptionsList('PO_STATUS');
    		$listSearchFields[6] = minder_array_merge(array(''=>''), $this->minder->getCompanyListLimited());
    		
            $this->view->searchFields       = $searchFields;
    		$this->view->descSearchFields   = $descSearchFields;
    		$this->view->typesSearchFields  = $typesSearchFields;
    		$this->view->listSearchFields  	= $listSearchFields;
    		$this->view->valueSearchFields	= $valueSearchFields;
            
            $allowedSqlChunk = array();
    		foreach ($poTableFields as $val) {
    			$allowedSqlChunk[] = $val.' LIKE ? AND ';
    		}
    		 
    		$allowed = array_combine($poTableFields, $allowedSqlChunk);
    		 
    		$this->view->conditions = $this->_setupConditions();

    		$this->session->params['allowed']      =   $allowed;
    		$this->session->params['conditions']   =   $this->_setupConditions();

    		$this->view->statusList = minder_array_merge(array(''=>''), $this->minder->getOptionsList('PO_STATUS'));

    		$order = new PurchaseOrder();
    		$this->view->order = $order;
    		$this->view->warehouseList = array('' => ' ') + $this->minder->getWarehouseList();

    		$temp = $this->minder->getPersonList(array('CS', 'CV', 'CO'));

    		if(count($temp)>0) {
    			$this->view->personList    = array('' => ' ') + array_combine(array_keys($temp), array_keys($temp));
    		} else {
    			$this->view->personList		= array('' => ' ');
    		}
    		$temp = $this->minder->getCompanyList();

    		if(count($temp)>0) {
    			$this->view->companyList   = array('' => ' ') + array_combine(array_keys($temp), array_keys($temp));
    		} else {
    			$this->view->companyList   = array('' => ' ');
    		}

    		$this->view->navigation = $this->session->navigation[$this->_request->getControllerName()][$this->_request->getActionName()];

    		$conditions = $this->_setupConditions(null, $allowed);
    		$clause     = $this->_makeClause($conditions, $allowed);

    		$orders = $this->minder->getPurchaseOrdersCommon($clause, $this->view->navigation['pageselector'], $this->view->navigation['show_by']);

            $action = isset($_POST['action']) ? strtoupper($_POST['action']) : '';
    		 
    		if ($orders['total'] != $this->session->navigation[$this->_controller][$this->_action]['total']) {
    			$this->_postProcessNavigation($orders);
    			$orders = $this->minder->getPurchaseOrdersCommon($clause, $this->view->navigation['pageselector'], $this->view->navigation['show_by']);
    		}

    		switch($action){
    			case 'CLEAR SELECTION':

    				$this->_setConditions(null, 'purchase-lines');
    				$this->_setConditions(null, 'lines-selected');
    				$this->_setConditions(null, 'purchase-lines-details');
         
    				break;
    			case 'REPORT: XLS':
    			case 'REPORT: XML':
    			case 'REPORT: CSV':
    			case 'REPORT: TXT':

    				$data       =   array();
    				$conditions =   $this->_getConditions('purchase-lines');

    				if(count($orders) > 0){
    					foreach($orders['data'] as $order){
    						if(in_array($order['PURCHASE_ORDER'], $conditions)){
    							$data[] =   $order;
    						}
    					}

    					$this->view->data   =   $data;
    					$this->_processReportTo($action);
    				}

    				break;
    		}

    		$this->_postProcessNavigation($orders);
    		$list   = array();

    		foreach ($orders['data'] as $data) {
    			$list[] = new PurchaseOrder($data);
    		}

    		$conditions = $this->_getConditions('purchase-lines');

    		$this->view->data       = $list;
    		$this->view->conditions = $conditions;

    		$totalSelected  =   0;
    		foreach($list as $order){
    			if(in_array($order->id, $conditions)){
    				$totalSelected++;
    			}
    		}

    		if($totalSelected == count($list)){
    			$this->view->checkedAll =   true;
    		} else {
    			$this->view->checkedAll =   false;
    		}
	}


	public function getDeliveryToDockAjaxAction()
	{
		$whId = trim($this->getRequest()->getParam('id'));
		 
		$jsonObject = new stdClass();
		 
		$ids = array(); $names = array();
		 
		if ($whId!='') {
			$locations = $this->minder->getLocationListByClause(array(
	    		'WH_ID = ?' 		=> $whId,
	    		'STORE_AREA = ?'	=> 'RC'
	    		));
       }
		 
		$jsonObject->ids = array_keys($locations); //$ids;
		$jsonObject->names = array_values($locations); //$names;
		 
		die(json_encode($jsonObject));
	}

	public function getAddressListByTypeAjaxAction()
	{   
        
        $company_id = trim($this->getRequest()->getParam('id'));
		$type       = trim($this->getRequest()->getParam('type'));
		
        $jsonObject = new stdClass();
		 
		$ids        = array(); 
        $names      = array(); 
        $record_ids = array();
        
		$addresses  = $this->minder->getAddresses($type, $company_id);
	
        foreach ($addresses as $adrObj) {
			$ids[]          = $adrObj->recordId;
			$names[]        = implode(', ', array($adrObj->line1, $adrObj->line2, $adrObj->city, $adrObj->state, $adrObj->postcode, $adrObj->country));
			$record_ids[]   = $adrObj->recordId;
		}
		 
		$jsonObject->ids        = $ids;
		$jsonObject->names      = $names;
		$jsonObject->record_ids = $record_ids;
		 
		die(json_encode($jsonObject));
	}


	public function getAddressByIdAjaxAction()
	{
		$company_id = trim($this->getRequest()->getParam('id'));
		 
		$jsonObject = new stdClass();
		 
		if($company_id!='') {
			$addresses = $this->minder->getAddress($company_id);
			$jsonObject->address = $addresses;
		} else {
			$jsonObject->address = new Address();
		}
		 
		die(json_encode($jsonObject));
	}

	public function getAddressByTypeAjaxAction()
	{
		$type = trim($this->getRequest()->getParam('id'));
		
        if($type!='') {
			$jsonObject = new stdClass();
			 
			$ids        = array(); 
            $names      = array();
			$address    = $this->minder->getAddresses('MT', $type);
			 
			$address    = current($address);
			
            $jsonObject->address = $address;
		}
			
		die(json_encode($jsonObject));
	}


	public function getServiceByViaAjaxAction()
	{
		$carrier_id = trim($this->getRequest()->getParam('id'));
		 
		$jsonObject = new stdClass();
		 
		$ids = array(); $names = array();
		$services = $this->minder->getShipServiceList($carrier_id);
		 
		 
		$jsonObject->ids = array_keys($services); //$ids;
		$jsonObject->names = array_values($services); //$names;
		 
		die(json_encode($jsonObject));
	}

	public function getPersonsByCompanyAjaxAction()
	{
		$company_id = trim($this->getRequest()->getParam('id'));
		 
		$jsonObject = new stdClass();
		 
		$ids = array(); $names = array();
		$addresses = $this->minder->getAddresses('DT', $company_id);
		foreach ($addresses as $adrObj) {
			$ids[] = $adrObj->recordId;
			$names[] = $adrObj->firstName;
		}

		$jsonObject->ids = $ids;
		$jsonObject->names = $names;
		 
		die(json_encode($jsonObject));
	}

	public function importCsvAction()
	{   
		$conditions         =   $this->_getConditions('purchase-lines');
        $purchaseOrderNo    =   array_shift($conditions);
		$csv                =   array();

        if($purchaseOrderNo != null && $purchaseOrderNo != '' && $purchaseOrderNo != 'select_all') {
			
			$message = null;
			
			
			$purchaseOrder          =   $this->minder->getPurchaseOrderById($purchaseOrderNo);
			$purchaseOrderWhId      =   $purchaseOrder->items['PO_RECEIVE_WH_ID'];
			$purchaseOrderPersonId  =   $purchaseOrder->items['PERSON_ID'];
            $purchaseOrderGrn       =   $purchaseOrder->items['PO_GRN'];
            
			$this->_fileUpload('userfile');
			$csv = file_get_contents($this->session->fileUploaded);
				
			$file = explode("\n", $csv);
			$csv  = array();
			
			for ($i=0; $i<count($file); $i++) {
				$row = trim($file[$i]);
				if($row != '') {
					$row = explode(',', $row);
					foreach ($row as $index => $field) {
						$row[$index] = trim($field);
					}
					$row    = array_pad($row, 13, '');
					$csv[]  = $row;
				}
			}
			
			$row = $csv[0];
				
			$WH_ID 				= $row[0];
			$PO_CONTAINER_NO	= $row[1];
			$GRN_VESSEL_NAME	= $row[2];
			$GRN_VOYAGE_NO		= $row[3];
			$GRN_DUE_DATE		= $row[4];
			$SUPPLIER_ID		= $row[5];
			$OWNER_ID			= $row[6];
			$PROD_ID			= $row[7];
			$PSL_ORDER_QTY		= $row[8];
			$PSL_OTHER1			= $row[9];
			$PSL_OTHER2			= $row[10];
			$PSL_OTHER_DATE3	= $row[11];
			$PSL_OTHER_DATE4	= $row[12];
			
            if(empty($purchaseOrderGrn)){
               
                $transaction = new Transaction_GRNDI();
            
                $transaction->orderNo               = $purchaseOrderNo;
            
                $transaction->carrierId             = $WH_ID . 'INTRANST';
                $transaction->conNoteNo             = 'IN-TRANSIT IMPORT';
                $transaction->vehicleRegistration   = 'INTRANSIT';
                
                $transaction->deliveryTypeId        = 'IP';
                $transaction->orderNo               = $purchaseOrderNo;
                $transaction->orderLineNo           = 1;
                $transaction->hasContainer          = ($PO_CONTAINER_NO != '') ? 'Y' : 'N';
                $transaction->palletOwnerId         = 'N';
                $transaction->palletQty             = $PSL_ORDER_QTY;
                $transaction->crateOwnerId          = '';
                $transaction->crateTypeId           = '';
                $transaction->crateQty              = 0; 
                   
                if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSS', '', 'MASTER    '))) {
                    $this->addError($this->minder->lastError);
                } else {
                    $result = preg_split('#:|\|#si', $result);
                    $grnNo  = $result[1];
                    
                    $this->addMessage('GRNDI: ' . $result[5]);
                }


                $transaction = new Transaction_GRNDL();

                $transaction->orderNo            = $purchaseOrderNo;

                $transaction->grnNo              = $grnNo;
                $transaction->ownerId            = $OWNER_ID;
                $transaction->containerNo        = $PO_CONTAINER_NO;
                $transaction->containerTypeId    = '';
                $transaction->supplierId         = $purchaseOrderPersonId;
                $transaction->deliveryTypeId     = 'IP';
                $transaction->printerId          = 'PA';
                $this->minder->limitPrinter =   $this->minder->checkPrinterLimited();
                $transaction->printerId     =   $this->minder->limitPrinter;    
                $transaction->grnLabelQty        = '0';

                if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSS', '', 'MASTER    '))) {
                    $this->addError($this->minder->lastError);
                } else {
                    $this->addMessage('GRNDL:' . $result);
                }    
            } else {
                $grnNo  =   $purchaseOrderGrn;
            }
			
			
            // update New GRN
			$this->minder->updateGrn($grnNo, 'GRN_DUE_DATE', $GRN_DUE_DATE);
            $this->minder->updateGrn($grnNo, 'VOYAGE_NO', $GRN_VOYAGE_NO);
            $this->minder->updateGrn($grnNo, 'VESSEL_NAME', $GRN_VESSEL_NAME);
            $this->minder->updateGrn($grnNo, 'CONTAINER_NO', $PO_CONTAINER_NO);
            $this->minder->updateGrn($grnNo, 'OWNER_ID', $SUPPLIER_ID);
			$this->minder->updateGrn($grnNo, 'RETURN_ID', $SUPPLIER_ID);
            
            $clause =   array('PURCHASE_ORDER = ? ' => $purchaseOrderNo);
            
            $this->minder->updatePurchaseOrderField($clause, 'PO_CONTAINER_NO', $PO_CONTAINER_NO);
            $this->minder->updatePurchaseOrderField($clause, 'PO_VESSEL_NAME', $GRN_VESSEL_NAME);
            $this->minder->updatePurchaseOrderField($clause, 'PO_VOYAGE_NO', $GRN_VOYAGE_NO);
            $this->minder->updatePurchaseOrderField($clause, 'PO_RECEIVE_WH_NAME', $WH_ID);
				
			$prev_prod_id = null;
			
            foreach ($csv as $row) {
                
				$WH_ID 				= $row[0];	
				$PO_CONTAINER_NO	= $row[1];
				$GRN_VESSEL_NAME	= $row[2];
				$GRN_VOYAGE_NO		= $row[3];
				$GRN_DUE_DATE		= $row[4];
				$SUPPLIER_ID		= $row[5];
				$OWNER_ID			= $row[6];
				$PROD_ID			= $row[7];
				$PSL_ORDER_QTY		= $row[8];
				$PSL_OTHER1			= $row[9];
				$PSL_OTHER2			= $row[10];
				$PSL_OTHER_DATE3	= $row[11];
				$PSL_OTHER_DATE4	= $row[12];

                $PSL_OTHER_DATE3 = str_replace('/', '-', $PSL_OTHER_DATE3);
                $date            = explode('-', $PSL_OTHER_DATE3);
                
                if($date[0] > 12 || strlen($date[2]) == 4){
                    $PSL_OTHER_DATE3 = $date[2] . '-' . $date[1] . '-' . $date[0];
                }
                
                $PSL_OTHER_DATE4 = str_replace('/', '-', $PSL_OTHER_DATE4);         
                $date            = explode('-', $PSL_OTHER_DATE4);
                
                if($date[0] > 12 || strlen($date[2]) == 4){
                    $PSL_OTHER_DATE4 = $date[2] . '-' . $date[1] . '-' . $date[0];
                }
                
                $GRN_DUE_DATE = str_replace('/', '-', $GRN_DUE_DATE);         
                $date         = explode('-', $GRN_DUE_DATE);
                
                if($date[0] > 12 || strlen($date[2]) == 4){
                    $GRN_DUE_DATE = $date[2] . '-' . $date[1] . '-' . $date[0];
                }
                
                if ($PROD_ID != $prev_prod_id) {
					
                    $productInfo                        = current($this->minder->getProductInfo($PROD_ID));
	                $purchaseLineNo 	                = $this->minder->getPurchaseOrderLineId($purchaseOrderNo);
					
                    $line['PURCHASE_ORDER, '] 			= $purchaseOrderNo;
					$line['PO_LINE, '] 					= $purchaseLineNo;
					$line['PROD_ID, '] 					= $PROD_ID;
					$line['UOM_ORDER, '] 				= 'EA';
					$line['PO_LINE_STATUS, '] 			= 'IN';
                    $line['COMMENTS, ']                 = 'Imported by: '. $this->minder->userId .' '.date('Y-m-d H:i:s');
                    $line['PO_LINE_DESCRIPTION, ']      = $productInfo['SHORT_DESC'];
					$line['PO_LINE_DUE_DATE, '] 		= $GRN_DUE_DATE;
                    
					
                    try{
						$result = $this->minder->addPurchaseOrderLine($line);
	                } catch(Exception $ex){
	                    $message    =   $this->minder->lastError;    
	                }
					
	                
					$prev_prod_id           =   $PROD_ID;
                    $newPurchaseOrderLines[]=   $purchaseLineNo;   
				
                }
                
                $data['PURCHASE_ORDER, '] 	= $purchaseOrderNo;
				$data['PO_LINE, '] 			= $purchaseLineNo;
				$data['PSL_OTHER1, '] 		= $PSL_OTHER1;
				$data['PSL_OTHER2, '] 		= $PSL_OTHER2;
				$data['PSL_OTHER_DATE3, '] 	= $PSL_OTHER_DATE3;
				$data['PSL_OTHER_DATE4, '] 	= $PSL_OTHER_DATE4;
				$data['PSL_ORDER_QTY, '] 	= $PSL_ORDER_QTY;
				$data['PSL_STATUS, '] 		= 'IN';
				$data['USER_ID, '] 			= $this->minder->userId; 
				$data['DEVICE_ID, '] 		= $this->minder->deviceId;
				$data['CREATE_DATE, '] 		= date('Y-m-d H:i:s');
				$data['LAST_UPDATE_DATE, ']	= date('Y-m-d H:i:s');
				$data['LAST_UPDATE_BY, '] 	= $this->minder->userId;
				
                
               
				$result = $this->minder->addPurcahseDetailLine($data);
                if(!$result){
                    $message    =   $this->minder->lastError;    
                }
				
				if ($message==null) {
					$this->addMessage('Import successfully.');
				} else {
					$this->addError($message);
				}
			}
            
            // update PO_LINE TOTAL & ORIGINAL_QTY
            foreach($newPurchaseOrderLines as $purchaseLineNo){
                
                $subLineQtySum  =   $this->minder->getDetailQtySum($purchaseOrderNo, $purchaseLineNo);
                $clause         =   array(
                                          'PO_LINE_QTY = ?, ' => $subLineQtySum,  
                                          'ORIGINAL_QTY = ?, '  => $subLineQtySum
                                         );
                
                $this->minder->updatePurchaseOrderLineByOrderId($clause, $purchaseOrderNo, $purchaseLineNo);
            }	
		} else {
			$this->addError('Please, select one order.');
		}


		$this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
		 
	}

	private function _fileUpload($field_name)
	{
		$fileElementName = $field_name;
		//$msg = htmlentities(print_r($_FILES, true));
		$msg = '';

		if(!empty($_FILES[$fileElementName]['error'])) {
			switch($_FILES[$fileElementName]['error']) {
				case '1':
					$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
					break;
				case '2':
					$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
				case '3':
					$error = 'The uploaded file was only partially uploaded';
					break;
				case '4':
					$error = 'No file was uploaded.';
					break;
				case '6':
					$error = 'Missing a temporary folder';
					break;
				case '7':
					$error = 'Failed to write file to disk';
					break;
				case '8':
					$error = 'File upload stopped by extension';
					break;
				case '999':
				default:
					$error = 'No error code avaiable';
			}
		} elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
			$error = 'No file was uploaded.';
		} else {
			$error = '';
			$msg .= " File Name: " . $_FILES[$fileElementName]['name'] . ", ";
			$msg .= " File Size: " . @filesize($_FILES[$fileElementName]['tmp_name']);
			$tempDir = $this->minder->isWinOs()?'C:/':'/tmp/';
			$this->session->fileUploaded = $tempDir . uniqid();
			move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $this->session->fileUploaded);
		}
	}

	public function editAction()
	{
		$action =   isset($_POST['action']) ? strtoupper($_POST['action']) : '';

		switch($action){
			case 'CANCEL':
				$this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
				break;
		}

		// 	analysing request params

		$id 							= trim($this->getRequest()->getParam('order'));
    	$order_status 					= trim($this->getRequest()->getParam('order_status'));
		$create_date 					= trim($this->getRequest()->getParam('create_date'));
		$revision_no 					= trim($this->getRequest()->getParam('revision_no'));
		$printed_date 					= trim($this->getRequest()->getParam('printed_date'));
		$due_date 						= trim($this->getRequest()->getParam('due_date'));
		$cost_centre 					= trim($this->getRequest()->getParam('cost_centre'));
		$currency						= trim($this->getRequest()->getParam('po_currency'));
		$earliest_date 					= trim($this->getRequest()->getParam('earliest_date'));
		$update_date 					= trim($this->getRequest()->getParam('update_date'));

        $session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;
        $tz_to = 'UTC';
        $format = 'Y-m-d h:i:s';

        if($this->minder->isNewDateCalculation() == false){
            //conversion
            $datetime1 = $create_date;
            $dt1 = new DateTime($datetime1, new DateTimeZone($tz_from));
            $dt1->setTimeZone(new DateTimeZone($tz_to));
            $utc1=$dt1->format($format) ;
            $create_date =$utc1;

            //conversion
            $datetime2 = $printed_date;
            $dt2 = new DateTime($datetime2, new DateTimeZone($tz_from));
            $dt2->setTimeZone(new DateTimeZone($tz_to));
            $utc2=$dt2->format($format) ;
            $printed_date =$utc2;

            // conversion
            $datetime3 = $due_date;
            $dt3 = new DateTime($datetime3, new DateTimeZone($tz_from));
            $dt3->setTimeZone(new DateTimeZone($tz_to));
            $utc3=$dt3->format($format) ;
            $due_date =$utc3;

            //conversion
            $datetime4 = $earliest_date;
            $dt4 = new DateTime($datetime4, new DateTimeZone($tz_from));
            $dt4->setTimeZone(new DateTimeZone($tz_to));
            $utc4=$dt4->format($format) ;
            $earliest_date =$utc4;

            //conversion 
            $datetime5 = $update_date;
            $dt5 = new DateTime($datetime5, new DateTimeZone($tz_from));
            $dt5->setTimeZone(new DateTimeZone($tz_to));
            $utc5=$dt5->format($format) ;
            $update_date =$utc5;
        }

		$order_entered_by 				= trim($this->getRequest()->getParam('order_entered_by'));
		$supplied_by_existing 			= trim($this->getRequest()->getParam('supplied_by_existing'));
		$supplied_by_new 				= trim($this->getRequest()->getParam('supplied_by_new'));
		$supplied_by_type 				= trim($this->getRequest()->getParam('person_type'));
		$supplied_first_name 			= trim($this->getRequest()->getParam('supplied_first_name'));
		$supplied_last_name 			= trim($this->getRequest()->getParam('supplied_last_name'));
		$supplied_address1 				= trim($this->getRequest()->getParam('supplied_address1'));
		$supplied_address2 				= trim($this->getRequest()->getParam('supplied_address2'));
		$supplied_address_type 			= trim($this->getRequest()->getParam('supplied_address_type'));
		$supplied_city 					= trim($this->getRequest()->getParam('supplied_city'));
		$supplied_state 				= trim($this->getRequest()->getParam('supplied_state'));
		$supplied_postcode 				= trim($this->getRequest()->getParam('supplied_postcode'));
		$supplied_country 				= trim($this->getRequest()->getParam('supplied_country'));
		$supplied_telephone 			= trim($this->getRequest()->getParam('supplied_telephone'));
		$supplied_contact 				= trim($this->getRequest()->getParam('supplied_contact'));
		$purchased_send_invoice_to 		= trim($this->getRequest()->getParam('purchased_send_invoice_to'));
		$delivery_required_by 			= trim($this->getRequest()->getParam('delivery_required_by'));
		$purchase_raised_by 			= trim($this->getRequest()->getParam('purchase_raised_by'));
		$delivery_warehouse 			= trim($this->getRequest()->getParam('delivery_warehouse'));
		$purchase_requisition_no 		= trim($this->getRequest()->getParam('purchase_requisition_no'));
		 
		$delivery_to_dock		 		= trim($this->getRequest()->getParam('delivery_to_dock'));
		 
		$shipping_address_as_invoice 	= trim($this->getRequest()->getParam('shipping_address_as_invoice'));
		$send_invoice_to_limited 		= trim($this->getRequest()->getParam('send_invoice_to_limited'));
		$invoice_first_name 			= trim($this->getRequest()->getParam('invoice_first_name'));
		$shipping_first_name 			= trim($this->getRequest()->getParam('shipping_first_name'));
		$invoice_last_name 				= trim($this->getRequest()->getParam('invoice_last_name'));
		$shipping_last_name 			= trim($this->getRequest()->getParam('shipping_last_name'));
		$invoice_address1 				= trim($this->getRequest()->getParam('invoice_address1'));
		$shipping_address1 				= trim($this->getRequest()->getParam('shipping_address1'));
		$invoice_address2 				= trim($this->getRequest()->getParam('invoice_address2'));
		$shipping_address2 				= trim($this->getRequest()->getParam('shipping_address2'));
		$invoice_city 					= trim($this->getRequest()->getParam('invoice_city'));
		$shipping_city 					= trim($this->getRequest()->getParam('shipping_city'));
		$invoice_state 					= trim($this->getRequest()->getParam('invoice_state'));
		$shipping_state 				= trim($this->getRequest()->getParam('shipping_state'));
		$invoice_postcode 				= trim($this->getRequest()->getParam('invoice_postcode'));
		$shipping_postcode 				= trim($this->getRequest()->getParam('shipping_postcode'));
		$invoice_country 				= trim($this->getRequest()->getParam('invoice_country'));
		$shipping_country 				= trim($this->getRequest()->getParam('shipping_country'));
		$invoice_telephone 				= trim($this->getRequest()->getParam('invoice_telephone'));
		$shipping_telephone 			= trim($this->getRequest()->getParam('shipping_telephone'));
		$ship_via 						= trim($this->getRequest()->getParam('ship_via'));
		$ship_account_no 				= trim($this->getRequest()->getParam('ship_account_no'));
		$ship_service					= trim($this->getRequest()->getParam('ship_service'));
		$ship_payment					= trim($this->getRequest()->getParam('ship_payment'));
		 
		$good_receipt_no				= trim($this->getRequest()->getParam('good_receipt_no'));
		$vessel_name					= trim($this->getRequest()->getParam('vessel_name'));
		$shipping_container				= trim($this->getRequest()->getParam('shipping_container'));
		$voyage_no						= trim($this->getRequest()->getParam('voyage_no'));
		 
		$shipping_container_no			= trim($this->getRequest()->getParam('shipping_container_no'));
		 
		$freight 						= trim($this->getRequest()->getParam('freight', '0.00'));
		$shipping_container 			= trim($this->getRequest()->getParam('shipping_container', '0.00'));
		$customs_fees 					= trim($this->getRequest()->getParam('customs_fees', '0.00'));
		$container_unloading 			= trim($this->getRequest()->getParam('container_unloading', '0.00'));
		$storage_fees 					= trim($this->getRequest()->getParam('storage_fees', '0.00'));
		$adminstrative_charges 			= trim($this->getRequest()->getParam('adminstrative_charges', '0.00'));
		$insurance 						= trim($this->getRequest()->getParam('insurance', '0.00'));
		$other_charges 					= trim($this->getRequest()->getParam('other_charges', '0.00'));
		$tax 							= trim($this->getRequest()->getParam('tax', '0'));
		$tax_cost 						= trim($this->getRequest()->getParam('tax_cost', '0.00'));
		$deposit_paid 					= trim($this->getRequest()->getParam('deposit_paid', '0.00'));
		$amount_due						= trim($this->getRequest()->getParam('amount_due', '0.00'));
		$internal_instructions			= trim($this->getRequest()->getParam('internal_instructions'));
		$external_instructions  		= trim($this->getRequest()->getParam('external_instructions'));

		 
		if ($this->getRequest()->getParam('do')=='Save') {
			 
			// 			Validate input
			$erors_flag = false;
			 
			$validatorNum = new Zend_Validate_Float();
			$validator255 = new Zend_Validate_StringLength(0, 255);
			$validator20 = new Zend_Validate_StringLength(0, 20);
			$validator10 = new Zend_Validate_StringLength(0, 10);
			$validator2 = new Zend_Validate_StringLength(0, 2);

			if (! $validator10->isValid($supplied_by_existing)) {
				$this->addMessage('Supplied by existing value is too long.');
				$erors_flag = true;
			}
				
			if (! $validator10->isValid($purchase_requisition_no)) {
				$this->addMessage('REQUISITION_NO value is too long.');
				$erors_flag = true;
			}
				
			if (! $validator10->isValid($revision_no)) {
				$this->addMessage('PO_REVISION_NO value is too long.');
				$erors_flag = true;
			}
				
			if (! $validator20->isValid($send_invoice_to_limited)) {
				$this->addMessage('COMPANY_ID value is too long.');
				$erors_flag = true;
			}
				
			if (! $validator255->isValid($internal_instructions)) {
				$this->addMessage('COMMENTS value is too long.');
				$erorrs_flag = true;
			}

			if ($freight=='')
			$freight 						= '0.00';

			if ($shipping_container=='')
			$shipping_container 			= '0.00';

			if ($customs_fees=='')
			$customs_fees 					= '0.00';

			if ($container_unloading=='')
			$container_unloading 			= '0.00';

			if ($storage_fees=='')
			$storage_fees 					= '0.00';

			if ($adminstrative_charges=='')
			$adminstrative_charges 			= '0.00';

			if ($insurance=='')
			$insurance 						= '0.00';

			if ($tax_cost=='')
			$tax_cost 						= '0.00';

			if ($deposit_paid=='')
			$deposit_paid 					= '0.00';
		  
			// create||edit order
			// SUPPLIER ADDRESS

			// check, add new supplier address

			if($supplied_by_existing == '' && $supplied_by_new!='') {

				$items = array();

				$items['PERSON_ID'] = $supplied_by_new;
				$items['ADDR_TYPE'] = 'MT'; // $address_type; @todo
				$items['COMPANY_ID'] = '';
				$items['ADDR_LINE1'] = $supplied_address1;
				$items['ADDR_LINE2'] = $supplied_address2;
				$items['ADDR_CITY'] = $supplied_city;
				$items['ADDR_STATE'] = $supplied_state;
				$items['ADDR_POST_CODE'] = $supplied_postcode;
				$items['ADDR_COUNTRY'] = $supplied_country;
				$items['ADDR_PHONE_NO'] = $supplied_telephone;
				$items['ADDR_FIRST_NAME'] = $supplied_first_name;
				$items['ADDR_LAST_NAME'] = $supplied_last_name;

				$supplier_person_address = new AddressLine($items);

				//		            var_dump($supplier_person_address);die();

				$this->minder->personAddressAdd($supplier_person_address);

				$supplied_by_existing = $supplied_by_new;

				//@todo
				//    		$person_type 					= $supplied_person->personType;
				 
				// update existing
					
			} else {
				 
				$supplier_person_address = current($this->minder->getAddresses('MT', $supplied_by_existing));

				if (
				$supplied_first_name					!= $supplier_person_address->firstName ||
				$supplied_last_name 					!= $supplier_person_address->lastName ||
				$supplied_address1 						!= $supplier_person_address->line1 ||
				$supplied_address2 						!= $supplier_person_address->line2 ||
				$supplied_city 							!= $supplier_person_address->city ||
				$supplied_state 						!= $supplier_person_address->state ||
				$supplied_postcode 						!= $supplier_person_address->postcode ||
				$supplied_country 						!= $supplier_person_address->country ||
				$supplied_telephone 					!= $supplier_person_address->phone ||
				$address_type							!= $supplier_person_address->type
				)
				{
					$items = array();

					$pa_id 				= $supplier_person_address->recordId;
					$items['PERSON_ID'] = $supplied_by_existing;
					$items['ADDR_TYPE'] = 'MT'; // $address_type; @todo
					$items['COMPANY_ID'] = '';
					$items['ADDR_LINE1'] = $supplied_address1;
					$items['ADDR_LINE2'] = $supplied_address2;
					$items['ADDR_CITY'] = $supplied_city;
					$items['ADDR_STATE'] = $supplied_state;
					$items['ADDR_POST_CODE'] = $supplied_postcode;
					$items['ADDR_COUNTRY'] = $supplied_country;
					$items['ADDR_PHONE_NO'] = $supplied_telephone;
					$items['ADDR_FIRST_NAME'] = $supplied_first_name;
					$items['ADDR_LAST_NAME'] = $supplied_last_name;

					$supplier_person_address = new AddressLine($items);
					
                    if ($pa_id!=null && $pa_id!='') {
						$supplier_person_address->id = $pa_id;
						$this->minder->personAddressUpdate($supplier_person_address);
					} else {
						$this->minder->personAddressAdd($supplier_person_address);
					}
				}
				 
				 
			}

		  
			if(true) {

                //************* ONLY FOR TESTING
                //$id = "I0000047";
                //******************************

				if ($id!=null && $id!='') {
					$order  = $this->minder->getPurchaseOrderById($id);
				} else {
					$order = new PurchaseOrder();
					$id = $this->minder->generatePoId();
				}

				 
				$order['itemList'] = array();

				$order['PO_LEGACY_INTERNAL_ID']   = '';
				$order['PO_LEGACY_STATUS']        = '';
				$order['PO_LEGACY_RECVD_DATE']    = '';
				$order['PO_LEGACY_DATE']          = '';
				$order['PO_LEGACY_STATUS_ID']     = '';
				$order['PO_LEGACY_CONSIGNMENT']   = '';
				$order['PO_LEGACY_CREATED_BY_NAME']   = '';
				$order['PO_LEGACY_OWNER_ID']      = '';
				$order['PO_LEGACY_MEMO']     	  = '';

				$order['PO_SHIP_TO_ADDRESS3']     = '';
				$order['PO_SHIP_TO_ADDRESS4']     = '';
				$order['PO_SHIP_TO_ADDRESS5']     = '';
				$order['DIVISION_ID']             = '';
				 
				$order['PURCHASE_ORDER']          = $id;
				$order['PERSON_ID']               = $supplied_by_existing;
				$order['REQUISITION_NO']          = $purchase_requisition_no;

				$order['PO_DATE']          		    = $create_date;
				$order['EARLIEST_DATE']   	 	    = $earliest_date;
				$order['PO_REVISION_NO']    	    = $revision_no;
				$order['COMPANY_ID']                = $purchased_send_invoice_to;
				$order['PO_STATUS']          	    = $order_status;
				$order['COMMENTS']            	    = $internal_instructions;
				$order['PO_PRINTED']          	    = $printed_date;
				$order['USER_ID']               	= $this->minder->userId;
				$order['ORDER_TYPE']          	    = 'PO';
				$order['PO_CURRENCY']               = $currency;
                
                $order['PO_RECEIVE_WH_NAME']        = $this->minder->getWarehouseDescription($delivery_warehouse);
                $order['SUPPLIED_BY_ID']            = $supplied_by_existing;
                $order['PO_SHIP_VIA']               = $ship_via;
                $order['PO_SHIP_VIA_SERVICE']       = $ship_service;
                $order['PO_SHIPPING_METHOD']        = $ship_payment;
                $order['PO_SHIPPING_ACCOUNT']       = $ship_account_no;
                
				if(empty($due_date)){
                    $order['PO_DUE_DATE']           = $this->minder->getClientDate(date('Y-m-d H:i:s'));
                } else {
                    $order['PO_DUE_DATE']           = $due_date;
                }
                
                if(empty($update_date)){
                    $order['LAST_UPDATE_DATE']  =   $this->minder->getClientDate(date('Y-m-d H:i:s'));  
                } else {
                    $order['LAST_UPDATE_DATE']  =   $update_date;   
                }
                
                $order['SUPPLIER_CONTACT']			= $supplied_contact;
                
                $order['PO_RECEIVE_WH_ID']    	    = $delivery_warehouse;

				$order['PO_RECEIVE_LOCN_ID'] 	    = $delivery_to_dock;

				$order['PO_RECEIVER']      			= $delivery_required_by;
				$order['PO_FREIGHT_COST']      		= $freight;
				$order['PO_CUSTOM_FEES']      		= $customs_fees;
				$order['PO_STORAGE_FEES']      		= $storage_fees;
				$order['PO_INSURANCE']      		= $insurance;
				$order['PO_AMOUNT_PAID']      		= $deposit_paid;
				$order['PO_CONTAINER_FEES']      	= $shipping_container;
				$order['PO_UNLOADING_FEES']      	= $container_unloading;
				$order['PO_ADMIN_FEES']      		= $adminstrative_charges;
				$order['PO_OTHER_FEES']      		= $other_charges;
				$order['PO_TAX_AMOUNT']      		= $tax_cost;
				$order['PO_TAX_RATE']      			= $tax;
				$order['PO_AMOUNT_DUE']      		= $amount_due;
				$order['PO_LINE_EXTERNAL_NOTES']  	= $external_instructions;

				$order['PO_GRN']     				= $good_receipt_no;
				$order['PO_VESSEL_NAME']      		= $vessel_name;
				$order['PO_CONTAINER_NO']      		= $shipping_container_no;
				$order['PO_VOYAGE_NO']     			= $voyage_no;

                $order['LAST_UPDATE_DATE']          = $this->minder->getClientDate(date('Y-m-d H:i:s'));
				$order['LAST_UPDATE_BY']            = $this->minder->userId;

				$order['PO_CREATED_BY_NAME']      = $purchase_raised_by;
				$order['PO_RECEIVE_WH_NAME']      = $delivery_warehouse;

				$order['PO_SHIP_TO_ATTENSION']    = $shipping_last_name;
				$order['PO_SHIP_TO_ADDRESSEE']    = $shipping_first_name;
				$order['PO_SHIP_TO_PHONE']        = $shipping_telephone;
				$order['PO_SHIP_TO_ADDRESS1']     = $shipping_address1;
				$order['PO_SHIP_TO_ADDRESS2']     = $shipping_address2;
				$order['PO_SHIP_TO_SUBURB']       = $shipping_city;
				$order['PO_SHIP_TO_STATE']        = $shipping_state;
				$order['PO_SHIP_TO_POSTCODE']     = $shipping_postcode;
				$order['PO_SHIP_TO_COUNTRY']      = $shipping_country;

				$addResult = $this->minder->addPurchaseOrder($order, false);
				if ($addResult) {
					if ($shipping_container!='' && $vessel_name!='') {

						/*
						 *  TRN_TYPE = 'GRND' and TRN_CODE = 'I'  means Receipt In-Transit Order
						 * WH_ID = 'FX'  seems correct if taken Purchase_Order.PO_RECEIVE_WH_ID
						 * LOCN_ID = blank  PDF says 'INTRANST'
						 * OBJECT = '|'  PDF says 'IN-TRANSIT IMPORT'
						 * TRN_DATE = Now()  correct
						 * REFERENCE = 'IP|PO24164||vessel|'  PDF says �IP|�+PURCHASE_ORDER+�||Y|U|0|N||||�
						 'IP|PO24164|| - this is correct but not '|vessel|'. Please consult Transctions details for
						 GRND B but '||Y|U|0|N||||' means |PO Line #|, 'Y' if there Shipping Container No and 'N' if none.
						 The 'U|0| - means Unknown Pallet Owners | 0 (zero) qty of Pallets.
						 * QTY = blank  PDF says 1
						 * COMPLETE = blank / �F� /, ERROR_TEXT = ��,
						 INSTANCE_ID = �MASTER �, EXPORTED = 0,SUB_LOCN_ID = 'MARTINWALK' / PDF say �INTRANSIT�
						 but I see Frank trigger uses 1st 8 charctters so enter 'INTRANST'.
						 * INPUT_SOURCE = blank /* PDF says �SSSSKSKSS�
						 * PERSON_ID = 'Admin', DEVICE_ID = 'CD'
						 *
						 */

						if($good_receipt_no=='' || $good_receipt_no==null) {

							$grndi = new Transaction_GRNDI();

							/*
							 *         return $this->deliveryTypeId .  '|' .
							 $this->orderNo .  '|' .
							 $this->containerNo .  '|' .
							 $this->containerTypeId .  '|' .
							 $this->printerId;
							 */

							$grndi->orderNo			= $id;

							$grndi->carrierId		= $delivery_warehouse.'INTRANST';
							$grndi->conNoteNo		= 'IN-TRANSIT IMPORT';
							$grndi->deliveryTypeId	= 'IP';

							if ($shipping_container_no!='' && $shipping_container_no!=null) {
								$grndi->hasContainer	= 'Y';
							} else {
								$grndi->hasContainer	= 'N';
							}

							$grndi->vehicleRegistration = 'INTRANSIT';
							$grndi->palletOwnerId	= 'U';
							$grndi->palletQty		= '0';
							$grndi->crateOwnerId	= 'N';
							$grndi->supplierId		= $supplied_by_existing;
							$grndi->crateQty		= '1';



							$source					= 'SSSSKSKSS';

							if (false ===
							($grndiResult = $this->minder->doTransactionResponse($grndi, 'Y', $source, '', 'MASTER    '))) {
								$grndiResult .= '.Fail.';
								$this->addError($grndiResult . $this->minder->lastError);
							} else {
								$grndiResult = preg_split('#:|\|#si', $grndiResult);
								$this->session->params[$this->_controller]['grndi'] = $grndiResult;
								$this->session->params[$this->_controller]['grn'] = $grndiResult[1];
								$grnNo = $grndiResult[1];
								$order['PO_GRN']  = $grnNo;
								$this->addMessage('GRNDI:' . $grndiResult[5]);
							}
                            
                            // update GRN fields GRN.VESSEL_NAME, GRN.VOYAGE_NO
                            
                            $this->minder->updateGrn($grnNo, 'VESSEL_NAME', $vessel_name);
                            $this->minder->updateGrn($grnNo, 'VOYAGE_NO', $voyage_no);
            

							/*
							 *  WH_ID = blank / PDF says LEFT i.e �TE� *
							 * LOCN_ID = blank / PDF says SUBSTR i.e. �AMAVO� hence combine WH_ID || LOCN_ID = 'TEAMAVO' /
							 * WH_ID = LEFT i.e �TE�, LOCN_ID = SUBSTR i.e. �AMAVO�,
							 * OBJECT = '|' / PDF says GRN created by preceeding GRND I otherwise where do we store details /,
							 * TRN_TYPE = �GRND� and TRN_CODE = �L�, TRN_DATE = NOW, / all correct /
							 * REFERENCE = 'IP|PO24164|0.00|vessel|� / PDF says = 'IP|�+PURCHASE_ORDER+�|�+ Shipping Container Number
							 i.e. �CGMU4927323� +�||�+User�s Printer ID i.e.�PA� - only the GRN_TYPE = 'IP' and
							 the PO is correct Note the �||� is designed to hold the Shipping Container type but leave empty as
							 User may not know /
							 * QTY = 1 /correct this time /
							 * COMPLETE =�F�, ERROR_TEXT = ��, INSTANCE_ID = �MASTER �, EXPORTED = 0,
							 * SUB_LOCN_ID = 'MARTINWALK' / PDF says SUPPLIER_ID gsn - I am unsure if this is correct at the moment /
							 * INPUT_SOURCE = 'SSSSSSSSS' / PDF says �SSSSKSKSS� /
							 * PERSON_ID = 'admin' , DEVICE_ID = DEVICE_ID.

							 */
								
							$grndl = new Transaction_GRNDL();
							/*
							 *         return $this->deliveryTypeId .  '|' .
							 $this->orderNo .  '|' .
							 $this->containerNo .  '|' .
							 $this->containerTypeId .  '|' .
							 $this->printerId;
							 */
							$grndl->orderNo				= $id;
								
							$grndl->grnNo				= $grnNo;
							$grndl->ownerId				= $purchased_send_invoice_to;
							$grndl->containerNo 		= $shipping_container_no;
							$grndl->containerTypeId		= '';
							$grndl->supplierId			= $supplied_by_existing;
							$grndl->deliveryTypeId 		= 'IP';
							$grndl->grnLabelQty			= '1';
							 
							$source					= 'SSSSKSKSS';
								
							if (false ===
							($grndlResult = $this->minder->doTransactionResponse($grndl, 'Y', $source, '', 'MASTER    '))) {
								$grndlResult .= '.Fail.';
								$this->addError($grndlResult . $this->minder->lastError);
							} else {
								$this->addMessage('GRNDL:' . $grndlResult);
							}
						}
					}
						
					unset($order['itemList']);
					$this->addMessage('Successfully saved');
					$this->_redirector->setCode(303)->goto('edit', 'purchase-order', '', array('order' => $id));
					return;
				} else {
					$this->addError($this->minder->lastError);
					$this->addError('Can\'t save order');
				}
			}

		}  elseif ($id!=null && $id!='') {

			try {
				$order  = $this->minder->getPurchaseOrderById($id);
			} catch (Minder_Exception $e) {
				$this->addError('Order '.$id.' doesn\'t exists.');
				$order = new PurchaseOrder();
			}
			 
			$order_status 					= $order->items["PO_STATUS"];
			$create_date 					= $order->items["PO_DATE"];
			$revision_no 					= $order->items["PO_REVISION_NO"];
			$printed_date 					= $order->items["PO_PRINTED"];
			$po_currency					= $order->items["PO_CURRENCY"];
			$due_date 						= $order->items["PO_DUE_DATE"];
			$cost_centre 					= $order->items["COST_CENTER"];
			$earliest_date 					= $order->items["EARLIEST_DATE"];
			$order_entered_by 				= $order->items["USER_ID"];
			
			$supplied_contact				= $order->items['SUPPLIER_CONTACT'];
			
			$supplied_by_existing 			= $order->items["PERSON_ID"];
			$supplied_by_new 				= $order->items["SUPPLIED_BY_ID"];
			$update_date 					= $order->items["LAST_UPDATE_DATE"];
			$purchased_send_invoice_to 		= $order->items['COMPANY_ID'];
			$supplied_contact 				= $order->items["SUPPLIER_CONTACT"];
			$delivery_required_by 			= $order->items["PO_RECEIVER"];
			$purchase_raised_by 			= $order->items["PO_CREATED_BY_NAME"];
			$delivery_warehouse 			= $order->items["PO_RECEIVE_WH_ID"];
			$purchase_requisition_no 		= $order->items["REQUISITION_NO"];
			$delivery_to_dock				= $order->items['PO_RECEIVE_LOCN_ID'];
			$send_invoice_to_limited 		= $order->items["COMPANY_ID"];
			$shipping_first_name 			= $order->items["PO_SHIP_TO_ADDRESSEE"];
			$shipping_last_name 			= $order->items["PO_SHIP_TO_ATTENSION"];
			$shipping_address1 				= $order->items["PO_SHIP_TO_ADDRESS1"];
			$shipping_address2 				= $order->items["PO_SHIP_TO_ADDRESS2"];
			$shipping_city 					= $order->items["PO_SHIP_TO_SUBURB"];
			$shipping_state 				= $order->items["PO_SHIP_TO_STATE"];
			$shipping_postcode 				= $order->items["PO_SHIP_TO_POSTCODE"];
			$shipping_country 				= $order->items["PO_SHIP_TO_COUNTRY"];
			$shipping_telephone 			= $order->items["PO_SHIP_TO_PHONE"];
			$ship_via 						= $order->items["PO_SHIP_VIA"];
			$ship_account_no 				= $order->items["PO_SHIP_VIA"];
			$ship_service					= $order->items["PO_SHIP_VIA_SERVICE"];
			$ship_payment					= $order->items["PO_SHIPPING_METHOD"];

			$freight 						= $order->items["PO_FREIGHT_COST"];
			$shipping_container 			= $order->items["PO_CONTAINER_FEES"];
			$customs_fees 					= $order->items["PO_CUSTOM_FEES"];
			$container_unloading 			= $order->items["PO_UNLOADING_FEES"];
			$storage_fees 					= $order->items["PO_STORAGE_FEES"];
			$adminstrative_charges 			= $order->items["PO_ADMIN_FEES"];
			$insurance 						= $order->items["PO_INSURANCE"];
			$other_charges 					= $order->items["PO_OTHER_FEES"];
			$tax 							= $order->items["PO_TAX_RATE"];
			$tax_cost 						= $order->items["PO_TAX_AMOUNT"];
			$deposit_paid 					= $order->items["PO_AMOUNT_PAID"];
			$amount_due						= $order->items["PO_AMOUNT_DUE"];

			$good_receipt_no				= $order->items['PO_GRN'];
			$vessel_name					= $order->items['PO_VESSEL_NAME'];
			$shipping_container_no			= $order->items['PO_CONTAINER_NO'];
			$voyage_no						= $order->items['PO_VOYAGE_NO'];

			$legacy_inwards					= $order->items["PO_LEGACY_CONSIGNMENT"];
			$legacy_ssn_prefix				= $order->items["PO_LEGACY_OWNER_ID"];
			$legacy_warehouse				= $order->items["PO_LEGACY_RECEIVE_WH_ID"];
			$legacy_receipt_id				= $order->items["PO_LEGACY_INTERNAL_ID"];
			$legacy_status					= $order->items["PO_LEGACY_STATUS"];
			$legacy_receipt_date			= $order->items["PO_LEGACY_RECVD_DATE"];
			$legacy_text					= $order->items["PO_LEGACY_MEMO"];

			$internal_instructions			= $order->items["COMMENTS"];
			$external_instructions			= $order->items["PO_LINE_EXTERNAL_NOTES"];


			$supplied_address = current($this->minder->getAddresses('MT', $supplied_by_existing));
			$supplied_person = $this->minder->getPerson($supplied_by_existing);

			$person_type 					= $supplied_person->personType;
			$address_type 					= $supplied_address->type;

			$supplied_first_name			= $supplied_address->firstName;
			$supplied_last_name 			= $supplied_address->lastName;
			$supplied_address1 				= $supplied_address->line1;
			$supplied_address2 				= $supplied_address->line2;
			$supplied_city 					= $supplied_address->city;
			$supplied_state 				= $supplied_address->state;
			$supplied_postcode 				= $supplied_address->postcode;
			$supplied_country 				= $supplied_address->country;
			$supplied_telephone 			= $supplied_address->phone;


			$shipping_address_as_invoice 	= $this->getRequest()->getParam('shipping_address_as_invoice');

			$invoice_address1 				= $this->getRequest()->getParam('invoice_address1');
			$invoice_first_name 			= $this->getRequest()->getParam('invoice_first_name');
			$invoice_last_name 				= $this->getRequest()->getParam('invoice_last_name');
			$invoice_address2 				= $this->getRequest()->getParam('invoice_address2');
			$invoice_city 					= $this->getRequest()->getParam('invoice_city');
			$invoice_state 					= $this->getRequest()->getParam('invoice_state');
			$invoice_postcode 				= $this->getRequest()->getParam('invoice_postcode');
			$invoice_country 				= $this->getRequest()->getParam('invoice_postcode');
			$invoice_telephone 				= $this->getRequest()->getParam('invoice_telephone');

			$line_total_sum = $this->minder->getTotalSumPOlines($id);
			if ($line_total_sum==null) {
				$line_total_sum = '0.00';
			}

		} else {
			$order_status 					= 'OP';
			$freight 						= '0.00';
			$shipping_container 			= '0.00';
			$customs_fees 					= '0.00';
			$container_unloading 			= '0.00';
			$storage_fees 					= '0.00';
			$adminstrative_charges 			= '0.00';
			$insurance 						= '0.00';
			$tax_cost 						= '0.00';
			$deposit_paid 					= '0.00';
			$line_total_sum 				= '0.00';
            $create_date                    = $this->minder->getClientDate(date('Y-m-d H:i:s')); 
		}
		 
		if($supplied_by_existing!=null) {
			$person = $this->minder->getPerson($supplied_by_existing);
			$this->view->person_type = $person->personType;

		}


		$default_gst_rate = $this->minder->getControlFields('DEFAULT_GST_RATE');
		$default_gst_rate = $default_gst_rate['DEFAULT_GST_RATE'];
		 
		if ($tax=='' || $tax==null) {
			$tax = $default_gst_rate;
		}
		
		if(trim($delivery_warehouse)=='') {
			$delivery_warehouse = $this->minder->getCurrentWhid();
		}
		
		if(trim($purchased_send_invoice_to)=='') {
			$purchased_send_invoice_to = $this->minder->getCurrentWhid();
		}
		
		$address_list = array();
        $addresses    = $this->minder->getAddresses('MT', $purchased_send_invoice_to);
        foreach ($addresses as $adrObj) {
            $address_list[$adrObj->recordId] = implode(', ', array($adrObj->line1, $adrObj->line2, $adrObj->city, $adrObj->state, $adrObj->postcode, $adrObj->country));;
        }
        $this->view->send_invoice_to_limited_list = minder_array_merge(array('' => ''), $address_list);
        
        $address_list = array();
        $addresses    = $this->minder->getAddresses('DT', $delivery_required_by);
		
        foreach ($addresses as $adrObj) {
	        $address_list[$adrObj->recordId] = implode(', ', array($adrObj->line1, $adrObj->line2, $adrObj->city, $adrObj->state, $adrObj->postcode, $adrObj->country));
		}
                                        
        $this->view->shipping_address_list = minder_array_merge(array('' => ''), $address_list);
        
		foreach ($addresses as $adrObj) {
			if ($adrObj->personId == $send_invoice_to_limited) {
				$invoice_first_name			= $adrObj->firstName;
				$invoice_last_name			= $adrObj->lastName;
				$invoice_address1			= $adrObj->line1;
				$invoice_address2			= $adrObj->line2;
				$invoice_city				= $adrObj->city;
				$invoice_country			= $adrObj->country;
				$invoice_postcode			= $adrObj->postcode;
				$invoice_state				= $adrObj->state;
				$invoice_telephone			= $adrObj->phone;
			}
		}
		
		
		
		$this->view->id 							= $id;
		 
		$this->view->order_status 					= $order_status;
		$this->view->create_date 					= $create_date;
		$this->view->revision_no 					= $revision_no;
		$this->view->printed_date 					= $printed_date;
		$this->view->due_date 						= $due_date;
		$this->view->cost_centre 					= $cost_centre;
		$this->view->earliest_date 					= $earliest_date;
		$this->view->update_date 					= $update_date;
		$this->view->order_entered_by 				= $order_entered_by;
		$this->view->supplied_by_existing 			= $supplied_by_existing;
		$this->view->supplied_by_new 				= $supplied_by_new;
		$this->view->person_type	 				= $person_type;
		$this->view->supplied_first_name			= $supplied_first_name;
		$this->view->supplied_last_name 			= $supplied_last_name;
		$this->view->supplied_address1 				= $supplied_address1;
		$this->view->supplied_address2 				= $supplied_address2;
		$this->view->address_type 					= $address_type;
		$this->view->supplied_city 					= $supplied_city;
		$this->view->supplied_state 				= $supplied_state;
		$this->view->supplied_postcode 				= $supplied_postcode;
		$this->view->supplied_country 				= $supplied_country;
		$this->view->supplied_telephone 			= $supplied_telephone;
		$this->view->supplied_contact 				= $supplied_contact;
		$this->view->purchased_send_invoice_to 		= $purchased_send_invoice_to;
		$this->view->delivery_required_by 			= $delivery_required_by;
		$this->view->purchase_raised_by 			= $purchase_raised_by;
		$this->view->delivery_warehouse 			= $delivery_warehouse;
		$this->view->purchase_requisition_no 		= $purchase_requisition_no;
		$this->view->delivery_to_dock				= $delivery_to_dock;
		$this->view->shipping_address_as_invoice 	= $shipping_address_as_invoice;
		$this->view->send_invoice_to_limited 		= $send_invoice_to_limited;
		$this->view->invoice_first_name 			= $invoice_first_name;
		$this->view->shipping_first_name 			= $shipping_first_name;
		$this->view->invoice_last_name 				= $invoice_last_name;
		$this->view->shipping_last_name 			= $shipping_last_name;
		$this->view->invoice_address1 				= $invoice_address1;
		$this->view->shipping_address1 				= $shipping_address1;
		$this->view->invoice_address2 				= $invoice_address2;
		$this->view->shipping_address2 				= $shipping_address2;
		$this->view->invoice_city 					= $invoice_city;
		$this->view->shipping_city 					= $shipping_city;
		$this->view->invoice_state 					= $invoice_state;
		$this->view->shipping_state 				= $shipping_state;
		$this->view->invoice_postcode 				= $invoice_postcode;
		$this->view->shipping_postcode 				= $shipping_postcode;
		$this->view->invoice_country 				= $invoice_country;
		$this->view->shipping_country 				= $shipping_country;
		$this->view->invoice_telephone 				= $invoice_telephone;
		$this->view->shipping_telephone 			= $shipping_telephone;
		 
		$this->view->good_receipt_no				= $good_receipt_no;
		$this->view->vessel_name					= $vessel_name;
		$this->view->shipping_container				= $shipping_container;
		$this->view->voyage_no						= $voyage_no;
		 
		$this->view->ship_via 						= $ship_via;
		$this->view->ship_account_no 				= $ship_account_no;
		$this->view->ship_service 					= $ship_service;
		$this->view->ship_payment 					= $ship_payment;
		 
		$this->view->freight 						= $freight;
		$this->view->shipping_container_no 			= $shipping_container_no;
		$this->view->customs_fees 					= $customs_fees;
		$this->view->container_unloading 			= $container_unloading;
		$this->view->storage_fees 					= $storage_fees;
		$this->view->adminstrative_charges 			= $adminstrative_charges;
		$this->view->insurance 						= $insurance;
		$this->view->other_charges 					= $other_charges;
		$this->view->tax 							= $tax;
		$this->view->tax_cost 						= $tax_cost;
		$this->view->deposit_paid 					= $deposit_paid;
		$this->view->amount_due						= $amount_due;
		 
		$this->view->legacy_inwards					= $legacy_inwards;
		$this->view->legacy_ssn_prefix				= $legacy_ssn_prefix;
		$this->view->legacy_warehouse				= $legacy_warehouse;
		$this->view->legacy_receipt_id				= $legacy_receipt_id;
		$this->view->legacy_status					= $legacy_status;
		$this->view->legacy_receipt_date			= $legacy_receipt_date;
		$this->view->legacy_text					= $legacy_text;
		 
		$this->view->internal_instructions			= $internal_instructions;
		$this->view->external_instructions			= $external_instructions;

		$this->view->line_total_sum					= $line_total_sum;
		/*

		"
		["insurance"]=>  string(0) ""
		["other_charges"]=>  string(0) ""
		["tax"]=>  string(0) ""
		["tax_cost"]=>  string(0) ""
		["deposit_paid"]=>  string(0) ""
		["amount_due"]=>  string(0) "
		*/
		 
		 
		 
		//		Lists for DDs
		 
		$po_status_list = $this->minder->getOptionsList('PO_STATUS');
		$this->view->po_status_list = $po_status_list;

		$po_currency_list = $this->minder->getOptionsList('CURRENCY');
		$this->view->po_currency_list = $po_currency_list;

		$cost_centre_list = $this->minder->getCostCentreList();
		$this->view->cost_centre_list = $cost_centre_list;
		 
		$supplied_by_existing_list = $this->minder->getPersonNamesList(array('CO','CS','IN'));
		$supplied_by_existing_list_parsed = array();
		foreach($supplied_by_existing_list as $key=>$val) {
			$supplied_by_existing_list_parsed["$key"] = $val;
		}
		$supplied_by_existing_list = $supplied_by_existing_list_parsed;
		$this->view->supplied_by_existing_list =  minder_array_merge(array('' => ''), $supplied_by_existing_list);

		 
		 
		$limitCompany	= $this->minder->limitCompany;
		$limitWarehouse = $this->minder->limitWarehouse;
		$isAdmin		= $this->minder->isAdmin;
		 
		//    	var_dump($limitCompany, $limitWarehouse, $isAdmin);
		 
		$company_list = $this->minder->getCompanyList();
		$warehouse_list = $this->minder->getWarehouseListLimited();
		$this->view->company_list = $company_list;

		$this->view->warehouse_list = $warehouse_list;
		 
		$this->view->delivery_to_dock_list = $this->minder->getLocationListByClause(array('STORE_AREA = ?' =>  'RC'));

    	 
    	 
    	$ship_via_list = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
    	$this->view->ship_via_list = $ship_via_list;
    	 
    	$ship_payment_list = $this->minder->getOptionsList('SHIP_METH');
    	$this->view->ship_payment_list = $ship_payment_list;
    	 
    	$this->renderScript('header.phtml', 'header');
    	$this->render();
    	$this->renderScript('footer.phtml', 'footer');
	    		 
    	return;
	    		 
	    		 
    	// Old code ------ cut here ---------------
    	
	    		$order = new PurchaseOrder();
	    		$order['PO_STATUS'] = 'OP';
	    		$order['PO_LEGACY_OWNER_ID'] = 'FI';
	    		$order['PO_CREATED_BY_NAME'] = $this->minder->userId;
	    		$lines = array();

	    		$this->view->datePickerFields = array(
                'PO_LEGACY_RECVD_DATE',
                'PO_DUE_DATE',
                'PO_DATE',
                'PO_LEGACY_DATE'
                );

                $this->view->lineAllowedFields = array(
                        'PO_LEGACY_LINE'     => 'Legacy Order Line',
                        'PROD_ID'            => 'Item Code',
                        'PO_LINE_QTY'        => 'Qty',
                        'PO_LINE_STATUS'     => 'Status',
                //                        'PO_REVISION_STATUS' => 'Rev.Status'
                );

                $this->view->lineAllowedFields = array(
                        'PO_LEGACY_LINE'     => 'Legacy Order Line',
                        'PROD_ID'            => 'Item Code',
                        'PO_LINE_QTY'        => 'Qty',
                        'PO_LINE_STATUS'     => 'Status',
                //                        'PO_REVISION_STATUS' => 'Rev.Status'
                );


                $list = array();
                for ($i = 0; $i < 10; $i++) {
                	$list['line-' . $i . '-PROD_ID'] = 'PROD_ID';
                }
                $this->view->autocompleteList = $list;
                if ($this->getRequest()->isPost()) {
                	switch (strtoupper($this->getRequest()->getPost('action'))) {
                		case 'SAVE':
                			$flash = array();
                			$purchaseValid    = $this->minder->getValidationParams('PURCHASE');
                			$consignmentValid = $this->minder->getValidationParams('LEGACYGRN');

                			$validator = new Zend_Validate();
                			$validator->addValidator(new Zend_Validate_Regex('~' . $purchaseValid . '~'));
                			if (!$validator->isValid($_POST['PURCHASE_ORDER'])) {
                				$flash = $flash + $validator->getMessages();
                			}

                			$validator = new Zend_Validate();
                			$validator->addValidator(new  Zend_Validate_Regex('~' . $consignmentValid . '~'));
                			if (!$validator->isValid($_POST['PO_LEGACY_CONSIGNMENT'])) {
                				$flash = $flash + $validator->getMessages();
                			}

                			if ($order->save($_POST) && $flash == array()) {
                				foreach ($order->getMandatoryList() as $field) {
                					if ($order[$field] == '') {
                						$flash[] = $field . ' can\'t be empty.';
                					}
                				}
                				if (!$flash) {
                					$prodValid = $this->minder->getValidationParams('PRODUCT_CODE');
                					$validator = new Zend_Validate_Regex('~' . $prodValid . '~');
                					$emptyLine = new PurchaseOrderLine();
                					//$emptyLine->purchaseOrder = $order->id;
                					foreach ($_POST['line'] as $line) {
                						$lastError = false;
                						$temp = new PurchaseOrderLine();
                						$line['PURCHASE_ORDER']      = $order->id;
                						$line['PO_LINE']             = $line['PO_LEGACY_LINE'];
                						$emptyLine['PO_LINE']        = $line['PO_LEGACY_LINE'];
                						$emptyLine['PURCHASE_ORDER'] = $emptyLine->id = $order->id;

                						/*
                						 try {
                						 if (!$validator->isValid($line['PROD_ID'])) {
                						 $flash[] = 'Item Code for ' . $line['PO_LEGACY_LINE'] . ' is not valid.';
                						 $lastError = true;
                						 }
                						 } catch (Exception $e) {
                						 die();
                						 }
                						 */

                						if ($temp->save($line) && !$lastError) {
                							if ($temp != $emptyLine) {
                								foreach ($temp->getMandatoryList() as $field) {
                									if ($temp[$field] == '') {
                										$flash[] = $field . ' for line ' . $line['PO_LEGACY_LINE'] . ' can\'t be empty.';
                									}
                								}
                								$lines[] = $temp;
                							}
                						}
                					}
                					if (!$flash) {
                						try {
                							$order['itemList'] = $lines;
                						} catch (Exception $e) {
                						}
                						if ($this->minder->addPurchaseOrder($order, false)) {
                							unset($order['itemList']);
                							$this->addMessage('Successfully saved');
                							$this->_redirector->setCode(303)
                							->goto('edit', 'purchase-order', '', array('id' => $order->id));
                							return;
                						} else {
                							$this->view->flash = $this->minder->lastError;
                						}
                					}
                				}
                			} else {
                				$flash[] = 'Can\'t save order';
                			}
                			$this->addError($flash);
                			$this->view->flash = $flash;
                			break;
                		case 'RETURN':
                			$this->_redirector->setCode(303)
                			->goto('index', 'purchase-order', '', array());
                			return;
                		default:
                			;
                			break;
                	}

                } else {
                	if ('' != $this->getRequest()->getParam('id')) {
                		if ($this->_getParam('method') == 'delete') {
                			if ($this->_getParam('line') != '') {
                				$result = $this->minder->deletePurchaseOrderLine($this->_getParam('id'), $this->_getParam('line'));
                				if ($result) {
                					$this->addMessage('Order line ' . $this->_getParam('line') . ' successfully deleted');
                				} else {
                					$this->addError('Order line ' . $this->_getParam('line') . ' can\'t be deleted.');
                				}
                			}
                			$this->_redirector->setCode(303)
                			->goto('edit',
                                                 'purchase-order',
                                                 '',
                			array('id' => $this->_getParam('id')));

                		}
                		try {
                			$order  = $this->minder->getPurchaseOrderById($this->getRequest()->getParam('id'));
                		} catch (Exception $e) {
                			$this->addError($e->getMessage());
                		}
                		$temp  = $this->minder->getPurchaseOrderLinesByPurchaseOrder($order->id);
                		foreach ($temp as $val) {
                			$lines[] = new PurchaseOrderLine($val);
                		}
                	}
                }

                $c = sizeof($lines);
                for($i = $c; $i < 10; $i++) {
                	$lines[] = new PurchaseOrderLine();
                }

                $this->view->order = $order;
                $this->view->lines = $lines;


	}

    public function newAction()
    {
    	
        $this->editAction();
    }

    public function setStatusAction()
    {
        $result = array('data' => array(), 'status' => array('msg' => null, 'value' => false));
        if ($this->_request->lines) {
            if ($this->_request->status) {
                $status = $this->_request->status;
                if (array_key_exists($status, array('OP' => 'Opened', 'CL' => 'Closed'))) {
                    $result['status']['value'] = true;
                    $result['status']['msg']   = 'Update completed';
                    foreach ($this->_request->lines as $line) {
                        $result['status']['value'] = $result['status']['value'] &&
                        $result['data'][$line] = $this->minder->updatePurchaseOrderStatus($line, $status);
                    }
                } else {
                    $result['status']['msg'] = 'Status \'' . $status . '\' not allowed.';
                }
            } else {
                $result['status']['msg'] = 'Can\'t set empty status';
            }
        } else {
            $result['status']['msg']   = 'Nothing to update';
        }
        //$result['data']['PO01859'] = false;
        //$result['status']['value'] = false;
        $this->view->data = $result;
    }

    public function searchAction()
    {
        $fieldName  =   $this->getRequest()->getParam('field_name');
        $fieldValue =   $this->getRequest()->getParam('field_value');
        
        switch(strtoupper($fieldName)){
            case 'PROD_ID':
                $productDescriptions   =    $this->minder->getProductShortDescriptionList($fieldValue);
                $this->view->json      =    array('short_desc' => current(array_keys($productDescriptions))); 
                break;
            default:
                $this->view->json   =   null;    
        }
    }
    
    public function purchaseLinesAction(){
        
        $this->_preProcessNavigation();
        
        $id     = $this->_getParam('id');
        $method = $this->_getParam('method');
        $this->view->po_id = $id;
        
        
        $pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];
        
        $allowed      = $this->session->params['allowed'];
        $conditions   = $this->session->params['conditions'];
        $clause       = $this->_makeClause($conditions, $allowed);
        
        $purchaseOrdersList = $this->minder->getPurchaseOrdersCommon($clause, $pageSelector, $showBy);
       
        if($id == 'select_all'){
            if($method == 'true'){
                foreach($purchaseOrdersList['data'] as $order){
                    $conditions[$order['PURCHASE_ORDER']]   =   $order['PURCHASE_ORDER'];
                }    
            } elseif($method == 'false') {
                foreach($purchaseOrdersList['data'] as $order){
                    unset($conditions[$order['PURCHASE_ORDER']]);
                }    
            } elseif($method == 'init'){
                $conditions =   $this->_getConditions('purchase-lines');
            }
            
            $this->_setConditions($conditions, 'purchase-lines');   
        } else {
            $conditions         = $this->_markSelected($purchaseOrdersList, $id, null, $method, $this->_action);
        }
        
        $selectedOrderList  =   array();
        foreach($purchaseOrdersList['data'] as $order){
            if(in_array($order['PURCHASE_ORDER'], $conditions)){
                $selectedOrderList[]    =   $order['PURCHASE_ORDER'];   
            }
        }
        
        $this->session->params['selected_order_list']    =   $selectedOrderList;   
       
        $pageSelector = $this->session->navigation[$this->_controller]['purchase-lines']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['purchase-lines']['show_by'];
        
        
        $fieldsData   = $this->minder->getUserHeaders('SCN_POLINE', array());
    
        if(!empty($selectedOrderList)){
            $purchaseLines= $this->minder->getPurchaseLinesByPurchaseOrders($selectedOrderList, $pageSelector, $showBy, $fieldsData);
        } else {
            $purchaseLines['data']  = array();    
            $purchaseLines['total'] = 0;    
        }
        
        $action = isset($_POST['action']) ? strtoupper($_POST['action']) : '';
      
        switch($action){
            case 'REPORT: XML':
            case 'REPORT: CSV':
            case 'REPORT: TXT':
            case 'REPORT: XLS':
                $this->view->headers    =   $fieldsData['headers'];
                $this->view->data       =   $purchaseLines['data'];
                $this->_processReportTo($action);       
                
                break;
            
            case 'ADD LINE':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('new-line', 'purchase-order', '');
                break;
            
            case 'DELETE LINE':
            
                $conditions =   $this->_getConditions('lines-selected');
                foreach($conditions as $value){
                    list($orderNo, $orderLineNo)    =   explode('_', $value);
                    
                    $purchaseDetailLines = $this->minder->purchaseLineDetailsList(array($value), $pageSelector, $showBy);
                    
                    if($purchaseDetailLines['total'] == 0){
                        $result = $this->minder->deletePurchaseOrderLine($orderNo, $orderLineNo);
                    
                        if(!$result){
                            $this->addError('Error while delete line.');
                            break;
                        } else {
                            $this->addMessage('Order line: ' . $orderLineNo . ' was successfully deleted.');
                            //delete from session
                            unset($conditions[$value]);
                        }    
                    } else {
                        $this->addError('Cannot Delete - Detail Line exists for this Purchase Order Line.');    
                    }
                }
                $this->_setConditions($conditions, 'lines-selected');
                
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                
                break;
            
            case 'IMPORT LINES':
                break;
                
        }
        
        $conditions =   $this->_getConditions('purchase-lines');
        $count      =   0;
        foreach($conditions as $key => $value){
            if($key == $value){
                $count++;
            }
        }
        $this->view->ordersSelected  =   $count;
        $this->view->totalOrders     =   count($purchaseOrdersList['data']);
        
        $conditions =   $this->_getConditions('lines-selected');   
       
        $this->view->conditions         =   $conditions;   
        $this->view->headers            =   $fieldsData['headers'];
        $this->view->purcahseLineList   =   $purchaseLines['data'];
        
        $this->_postProcessNavigation($purchaseLines);
        
        $totalSelected  =   0;
        foreach($purchaseLines['data'] as $line){
            $lineId =   trim($line['PURCHASE_ORDER'] . '_' . $line['PO_LINE']);   
            if(in_array($lineId, $conditions)){
                $totalSelected++;
            }
        }
       
        if($totalSelected == count($purchaseLines['data'])){
            $this->view->checkedAll =   true;
        } else {
            $this->view->checkedAll =   false;    
        }
        
        $this->view->totalSelected  =   $totalSelected;
    }
    
    public function linesSelectedAction(){
        
        $id     = trim($this->_getParam('id'));
        $method = $this->_getParam('method');
        
        
        $pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];
        
        
        $fieldsData         = $this->minder->getUserHeaders('SCN_POLINE', array());
        $selectedOrderList  = $this->session->params['selected_order_list'];
        $purchaseOrdersList = $this->minder->getPurchaseLinesByPurchaseOrders($selectedOrderList, $pageSelector, $showBy, $fieldsData);
        
        if($id == 'select_all'){
            if($method == 'true'){
                foreach($purchaseOrdersList['data'] as $order){
                    $lineId =   trim($order['PURCHASE_ORDER'] . '_' . $order['PO_LINE']);
                    $conditions[$lineId]   =   $lineId;
                }    
            } elseif($method == 'false') {
                foreach($purchaseOrdersList['data'] as $order){
                    $lineId =   trim($order['PURCHASE_ORDER'] . '_' . $order['PO_LINE']);
                    unset($conditions[$lineId]);
                }    
            } elseif($method == 'init'){
                $conditions =   $this->_getConditions('lines-selected');
            }
            
            $this->_setConditions($conditions, 'lines-selected');   
        } else {
            $conditions     =   $this->_getConditions('lines-selected');
                
            if($method == 'true'){
                $conditions[$id]   =   $id;
              
                $this->_setConditions($conditions, 'lines-selected');
            } elseif($method == 'false'){
                $conditions     =   $this->_getConditions('lines-selected');
                unset($conditions[$id]);
            }
        
            $this->_setConditions($conditions, 'lines-selected');
        }
        
        $conditions = $this->_getConditions('lines-selected');
        $json['total_selected_lines']       =   count($conditions);
        $json['total_lines']                =   count($purchaseOrdersList['data']);
        
        $this->view->json   =   $json;
    }
    
    public function detailLinesSelectedAction(){
        
        $id     = trim($this->_getParam('id'));
        $method = $this->_getParam('method');
        
        $conditions =   $this->_getConditions('lines-selected'); 
        $lineList   = array();
     
        foreach($conditions as $item){
            $lineList[] =   $item;
        }
        
        unset($conditions);
        
        $pageSelector   = $this->session->navigation[$this->_controller]['purchase-lines-details']['pageselector'];
        $showBy         = $this->session->navigation[$this->_controller]['purchase-lines-details']['show_by'];
        
        if(!empty($lineList)){
            $lineDetailsList    =   $this->minder->purchaseLineDetailsList($lineList, $pageSelector, $showBy);
        } else {
            $lineDetailsList['data']    =   array();
            $lineDetailsList['total']   =   0;
        }
        
        if($id == 'select_all'){
            if($method == 'true'){
                foreach($lineDetailsList['data'] as $line){
                    $conditions[$line['RECORD_ID']]   =   $line['RECORD_ID'];
                }    
            } elseif($method == 'false') {
                foreach($lineDetailsList['data'] as $line){
                    unset($conditions[$line['RECORD_ID']]);
                }    
            } elseif($method == 'init'){
                $conditions =   $this->_getConditions('detail-lines-selected');
            }
            
            $this->_setConditions($conditions, 'detail-lines-selected');   
        } else {
            $conditions     =   $this->_getConditions('detail-lines-selected');
                
            if($method == 'true'){
                $conditions[$id]   =   $id;
              
                $this->_setConditions($conditions, 'detail-lines-selected');
            } elseif($method == 'false'){
                $conditions     =   $this->_getConditions('detail-lines-selected');
                unset($conditions[$id]);
            }
        
            $this->_setConditions($conditions, 'detail-lines-selected');
        }
        
        $conditions =   $this->_getConditions('detail-lines-selected');
        
        $json['total_details_lines']            =   count($lineDetailsList['data']);
        $json['total_selected_details_lines']   =   count($conditions);
        
        $this->view->json   =   $json;
        
    }
    
    public function purchaseLinesDetailsAction(){
        $this->_preProcessNavigation();
        
        $conditions     =   $this->_getConditions('lines-selected');
        
        
        
        $this->view->headers    =   array('PURCHASE_ORDER'          => 'Order #',
                                          'PO_LINE'                 => 'Line #',
                                          'SSN_ID'                  => 'SSN ID',
                                          'PSL_OTHER1'              => 'Pallet #',
                                          'PSL_OTHER2'              => 'Other 2',
                                          'PSL_OTHER_DATE3'         => 'Use By',
                                          'PSL_OTHER_DATE4'         => 'Packed Date',
                                          'PSL_STATUS'              => 'Status',
                                          'PSL_ORDER_QTY'           => 'Qty',
                                          'LAST_UPDATE_DATE'        => 'Last Update',
                                          'LAST_UPDATE_BY'          => 'User');
        $lineList = array();
        foreach($conditions as $item){
            $lineList[] =   $item;
        }
        
        $pageSelector = $this->session->navigation[$this->_controller]['purchase-lines-details']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['purchase-lines-details']['show_by'];
        
        $conditions   = $this->_getConditions('detail-lines-selected');
        
        if(!empty($lineList)){
             $lineDetailsList    =   $this->minder->purchaseLineDetailsList($lineList, $pageSelector, $showBy);
        } else {
            $lineDetailsList['data']    =   array();
            $lineDetailsList['total']   =   0;
        }
     
        
        $action =   isset($_POST['action']) ? strtoupper($_POST['action']) : '';
      
        switch($action){
            case 'REPORT: XML':
            case 'REPORT: CSV':
            case 'REPORT: TXT':
            case 'REPORT: XLS':
                
                if(!empty($lineDetailsList['data'])){
                    foreach($lineDetailsList['data'] as $line){
                        if(in_array($line['RECORD_ID'], $conditions)){
                            $data[] =   $line;
                        }
                    }
                    $this->view->data   =   $data;
                } else {
                    $this->view->data       =   array();
                }
                $this->_processReportTo($action);
                break;
            
            case 'DELETE':
                
                $conditions =   $this->_getConditions('detail-lines-selected');
                foreach($conditions as $value){
                    
                    $result = $this->minder->deletePurchaseDetailLine($value);
                    
                    if(!$result){
                        $this->addError('Error while delete detail line.');
                        break;
                    } else {
                        $this->addMessage('Order detail line: ' . $value . ' was successfully deleted.');
                        //delete from session
                        unset($conditions[$value]);
                    }
                }
                $this->_setConditions($conditions, 'detail-lines-selected');
                
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                 
                break;
            
            case 'CREATE ISSNS':
                
                $result             =   true;
                $conditions         =   $this->_getConditions('detail-lines-selected');
                
                foreach($conditions as $subLineNo){
                    
                    $purchaseOrderSubLineData   =   $this->minder->getPurchaseDetailLineById($subLineNo);
                   
                    if(empty($purchaseOrderSubLineData['SSN_ID']) || is_null($purchaseOrderSubLineData['SSN_ID'])){
                        
                        $purchaseOrderNo            =   $purchaseOrderSubLineData['PURCHASE_ORDER'];
                        $purchaseOrderLineNo        =   $purchaseOrderSubLineData['PO_LINE'];
                        
                        $purchaseOrderData          =   $this->minder->getPurchaseOrderById($purchaseOrderNo);
                        $purchaseOrderLineData      =   current($this->minder->getPurchaseOrderLineById($purchaseOrderNo, $purchaseOrderLineNo));
                        
                        $purchaseOrderGrn           =   $purchaseOrderData['PO_GRN'];
                        $purchaseOrderContainerNo   =   $purchaseOrderData['PO_CONTAINER_NO'];
                        $purchaseOrderPersonId      =   $purchaseOrderData['PERSON_ID'];
                        $purchaseOrderReceiver      =   $purchaseOrderData['PO_RECEIVER'];
                        $purchaseOrderReceiveWhId   =   $purchaseOrderData['PO_RECEIVE_WH_ID'];
                        
                        
                        if(empty($purchaseOrderGrn)){
                            
                            $transaction                        =   new Transaction_GRNDI();
                            
                            $transaction->orderNo               =   $purchaseOrderNo;
                                
                            $transaction->carrierId             =   $purchaseOrderData['PO_RECEIVE_WH_ID'] . 'INTRANST';
                            $transaction->conNoteNo             =   'IN-TRANSIT IMPORT';                            
                            $transaction->deliveryTypeId        =   'IP';
                            $transaction->hasContainer          =   empty($purchaseOrderContainerNo) ? 'N' : 'Y';
                            $transaction->palletOwnerId         =   'U';
                            $transaction->palletQty             =   '0';
                            $transaction->crateOwnerId          =   'N';
                            $transaction->supplierId            =   $purchaseOrderPersonId;
                            $transaction->crateQty              =   '1';
                            $transaction->vehicleRegistration   =   'INTRANSIT';
                       
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSS', '', 'MASTER    ');
                            
                            if($result == false){
                                $this->addError($this->minder->lastError);
                            }
                            
                            $grnData = preg_split('#:|\|#si', $result);
                            
                            $purchaseOrderGrn   =   $grnData[1];
                            
                            $transaction                         = new Transaction_GRNDL();
                            $transaction->orderNo                = $purchaseOrderNo;
                            $transaction->grnNo                  = $purchaseOrderGrn;
                            $transaction->ownerId                = $purchaseOrderData['PO_RECEIVE_WH_ID'] . 'AMAVO';
                            $transaction->containerNo            = empty($purchaseOrderContainerNo) ? 'N' : 'Y';
                            $transaction->containerTypeId        = '';
                            $transaction->deliveryTypeId         = $purchaseOrderReceiver;
                            $transaction->supplierId             = $purchaseOrderPersonId;
                            $transaction->deliveryTypeId         = 'IP';
                            $transaction->grnLabelQty            = '1';
                            
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSS', '', 'MASTER    ');
                      
                            if($result == false){
                                $this->addError($this->minder->lastError);
                            }   
                        } 
                        
                        $transaction                =   new Transaction_GRNVP();
                       
                        $transaction->locationId    =   current($this->minder->getWhReceiveLocationList());
                        $transaction->productId     =   $purchaseOrderLineData['PROD_ID'];
                        $transaction->totalVerified =   ($purchaseOrderLineData['ORIGINAL_QTY'] !='') ? $purchaseOrderLineData['ORIGINAL_QTY'] : 1;
                        $transaction->grnNo         =   $purchaseOrderGrn;
                        $transaction->deliveryTypeId=   'IP';
                        $transaction->orderNo       =   $purchaseOrderNo;
                        $transaction->orderLineNo   =   $purchaseOrderSubLineData['PO_LINE'];
                        $transaction->qtyOfLabels1  =   1;
                        $transaction->qtyOnLabels1  =   ($purchaseOrderSubLineData['PSL_ORDER_QTY'] !='') ? $purchaseOrderSubLineData ['PSL_ORDER_QTY'] : 1;
                        $transaction->qtyOfLabels2  =   0;
                        $transaction->qtyOnLabels2  =   0;
                        
				        
                        $this->minder->limitPrinter =   $this->minder->checkPrinterLimited();
                        $transaction->printerId     =   $this->minder->limitPrinter;    
                        
//				        var_dump($this->minder->limitPrinter); die();
                        
                        $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
                        
                        $resultSave = $result;
                        $result     = explode('|', $result);
                        
                        $newIssnId  = array_shift($result);
                        
                        $message    = array_pop($result);
                        
                        if($message == 'Processed successfully'){
                            $this->addMessage('Processed successfully');
                        } else {
                            $this->addError('Error while create ISSN for detail line: ' . $subLineNo . ' ' . $message . 'Error:' . $resultSave);
                        }
                        
                        // run UIO transactions
                        
                        if(!empty($purchaseOrderSubLineData['PSL_OTHER1'])){
                            $transaction                =   new Transaction_UIO1A(); 
                            $transaction->locnId        =   current($this->minder->getWhReceiveLocationList());  
                            $transaction->objectId      =   $newIssnId;
                            $transaction->other1Value   =   $purchaseOrderSubLineData['PSL_OTHER1'];
                            $transaction->qty           =   $purchaseOrderSubLineData['PSL_ORDER_QTY'];
                        
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');    
                        }
                        
                        
                        if(!empty($purchaseOrderSubLineData['PSL_OTHER2'])) {
                            $transaction                =   new Transaction_UIO2A(); 
                            $transaction->locnId        =   current($this->minder->getWhReceiveLocationList());  
                            $transaction->objectId      =   $newIssnId;
                            $transaction->other2Value   =   $purchaseOrderSubLineData['PSL_OTHER2'];
                            $transaction->qty           =   $purchaseOrderSubLineData['PSL_ORDER_QTY'];
                        
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
                        }
                        
                        if(!empty($purchaseOrderSubLineData['PSL_OTHER_DATE3'])) {
                            $transaction                =   new Transaction_UIO3A(); 
                            $transaction->locnId        =   current($this->minder->getWhReceiveLocationList());  
                            $transaction->objectId      =   $newIssnId;
                            $transaction->other3Value   =   $purchaseOrderSubLineData['PSL_OTHER_DATE3'];
                            $transaction->qty           =   $purchaseOrderSubLineData['PSL_ORDER_QTY'];
                        
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
                        }
                        
                        if(!empty($purchaseOrderSubLineData['PSL_OTHER_DATE4'])) {
                            $transaction                =   new Transaction_UIO4A(); 
                            $transaction->locnId        =   current($this->minder->getWhReceiveLocationList());  
                            $transaction->objectId      =   $newIssnId;
                            $transaction->other4Value   =   $purchaseOrderSubLineData['PSL_OTHER_DATE4'];
                            $transaction->qty           =   $purchaseOrderSubLineData['PSL_ORDER_QTY'];
                        
                            $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSK', '', 'MASTER    ');
                        }
                        
                        $data       = array('SSN_ID = ?, ' => $newIssnId);
                        $message    = '';
                        
                        try{
                            $result     = $this->minder->updatePurchaseDetailLine($subLineNo, $data);
                            $message    = $this->minder->lastError;
                        
                        } catch(Exception $ex){
                            $message    =   $this->minder->lastError;    
                        }
                        
                        if($result){
                            $this->addMessage('Detail line was successfully updated.');
                        } else {
                            $this->addError('Error while update detail line: ' . $message);
                        }
                        
                        // print new ISSN
                        $printerObj = $this->minder->getPrinter(null, $this->minder->limitPrinter);
                        $issnData   = $this->minder->getIssnForPrint($newIssnId);
                        
                        try{
                            $result    =    $printerObj->printIssnLabel($issnData);
                        
                            if($result['RES'] < 0){
                                $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
                            } else {
                                $this->addMessage($result['ERROR_TEXT']);
                            }             
                        } catch(Exception $ex){
                               $this->addError($ex->getMessage());
                        }
                    }
                }
                
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                break;
        }
        
        $this->view->purcahseLineDetailsList   =   $lineDetailsList['data'];
        $this->view->conditions                =   $conditions;  
        $this->view->totalSelectedDetailLines  =   count($conditions);
       
        if(count($conditions) == count($lineDetailsList['data'])){
            $this->view->allDetailsCheck    =   'checked';
        } else {
            $this->view->allDetailsCheck    =   false;     
        }
         
        $this->_postProcessNavigation($lineDetailsList);
    }
    
    public function editLineAction(){
        
        $paramData  =   $this->getRequest()->getParam('line');
        
        list($orderNo, $orderLineNo)    =   explode('_', $paramData);
        
        
        $action = isset($_POST['action']) ? strtoupper($_POST['action']) : '';
        switch($action){
            case 'SAVE':
            
                $allowed    =   array('prod_id'                 =>  'PROD_ID = ?, ',
                                      'po_line_description'     =>  'PO_LINE_DESCRIPTION = ?, ',
                                      'original_qty'            =>  'ORIGINAL_QTY = ?, ',
                                      'po_line_qty'             =>  'PO_LINE_QTY = ?, ',
                                      'unit_price'              =>  'UNIT_PRICE = ?, ',
                                      'po_line_discount'        =>  'PO_LINE_DISCOUNT = ?, ',
                                      'po_line_total'           =>  'PO_LINE_TOTAL = ?, ',
                                      'po_currency'             =>  'PO_CURRENCY = ?, ',
                                      'gst_rate'                =>  'GST_RATE = ?, ',
                                      'gst_value'               =>  'GST_VALUE = ?, ',
                                      'gst_code'                =>  'GST_CODE = ?, ',
                                      'uom_order'               =>  'UOM_ORDER = ?, ',
                                      'earliest_date'           =>  'EARLIEST_DATE = ?, ',
                                      'po_line_due_date'        =>  'PO_LINE_DUE_DATE = ?, ',
                                      'po_line_status'          =>  'PO_LINE_STATUS = ?, ',
                                      'requisition_no'          =>  'REQUISITION_NO = ?, ',
                                      'comments'                =>  'COMMENTS = ?, ',
                                      'po_line_external_notes'  =>  'PO_LINE_EXTERNAL_NOTES = ?, ',
                                      'po_line_options'         =>  'PO_LINE_OPTIONS = ?, ',
                                      'po_line_qty_f'           =>  'PO_LINE_QTY_F = ?, ',
                                      'po_line_lotno_list'      =>  'PO_LINE_LOTNO_LIST = ?, ',
                                      'po_line_status_tf'       =>  'PO_LINE_STATUS_TF = ?, ',
                                      'po_line_customer_id'     =>  'PO_LINE_CUSTOMER_ID = ?, ',
                                      'po_line_customer_name'   =>  'PO_LINE_CUSTOMER_NAME = ?, ',
                                      'po_legacy_recv_id'       =>  'PO_LEGACY_RECV_ID = ?, ',
                                      'po_revision_status'      =>  'PO_REVISION_STATUS = ?, ',
                                      'po_legacy_line'          =>  'PO_LEGACY_LINE = ?, ',
                                      'last_update_date'        =>  'LAST_UPDATE_DATE = ?, ',
                                      'last_update_by'          =>  'LAST_UPDATE_BY = ?, ');
                
                $validate   =   true;
                $validators =   $this->_getValidators('purchase-line');
                $conditions = $this->_setupConditions(null, $allowed);
                
                
                foreach($conditions as $key => $value){
                    $validator  =   $validators[$key];
                    
                    if(!is_null($validator) && !$validator->isValid($value)){
                        $validate   =   false;
                        $this->addError('Field: ' . strtoupper($key) . ' ' . current($validator->getMessages()));    
                    }
                }
                
                $clause     = $this->_makeClause($conditions, $allowed);
        
                $result     = $this->minder->updatePurchaseOrderLineByOrderId($clause, $orderNo, $orderLineNo);
          
                if($result){
                    $this->addMessage('Line was successfully updated.');
                } else {
                    $this->addError('Error while update order line.');
                }
                
                if($validate){
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                }
                    
                break;
            case 'CANCEL':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                break;
        }
        
        $purchaseOrderLine  =   current($this->minder->getPurchaseOrderLineById($orderNo, $orderLineNo));
        
          
        $this->view->currencyList       =   minder_array_merge(array('AUSTRALIAN DOLLAR' => 'AUSTRALIAN DOLLAR'), $this->minder->getOptionsList('CURRENCY'));        
        $this->view->poLineStatusList   =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('PO_STATUS'));        
        $this->view->uomOrderList       =   minder_array_merge(array('' => ''), $this->minder->getUoms());
        $this->view->productList        =   minder_array_merge(array('' => ''), $this->minder->getFilteredByCompnayProdList($purchaseOrderCompanyId));
   
        $this->view->purchaseOrderLine  =   $purchaseOrderLine;
        $this->view->gstRate            =   $this->minder->defaultControlValues['DEFAULT_GST_RATE'];
        
    }
    
    public function newLineAction(){
        
        $action = isset($_POST['action']) ? strtoupper($_POST['action']) : '';
        switch($action){
            case 'SAVE':
                
                $allowed    =   array(
                                        'purchase_order'        =>  'PURCHASE_ORDER, ',
                                        'po_line'               =>  'PO_LINE, ',
                                        'prod_id'               =>  'PROD_ID, ',
                                        'po_line_description'   =>  'PO_LINE_DESCRIPTION, ',
                                        'original_qty'          =>  'ORIGINAL_QTY, ',
                                        'po_line_qty'           =>  'PO_LINE_QTY, ',
                                        'unit_price'            =>  'UNIT_PRICE, ',
                                        'po_line_discount'      =>  'PO_LINE_DISCOUNT, ',
                                        'po_line_total'         =>  'PO_LINE_TOTAL, ',
                                        'po_currency'           =>  'PO_CURRENCY, ',
                                        'gst_rate'              =>  'GST_RATE, ',
                                        'gst_value'             =>  'GST_VALUE, ',
                                        'uom_order'             =>  'UOM_ORDER, ',
                                        'earliest_date'         =>  'EARLIEST_DATE, ',
                                        'po_line_due_date'      =>  'PO_LINE_DUE_DATE, ',
                                        'po_line_status'        =>  'PO_LINE_STATUS, ',
                                        'requisition_no'        =>  'REQUISITION_NO, ',
                                        'comments'              =>  'COMMENTS, ',
                                        'po_line_external_notes'=>  'PO_LINE_EXTERNAL_NOTES, ',
                                        'po_line_options'       =>  'PO_LINE_OPTIONS, ',
                                        'po_line_qty_f'         =>  'PO_LINE_QTY_F, ',
                                        'po_line_lotno_list'    =>  'PO_LINE_LOTNO_LIST, ',
                                        'po_line_status_tf'     =>  'PO_LINE_STATUS_TF, ',
                                        'po_line_customer_id'   =>  'PO_LINE_CUSTOMER_ID, ',
                                        'po_line_customer_name' =>  'PO_LINE_CUSTOMER_NAME, ',
                                        'po_legacy_recv_id'     =>  'PO_LEGACY_RECV_ID, ',
                                        'po_revision_status'    =>  'PO_REVISION_STATUS, ',
                                        'po_legacy_line'        =>  'PO_LEGACY_LINE, ',
                                        'last_update_date'      =>  'LAST_UPDATE_DATE, ',
                                        'last_update_by'        =>  'LAST_UPDATE_BY, '
                                     );
                
                $validate       =   true;
                $validators     =   $this->_getValidators('purchase-line');
                $conditions     =  $this->_setupConditions(null, $allowed);
                
                
                foreach($conditions as $key => $value){
                    $validator  =   $validators[$key];
                    
                    if(!is_null($validator) && !$validator->isValid($value)){
                        $validate   =   false;
                        $this->addError('Field: ' . strtoupper($key) . ' ' . current($validator->getMessages()));    
                    }
                }
                
                $data       = $this->_makeClause($conditions, $allowed);
                $message    = '';
             
                try{
                    $result     = $this->minder->addPurchaseOrderLine($data);
                    $message    = $this->minder->lastError;
          
                } catch(Exception $ex){
                    $message    =   $this->minder->lastError;    
                }
                
                if($result){
                    $this->addMessage('New order line was successfully added.');
                } else {
                    $this->addError('Error while add order line.');
                }
                
                if($validate){
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                }
                
                $this->view->prodId         =   isset($_POST['prod_id'])                ? $_POST['prod_id']             : ''; 
                $this->view->poLine         =   isset($_POST['po_line_description'])    ? $_POST['po_line_description'] : ''; 
                $this->view->originalQty    =   isset($_POST['original_qty'])           ? $_POST['original_qty']        : ''; 
                $this->view->poLineQty      =   isset($_POST['po_line_qty'])            ? $_POST['po_line_qty']         : ''; 
                $this->view->unitPrice      =   isset($_POST['unit_price'])             ? $_POST['unit_price']          : ''; 
                $this->view->poLineDiscount =   isset($_POST['po_line_discount'])       ? $_POST['po_line_discount']    : ''; 
                $this->view->poLineTotal    =   isset($_POST['po_line_total'])          ? $_POST['po_line_total']       : ''; 
                $this->view->poCurrency     =   isset($_POST['po_currency'])            ? $_POST['po_currency']         : ''; 
                $this->view->gstRate        =   isset($_POST['gst_rate'])               ? $_POST['gst_rate']            : ''; 
                $this->view->gstValue       =   isset($_POST['gst_value'])              ? $_POST['gst_value']           : ''; 
                $this->view->gstCode        =   isset($_POST['gst_code'])               ? $_POST['gst_code']            : ''; 
                $this->view->uomOrder       =   isset($_POST['uom_order'])              ? $_POST['uom_order']           : ''; 
                $this->view->earliestDate   =   isset($_POST['earliest_date'])          ? $_POST['earliest_date']       : ''; 
                $this->view->poLineDueDate  =   isset($_POST['po_line_due_date'])       ? $_POST['po_line_due_date']    : ''; 
                $this->view->poLineStatus   =   isset($_POST['po_line_status'])         ? $_POST['po_line_status']      : ''; 
                $this->view->requisitionNo  =   isset($_POST['requisition_no'])         ? $_POST['requisition_no']      : '';
                $this->view->comments       =   isset($_POST['comments'])               ? $_POST['comments']            : ''; 
                $this->view->poNotes        =   isset($_POST['po_line_external_notes']) ? $_POST['po_line_external_notes'] : ''; 
                $this->view->other1         =   isset($_POST['po_line_other1'])         ? $_POST['po_line_other1']      : ''; 
                $this->view->other2         =   isset($_POST['po_line_other2'])         ? $_POST['po_line_other2']      : ''; 
                $this->view->poOptions      =   isset($_POST['po_line_options'])        ? $_POST['po_line_options']     : ''; 
                $this->view->lineQtyF       =   isset($_POST['po_line_qty_f'])          ? $_POST['po_line_qty_f']       : ''; 
                $this->view->lineLotno      =   isset($_POST['po_line_lotno_list'])     ? $_POST['po_line_lotno_list']  : ''; 
                $this->view->statusTf       =   isset($_POST['po_line_status_tf'])      ? $_POST['po_line_status_tf']   : ''; 
                $this->view->customerId     =   isset($_POST['po_line_customer_id'])    ? $_POST['po_line_customer_id'] : ''; 
                $this->view->customerName   =   isset($_POST['po_line_customer_name'])  ? $_POST['po_line_customer_name'] : ''; 
                $this->view->recvId         =   isset($_POST['po_legacy_recv_id'])      ? $_POST['po_legacy_recv_id']   : ''; 
                $this->view->revisionStatus =   isset($_POST['po_revision_status'])     ? $_POST['po_revision_status']  : ''; 
                $this->view->legacyLine     =   isset($_POST['po_legacy_line'])         ? $_POST['po_legacy_line']      : ''; 
                $this->view->updateDate     =   isset($_POST['last_update_date'])       ? $_POST['last_update_date']    : ''; 
                $this->view->updateBy       =   isset($_POST['last_update_by'])         ? $_POST['last_update_by']      : ''; 
              
                
                break;
            case 'CANCEL':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                break;
        }
        
        
        $conditions =   $this->_getConditions('purchase-lines');
        
        $purchaseOrderNo        =   array_shift($conditions);
        $purchaseOrderLineNo    =   $this->minder->getNewPurchaseLineNo($purchaseOrderNo);
        $purchaseOrderData      =   $this->minder->getPurchaseOrderById($purchaseOrderNo);
        $purchaseOrderCompanyId =   $purchaseOrderData['COMPANY_ID'];
        
        $this->view->currencyList       =   minder_array_merge(array('AUSTRALIAN DOLLAR' => 'AUSTRALIAN DOLLAR'), $this->minder->getOptionsList('CURRENCY'));        
        $this->view->poLineStatusList   =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('PO_STATUS'));        
        
        $this->view->purchaseOrderNo    =   $purchaseOrderNo;
        $this->view->purchaseOrderLineNo=   $purchaseOrderLineNo;
        
        $this->view->gstRate            =   empty($_POST['gst_rate'])           ? $this->minder->defaultControlValues['DEFAULT_GST_RATE'] : $_POST['gst_rate']; 
        $this->view->poLineStatus       =   empty($_POST['po_line_status'])     ? 'OP'                                                    : $_POST['po_line_status'];    
        $this->view->earliestDate       =   empty($_POST['earliest_date'])      ? date('Y-m-d')                                           : $_POST['po_line_status'];    
        $this->view->poLineDueDate      =   empty($_POST['po_line_due_date'])   ? date('Y-m-d')                                           : $_POST['po_line_status'];    
        $this->view->uomOrderList       =   minder_array_merge(array('' => ''), $this->minder->getUoms());
        $this->view->productList        =   minder_array_merge(array('' => ''), $this->minder->getFilteredByCompnayProdList($purchaseOrderCompanyId));
    
    }
    
    public function newLineDetailAction(){
        
        $purchaseOrderNo        =   $this->getRequest()->getParam('purchase_order');
        $purchaseOrderLineNo    =   $this->getRequest()->getParam('po_line');
        
        $action                 =   isset($_POST['action']) ? strtoupper($_POST['action']) : '';
        switch($action){
            case 'SAVE':
                
                $allowed    =   array('purchase_order'  =>  'PURCHASE_ORDER, ',
                                      'po_line'         =>  'PO_LINE, ',
                                      'ssn_id'          =>  'SSN_ID, ',
                                      'psl_other1'      =>  'PSL_OTHER1, ',
                                      'psl_other2'      =>  'PSL_OTHER2, ',
                                      'psl_other_date3' =>  'PSL_OTHER_DATE3, ',
                                      'psl_other_date4' =>  'PSL_OTHER_DATE4, ',
                                      'psl_status'      =>  'PSL_STATUS, ',
                                      'psl_order_qty'   =>  'PSL_ORDER_QTY, ',
                                      'psl_received_qty'=>  'PSL_RECEIVED_QTY, ',
                                      'last_update_date'=>  'LAST_UPDATE_DATE, ',
                                      'last_update_by'  =>  'LAST_UPDATE_BY, ',
                                      'device_id'       =>  'DEVICE_ID, '
                                      );
                
                $conditions = $this->_setupConditions(null, $allowed);
                $data       = $this->_makeClause($conditions, $allowed);
                $data       = array_merge($data, array('USER_ID, ' => $this->minder->userId));  
                $message    = '';
                
                $purchaseLineData       =   current($this->minder->getPurchaseOrderLineById($purchaseOrderNo, $purchaseOrderLineNo));
             
                $purchaseOrderLineQty   =   !empty($purchaseLineData['ORIGINAL_QTY']) ? $purchaseLineData['ORIGINAL_QTY'] : 0;
                $detailLinesTotalQty    =   $this->minder->getDetailQtySum($purchaseOrderNo, $purchaseOrderLineNo);
                $newDetailLineQty       =   $this->getRequest()->getParam('psl_order_qty');
                $newDetailLineQty       =   !empty($newDetailLineQty)                 ? $newDetailLineQty                 : 0;
             
                if(($detailLinesTotalQty + $newDetailLineQty) <= $purchaseOrderLineQty){
                    try{
                        $result     = $this->minder->addPurcahseDetailLine($data);
                        $message    = $this->minder->lastError;
              
                    } catch(Exception $ex){
                        $message    =   $this->minder->lastError;    
                    }
                    
                    if($result){
                        $this->addMessage('New detail line was successfully added.');
                    } else {
                        $this->addError('Error while add detail line: ' . $message);
                    }    
                } else {
                    $this->addError('Total Qty of PO_SUB_LINE\'s must be less or equal to PURCHASE_ORDER_LINE.ORIGINAL_QTY');
                }
                
           
            
            case 'CANCEL':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                break;
        }
        
        $conditions                                 =   $this->_getConditions('lines-selected');
        list($purchaseOrderNo, $purchaseLineNo)     =   explode('_', array_shift($conditions));
      
        $this->view->purchaseOrderNo    =   $purchaseOrderNo;   
        $this->view->purchaseLineNo     =   $purchaseLineNo;   
        
        $this->view->pslStatusList  =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('POL_STATUS'));
            
    }
    
    public function editLineDetailAction(){
        
        $lineNo =   $this->getRequest()->getParam('object_no');
        
        if(!empty($lineNo)){
            $lineDetails    =   $this->minder->getPurchaseDetailLineById($lineNo);
        } else {
            $lineDetails    =   array();
        }
        
        $purchaseOrderNo        =   $this->getRequest()->getParam('purchase_order');
        $purchaseOrderLineNo    =   $this->getRequest()->getParam('po_line');
        
       
        $action = isset($_POST['action']) ? strtoupper($_POST['action']) : '';
        switch($action){
            case 'SAVE':
                $lineNo     =   $this->getRequest()->getParam('record_id');
                
                $allowed    =   array('purchase_order'  =>  'PURCHASE_ORDER = ?, ',
                                      'po_line'         =>  'PO_LINE = ?, ',
                                      'ssn_id'          =>  'SSN_ID = ?, ',
                                      'psl_other1'      =>  'PSL_OTHER1 = ?, ',
                                      'psl_other2'      =>  'PSL_OTHER2 = ?, ',
                                      'psl_other_date3' =>  'PSL_OTHER_DATE3 = ?, ',
                                      'psl_other_date4' =>  'PSL_OTHER_DATE4 = ?, ',
                                      'psl_status'      =>  'PSL_STATUS = ?, ',
                                      'psl_order_qty'   =>  'PSL_ORDER_QTY = ?, ',
                                      'psl_received_qty'=>  'PSL_RECEIVED_QTY = ?, ',
                                      'last_update_date'=>  'LAST_UPDATE_DATE = ?, ',
                                      'last_update_by'  =>  'LAST_UPDATE_BY = ?, ',
                                      'device_id'       =>  'DEVICE_ID = ?, '
                                      );
                
                $conditions = $this->_setupConditions(null, $allowed);
                $data       = $this->_makeClause($conditions, $allowed);
                $message    = '';
                
                $purchaseLineData       =   current($this->minder->getPurchaseOrderLineById($purchaseOrderNo, $purchaseOrderLineNo));
             
                
                $purchaseOrderLineQty   =   !empty($purchaseLineData['ORIGINAL_QTY']) ? $purchaseLineData['ORIGINAL_QTY'] : 0;
                $detailLinesTotalQty    =   $this->minder->getDetailQtySum($purchaseOrderNo, $purchaseOrderLineNo);
                $newDetailLineQty       =   $this->getRequest()->getParam('psl_order_qty');
                $newDetailLineQty       =   !empty($newDetailLineQty)                 ? $newDetailLineQty                 : 0;
                
                if(($detailLinesTotalQty + $newDetailLineQty) <= $purchaseOrderLineQty){
                    try{
                        $result     = $this->minder->updatePurchaseDetailLine($lineNo, $data);
                        $message    = $this->minder->lastError;
              
                    } catch(Exception $ex){
                        $message    =   $this->minder->lastError;    
                    }
                    
                    if($result){
                        $this->addMessage('Detail line was successfully updated.');
                    } else {
                        $this->addError('Error while update detail line: ' . $message);
                    }
                } else {
                    $this->addError('Total Qty of PO_SUB_LINE\'s must be less or equal to PURCHASE_ORDER_LINE.ORIGINAL_QTY');    
                }  
                
                
                
            case 'CANCEL':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)->goto('index', 'purchase-order', '');
                break;
        }
        
        $this->view->lineDetails    =   $lineDetails;
        $this->view->pslStatusList  =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('POL_STATUS'));
  
    }
    
    
    private function _getValidators($action){
        
        switch($action){
            case 'purchase-line':
                $validators     =   array(
                                            'purchase_order'        =>  new Zend_Validate_StringLength(0, 10),
                                            'po_line'               =>  new Zend_Validate_StringLength(0, 4),
                                            'prod_id'               =>  new Zend_Validate_StringLength(0, 30),
                                            'po_line_description'   =>  null,
                                            'original_qty'          =>  null,
                                            'po_line_qty'           =>  null,
                                            'unit_price'            =>  null,
                                            'po_line_discount'      =>  null,
                                            'po_line_total'         =>  null,
                                            'po_currency'           =>  new Zend_Validate_StringLength(0, 20),
                                            'gst_rate'              =>  null,
                                            'gst_value'             =>  null,
                                            'uom_order'             =>  new Zend_Validate_StringLength(0, 2),
                                            'earliest_date'         =>  new Zend_Validate_StringLength(0, 20),
                                            'po_line_due_date'      =>  new Zend_Validate_StringLength(0, 20),
                                            'po_line_status'        =>  new Zend_Validate_StringLength(0, 2),
                                            'requisition_no'        =>  new Zend_Validate_StringLength(0, 10),
                                            'comments'              =>  new Zend_Validate_StringLength(0, 255),
                                            'po_line_options'       =>  new Zend_Validate_StringLength(0, 255),
                                            'po_line_qty_f'         =>  null,
                                            'po_line_lotno_list'    =>  null,
                                            'po_line_status_tf'     =>  new Zend_Validate_StringLength(0, 1),
                                            'po_line_customer_id'   =>  new Zend_Validate_StringLength(0, 10),
                                            'po_line_customer_name' =>  new Zend_Validate_StringLength(0, 40),
                                            'po_legacy_recv_id'     =>  null,
                                            'po_revision_status'    =>  new Zend_Validate_StringLength(0, 2),
                                            'po_legacy_line'        =>  new Zend_Validate_StringLength(0, 4),
                                            'last_update_date'      =>  new Zend_Validate_StringLength(0, 20),
                                            'last_update_by'        =>  new Zend_Validate_StringLength(0, 10)
				);
				break;
			case 'purchase-detail-line':
				break;
		}

		return $validators;
	}
    
    /*protected function _setupShortcuts() {
        
        $shortcuts = array(
                            'Sales Orders' => array(
                                'Sales Orders'     => $this->view->url(array('controller' => 'pick-order2', 'action' => 'index'), null, true),
                                'Sales Invoices'   => $this->view->url(array('controller' => 'pick-invoice', 'action' => 'index'), null, true),
                                'New Sales Order'  => $this->view->url(array('controller' => 'pick-order2', 'action' => 'new', 'pick_order_type' => 'SO'), null, true),
                                'Fast Sales Order' => $this->view->url(array('module' => 'warehouse', 'controller' => 'products', 'action' => 'index', 'from' => 'fso', 'without' => 1), null, true),
                                'Import Mapped Order' => $this->view->url(array('controller' => 'mapping', 'action' => 'index'), null, true),
                            ),
                            'Transfer Orders' => array(
                                'Transfer Orders'   => $this->view->url(array('controller' => 'transfer-order', 'action' => 'index'), null, true),
                                'Import Mapped Order' => $this->view->url(array('controller' => 'mapping', 'action' => 'index'), null, true)
                            ),
                            'Purchase Orders' => array(
                    //                'New Purchase Order' => $this->view->url(array('controller' => 'purchase-order', 'action' => 'edit'), null, true),
                                'Purchase Orders'       => $this->view->url(array('controller' => 'purchase-order', 'action' => 'index'), null, true),
                                'Import Mapped Order' => $this->view->url(array('controller' => 'mapping', 'action' => 'index'), null, true)
                            ),
            'Person Details'            => array(
                'PERSON'                =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'default'), null, true)
            )
        );
        
        $this->view->shortcuts = $shortcuts;
   }*/
}
