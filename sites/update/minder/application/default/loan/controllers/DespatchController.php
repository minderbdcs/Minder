<?php
/**
 * Minder
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

include_once('functions.php');

/**
 * PickOrderController
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Strelnikov Evgeniy <strelnikov.evgeniy@binary-studio.com@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class DespatchController extends Minder_Controller_Action
{
    protected $_showBy = 5;

    public function init()
    {
        parent::init();
        if (!isset($this->session->params[$this->_controller]['index']['pick_orders'])) {
            $this->session->params[$this->_controller]['index']['pick_orders'] = array();
        }

        $this->session->returnOrder = $this->_controller;
        $this->_setupShortcuts();
    
  }

    /**
     * Diplay the sales order homepage (/pick-order/index)
     *
     * This page lists the sales orders. It optionally takes input from the user
     * allowing the limit the results by searching for certain sales orders.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->pageTitle     = 'Despatching Orders';
        
        
        if (($from = $this->getRequest()->getPost('from')) && $from == 'lines-action') {
            $this->_action = 'lines';
            $this->_preProcessNavigation();
            $this->_action = 'index';
        } else {
            $this->_preProcessNavigation();
        }
        
        /*
        $this->view->headers = array('PICK_ORDER'           => 'Order #',
                                     'DESPATCH_LOCATION'    => 'Location',
                                     'PICK_PRIORITY'        => 'Priority',
                                     'PICK_STATUS'          => 'Status',
                                     'PICK_DUE_DATE'        => 'Due Date',
                                     'PERSON_ID'            => 'Customer ID',
                                     'D_FIRST_NAME'         => 'Delivery To',
                                     'CUSTOMER_PO_WO'       => 'Customer Ref.',
                                     'SHIP_VIA'             => 'Ship Via',
                                     'WH_ID'                => 'WH',
                                     'COMPANY_ID'           => 'Company ID',
                                     'SPECIAL_INSTRUCTIONS2'=> 'Reason');
        */
        
        $fieldsData          = $this->minder->getUserHeaders('SCN_AWDESP');
    
        $this->view->headers = $fieldsData['headers'];
        
        $allowed = array(
                            'order_no'            => 'PICK_ORDER.PICK_ORDER LIKE ? AND ',
                            'company_id'          => 'PICK_ORDER.COMPANY_ID = ? AND ',
                            'ship_via'            => 'PICK_ORDER.SHIP_VIA = ? AND ',
                            'despatch_location'   => 'PICK_ORDER.DESPATCH_LOCATION = ? AND ',
                            'contact_name'        => 'PICK_ORDER.CONTACT_NAME LIKE ? AND ',
                            'sold_to'             => 'PICK_ORDER.PERSON_ID = ? AND ',
                        );
        
        $this->view->pickStatusDescription = $this->minder->getPickOrderStatusList();
        
        
        $conditions     = $this->_setupConditions(null, $allowed);
        $criteria       = $this->_makeClause($conditions, $allowed);
        $criteria       = array_merge(array("(PICK_ITEM.PICK_LINE_STATUS = 'DS' OR 
                                              PICK_ITEM.PICK_LINE_STATUS = 'DL' OR 
                                              PICK_ITEM.PICK_LINE_STATUS = 'DC' OR
                                              PICK_ITEM.PICK_LINE_STATUS = 'DI') AND  " => '',
                                            ), $criteria);
      
        $pageSelector   = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy         = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
    
        $this->view->pickOrders = array();
      
        try {
            $pickOrders                 = $this->minder->getDespatchedPickOrders($criteria, $pageSelector, $showBy, $fieldsData);
            $this->view->pickOrders     = $orderList = $pickOrders['data'];
     
        } catch (Exception $e) {
            $this->session->conditions[$this->_controller][$this->_action] = $this->view->conditions = array();
            $this->addError('Error occured while searching.');
        }
        
        if (isset($_POST['action'])) {
            $action = strtoupper($_POST['action']);
          
            switch($action) {
            
                case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: TXT':
                case 'REPORT: XML':
                    
                    $selectedOrder      = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $this->view->data   = array(); 
                    foreach($orderList as $key => $value) {
                        if(in_array($key, $selectedOrder)) {
                            $this->view->data[] = $value;
                        }        
                    }
                    $this->_processReportTo($action);
                    break;
                
                case 'CONNOTE':
               
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('connote',
                                             'despatch'
                                             );
                    break;
               case 'CLEAR SELECTION':
                    $this->session->params[$this->_controller][$this->_action]['pick_orders']   =   array();
                    $this->session->conditions[$this->_controller][$this->_action]              =   array();
                    
                    $this->_setConditions(array(), 'lines');
             
                    break;
            }    
        }
        
        
        
        
        
        $this->_postProcessNavigation($pickOrders);
        
        $this->view->conditions   = $this->_getConditions();
        $this->view->shipViaList  = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $this->view->locationList = minder_array_merge(array('' => ''), $this->minder->getLocationListByClause(array('STORE_AREA = ? ' => 'DS'), false));
        $this->view->companyList  = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
        $this->view->soldToList   = minder_array_merge(array('' => ''), $this->minder->getPersonFromPickOrder(array('(PERSON.PERSON_TYPE IN (?, ?, ?) AND PICK_ORDER.PICK_ORDER_TYPE = ?) OR (PERSON.PERSON_TYPE IN (?) AND PICK_ORDER.PICK_ORDER_TYPE = ?)' => array('CO', 'CU', 'IN', 'SO', 'RP', 'TO')))); 
          
        $this->view->soldTo       = isset($_POST['sold_to']) ? $_POST['sold_to'] : '';
        $this->view->orderNo      = isset($_POST['order_no']) ? $_POST['order_no'] : '';
        $this->view->contactName  = isset($_POST['contact_name']) ? $_POST['contact_name'] : '';
        $this->view->shipVia      = isset($_POST['ship_via']) ? $_POST['ship_via'] : '';
        $this->view->companyId    = isset($_POST['company_id']) ? $_POST['company_id'] : '';
        $this->view->despatch     = isset($_POST['despatch_location']) ? $_POST['despatch_location'] : '';
        
        
        $this->render('/waiting/index'); 
    }
    
    public function connoteAction() {
   
        $this->_preProcessNavigation();
        
        if (!empty($_POST['action1'])) {
            $action = strtoupper($_POST['action1']); 
         
            switch ($action) {
                

                case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
                
                
                    $this->view->headers = array(
                                                    'PICK_ORDER'            => 'Order #',
                                                    'PICK_LABEL_NO'         => 'Label #',
                                                    'PICK_ORDER_LINE_NO'    => 'Line #',
                                                    'PROD_ID'               => 'Product ID',
                                                    'SSN_ID'                => 'SSN',
                                                    'DESCRIPTION'           => 'Description',
                                                    'QTY_PICKED'            => 'Qty',
                                                    'WH_ID'                 => 'WH',
                                                    'DESPATCH_LOCATION'     => 'Location',
                                                    'PICK_LINE_STATUS'      => 'Status',
                                                    'SPECIAL_INSTRUCTIONS2' => 'Special Instructions (Internal)' 
                                                );

                    $ordersList    = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList = $this->minder->getPickItemsByPickOrders($ordersList);
                    $this->view->data = array();
                    foreach($pickItemsList as $key => $value) {
                        if(in_array($key, $selectedLines)) {
                             $this->view->data[] =   $value;
                        }    
                    }
                 
                    $this->_processReportTo($action);
                    break;
                 
                 case 'CANCEL':
                 	if(isset($this->session->assembly_returnurl)) {
                 		$this->_redirect($this->session->assembly_returnurl);	
                 	} else {
	                    $this->_redirector = $this->_helper->getHelper('Redirector');
	                    $this->_redirector->setCode(303)
	                                      ->goto('index',
	                                             'despatch');
                 	}
                    break;
                
            }
        }

        /**
         * Calculate and mark stuff for Pick Orders.
         */

        $id     = $this->_getParam('id');
        $method = $this->_getParam('method');

        // @TODO: Change this to $this->_preProcessNavigation() when code be moved from indexAction().
        if (!isset($this->session->navigation[$this->_controller]['connote'])) {
            $this->session->navigation[$this->_controller]['connote']['show_by']      = $this->_showBy;
            $this->session->navigation[$this->_controller]['connote']['pageselector'] = $this->_pageSelector;
        }

        $allowed = array(
            'PICK_ORDER'            => 'PICK_ORDER = ? AND ',
            'CONTACT_NAME'          => 'CONTACT_NAME = ? AND ',
            'CUSTOMER_PO_WO'        => 'CUSTOMER_PO_WO = ? AND ',
            'PERSON_ID'             => 'PERSON_ID = ? AND ',
            'P_FIRST_NAME'          => 'P_FIRST_NAME = ? AND ',
            'COMPANY_ID'            => 'COMPANY_ID = ? AND ',
            'PICK_DUE_DATE'         => 'PICK_DUE_DATE = ? AND ',
            'PICK_STATUS'           => 'PICK_STATUS = ? AND ',
            'CREATED_BY'            => 'CREATED_BY = ? AND ',
            'PICK_PRIORITY'         => 'PICK_PRIORITY = ? AND ',
            'SHIP_VIA'              => 'SHIP_VIA = ? AND ',
            'DESPATCH_LOCATION'     => 'DESPATCH_LOCATION = ? AND ',
            'D_CITY'                => 'D_CITY = ? AND ',
            'D_STATE'               => 'D_STATE = ? AND ',
            'D_COUNTRY'             => 'D_COUNTRY = ? AND ',
            'FROM_CREATE_DATE'      => 'FROM_CREATE_DATE = ? AND ',
            'TO_CREATE_DATE'        => 'TO_CREATE_DATE = ? AND ',
            'SPECIAL_INSTRUCTIONS1' => 'SPECIAL_INSTRUCTIONS1 = ? AND ',
            'SPECIAL_INSTRUCTIONS2' => 'SPECIAL_INSTRUCTIONS2 = ? AND ',
            'PICK_LABEL_NO'         => 'PICK_LABEL_NO = ? AND ',
        );

        if (!isset($this->session->conditions[$this->_controller]['index'])) {
            $this->session->conditions[$this->_controller]['index'] = array();
        }

        $clause = array_intersect_key($this->session->conditions[$this->_controller]['index'], $allowed);
        $clause = array_merge(array("(PICK_ITEM.PICK_LINE_STATUS = 'DS' OR 
                                  PICK_ITEM.PICK_LINE_STATUS = 'DL' OR 
                                  PICK_ITEM.PICK_LINE_STATUS = 'DC' OR
                                  PICK_ITEM.PICK_LINE_STATUS = 'DI') AND  " => '',
                              ), $clause);
        
        $pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];  
        
        $fieldsData = $this->minder->getUserHeaders('SCN_AWDESP'); 
        $lines      = $this->view->minder->getDespatchedPickOrders($clause, $pageSelector, $showBy, $fieldsData);
        
        $lines      = $lines['data'];
        $numRecords = count($lines);

        $conditions = $this->_getConditions('index');
        
        if ($id == 'select_all') {
            if ('true' == $method) {
                foreach($lines as $key => $line) {
                    $conditions[$key] = $key;
                    $this->session->params[$this->_controller]['index']['pick_orders'] = array_unique(array_merge($this->session->params[$this->_controller]['index']['pick_orders'], array($key)));
                }
                arsort($this->session->params[$this->_controller]['index']['pick_orders']);
            } elseif ('false' == $method) {
                foreach($lines as $key => $line) {
                    unset($conditions[$key]);
                    if (false !== ($key1 = array_search($key, $this->session->params[$this->_controller]['index']['pick_orders']))) {
                        unset($this->session->params[$this->_controller]['index']['pick_orders'][$key1]);
                    }
                }
            }
        } else {
            $value = $this->getRequest()->getParam('value');
            if (is_null($value)) {
                $value = $id;
            }
            if ('true' == $method) {
                $conditions[$id] = $value;
                $this->session->params[$this->_controller]['index']['pick_orders'] = array_unique(array_merge($this->session->params[$this->_controller]['index']['pick_orders'], array($value)));
                arsort($this->session->params[$this->_controller]['index']['pick_orders']);
            } elseif ('false' == $method) {
                unset($conditions[$id]);
                if (false !== ($key = array_search($id, $this->session->params[$this->_controller]['index']['pick_orders']))) {
                    unset($this->session->params[$this->_controller]['index']['pick_orders'][$key]);
                }
            }
        }
        
        $this->_setConditions($conditions, 'connote');
       
        $pickOrdersCount = 0;
        foreach ($lines as $key => $line) {
            if (array_key_exists($key, $conditions)) {
                $pickOrdersCount++;
            }
        }

        /**
         * Calculate and mark stuff for Pick Items (Lines).
         */

        $this->calcLinesAction();
        
        if (!isset($this->view->pickItems)) {
            $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
            $this->view->pickItems = array();
            if (count($pickOrders)) {
                $clause                = array('PICK_ITEM_DETAIL.QTY_PICKED > 0 AND ' => ' ');
                $this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders, $clause);
            }
        }
      
        if (isset($this->view->counters)) {
            $this->view->counters += array('orders_selected' => $pickOrdersCount);
        } else {
            $this->view->counters = array('orders_selected' => $pickOrdersCount);
        }

        $this->view->headers = array(
                                           'PICK_ORDER'            => 'Order #',
                                           'PICK_LABEL_NO'         => 'Label #',
                                           'PICK_ORDER_LINE_NO'    => 'Line #',
                                           'PROD_ID'               => 'Product ID',
                                           'SSN_ID'                => 'ISSN',
                                           'DESCRIPTION'           => 'Description',
                                           'QTY_PICKED'            => 'Qty',
                                           'WH_ID'                 => 'WH',
                                           'DESPATCH_LOCATION'     => 'Location',
                                           'PICK_LINE_STATUS'      => 'Status',
                                           'SPECIAL_INSTRUCTIONS2' => 'Special Instructions (Internal)'
                                            
                                    );            
       
       $this->_action = 'connote';
       $this->_postProcessNavigation($this->view->pickItems);

        
       $this->view->pickItems = array_slice($this->view->pickItems,
       $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
       $this->view->maxno);
        
       /**
        * Calculate count of unic orders. Must = 1
        */
        $totalUnicOrders = array();
        foreach ($this->view->pickItems as $key => $line) {
            $totalUnicOrders[$line['PICK_ORDER']]   =   $line->items['PICK_ORDER'];
        }
        
        if(count($totalUnicOrders) > 1){
            $this->addWarning('You have more than 1 Order selected, please <a href="./" style="color:#003399; text-decoration:underline;">go back</a> and use "CLEAR SELECTION" button.');    
        }
        
        $this->render('/waiting/connote'); 
    }
    
    public function despatchAction() {
        
            $action = isset($_POST['action1']) ? $_POST['action1'] : $_GET['action1']; 
            $action = strtoupper($action); 
            
            switch ($action) {
                

                case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
                
                    $this->view->headers    =   array('PICK_ORDER'              =>  'PICK_ORDER',
                                                      'PICK_ORDER_LINE_NO'      =>  'PICK_ORDER_LINE_NO',
                                                      'CONTACT_NAME'            =>  'CONTACT_NAME',
                                                      'PROD_ID'                 =>  'PROD_ID',
                                                      'SPECIAL_INSTRUCTIONS1'   =>  'SPECIAL_INSTRUCTIONS1',
                                                      'SPECIAL_INSTRUCTIONS2'   =>  'SPECIAL_INSTRUCTIONS2',
                                                      'SHIP_VIA'                =>  'SHIP_VIA',
                                                      'PICK_ORDER_QTY'          =>  'PICK_ORDER_QTY',
                                                      'PICK_LINE_DUE_DATE'      =>  'PICK_LINE_DUE_DATE',
                                                      'PICK_STARTED'            =>  'PICK_STARTED',
                                                      'WH_ID'                   =>  'WH_ID',
                                                      'PICK_LOCATION'           =>  'PICK_LOCATION',
                                                      'PICK_LINE_PRIORITY'      =>  'PICK_LINE_PRIORITY',
                                                      
                                                      'SSN_ID'                  =>  'SSN_ID',
                                                      'PICK_DETAIL_STATUS'      =>  'PICK_DETAIL_STATUS',
                                                      'QTY_PICKED'              =>  'QTY_PICKED',
                                                      'DESPATCH_LOCATION'       =>  'DESPATCH_LOCATION',
                                                      'USER_ID'                 =>  'USER_ID',
                                                      'CREATE_DATE'             =>  'CREATE_DATE',
                                                      'FROM_WH_ID'              =>  'FROM_WH_ID',
                                                      'FROM_LOCN_ID'            =>  'FROM_LOCN_ID');
                    
                    
                    $itemNo     =   $this->session->itemNo;
                    $itemData   =   $this->minder->getPickItemById($itemNo);
                    $itemDetails=   $this->minder->getAllPickItemDetail($itemNo);
                    
                    $data[]     =   array('PICK_ORDER'              =>  $itemData['PICK_ORDER'],
                                          'PICK_ORDER_LINE_NO'      =>  $itemData['PICK_ORDER_LINE_NO'],
                                          'CONTACT_NAME'            =>  $itemData['CONTACT_NAME'],
                                          'PROD_ID'                 =>  $itemData['PROD_ID'],
                                          'SPECIAL_INSTRUCTIONS1'   =>  $itemData['SPECIAL_INSTRUCTIONS1'],
                                          'SPECIAL_INSTRUCTIONS2'   =>  $itemData['SPECIAL_INSTRUCTIONS2'],
                                          'SHIP_VIA'                =>  $itemData['SHIP_VIA'],
                                          'PICK_ORDER_QTY'          =>  $itemData['PICK_ORDER_QTY'],
                                          'PICK_LINE_DUE_DATE'      =>  $itemData['PICK_LINE_DUE_DATE'],
                                          'PICK_STARTED'            =>  $itemData['PICK_STARTED'],
                                          'WH_ID'                   =>  $itemData['WH_ID'],
                                          'PICK_LOCATION'           =>  $itemData['PICK_LOCATION'],
                                          'PICK_LINE_PRIORITY'      =>  $itemData['PICK_LINE_PRIORITY'],
                                          'PICK_LINE_PRIORITY'      =>  $itemData['PICK_LINE_PRIORITY'],
                                          
                                          'SSN_ID'                  =>  $itemDetails[0]['SSN_ID'],
                                          'PICK_DETAIL_STATUS'      =>  $itemDetails[0]['PICK_DETAIL_ID'],
                                          'QTY_PICKED'              =>  $itemDetails[0]['QTY_PICKED'],
                                          'DESPATCH_LOCATION'       =>  $itemDetails[0]['DESPATCH_LOCATION'],
                                          'USER_ID'                 =>  $itemDetails[0]['USER_ID'],
                                          'CREATE_DATE'             =>  $itemDetails[0]['CREATE_DATE'],
                                          'FROM_WH_ID'              =>  $itemDetails[0]['FROM_WH_ID'],
                                          'FROM_LOCN_ID'            =>  $itemDetails[0]['FROM_LOCN_ID']
                                          );
                    $this->view->data = $data;
                    
                    
                    $this->_processReportTo($action);
               
                    return;
                
                case 'LOAD':
                
                    $this->session->itemNo = '';
                
                    $itemNo     = $this->_request->getParam('id') ;
                    $itemData   = $this->minder->getPickItemById($itemNo);
                    $orderNo    = $itemData->items['PICK_ORDER'];
                    $orderData  = $this->minder->getPickOrder($orderNo, 'ALL');
                    
                    $this->session->itemNo  = $itemNo;
                    $this->session->orderNo = $orderNo;
                    
                    $this->view->firstName      = empty($orderData->dFirstName)     ? $orderData->pFirstName    : $orderData->dFirstName;
                    $this->view->addresLine1    = empty($orderData->dAddressLine1)  ? $orderData->pAddressLine1 : $orderData->dAddressLine1;
                    $this->view->addresLine2    = empty($orderData->dAddressLine2)  ? $orderData->pAddressLine2 : $orderData->dAddressLine2;;
                    $this->view->dCity          = empty($orderData->dCity)          ? $orderData->pCity         : $orderData->dCity;;
                    $this->view->dState         = empty($orderData->dState)         ? $orderData->pState        : $orderData->dState; 
                    $this->view->dPostCod       = empty($orderData->dPostCode)      ? $orderData->pPostCode     : $orderData->dPostCode;
                    $this->view->dState         = $this->view->dState . ' ' . $this->view->dPostCod;    
                    $this->view->dCountry       = empty($orderData->dCountry)       ? $orderData->pCountry      : $orderData->dCountry;
                    $this->view->contactName    = $orderData->contactName;
                    $this->view->dPhone         = empty($orderData->dPhone)         ? $orderData->pPhone        : $orderData->dPhone;
                    $this->view->customerPoWo   = $orderData->customerPoWo;
                    $this->view->remarks1       = $orderData->remarks1;
                    $this->view->remarks2       = $orderData->remarks2;
                    
                                                    
                    $this->view->specialInstructions1   = $orderData->specialInstructions1;
                    $this->view->specialInstructions2   = $orderData->specialInstructions2;
                    $this->view->inclusionsOrder        = $orderData->supplierList;
                    $this->view->partialPick            = $orderData->partialPickAllowed;
                    $this->view->invoiceOrder           = $orderData->invWithGoods;
                    
                    $shipList                           = $this->minder->getTrnTypeShipViaList();
                
                    if(!empty($orderData->shipVia)) {
                        $this->view->despatchType       = $shipList[$orderData->shipVia];
                    } else {                            
                         $this->view->despatchType      = '';
                    }
                    
                    
                    $this->view->shipServiceList        = $orderData->shipVia;
                    if(!empty($orderData->shipVia)) {
                        $carrierList                        = $this->minder->getCarrierByClause(array('CARRIER_ID = ? AND ' => $orderData->shipVia));
                        if($carrierList[0]['DEFAULT_CONNOTE_ISSO'] == 'T') {
                            $this->view->consignment            = $orderData->pickOrder;
                        } else {
                            $this->view->consignment            = '';
                        }
                    } else {
                        $this->view->consignment            = $orderData->pickOrder;
                    }
                    
                    $this->view->carrier                = $orderData->shipVia;
                    $this->view->carrierList            = minder_array_merge(array('' => '') , $this->minder->getCarriersList());
                    $this->view->payerList              = array('SENDER'    => 'SENDER',
                                                                'RECEIVER'  => 'RECEIVER');
                    $this->view->printAddressList       = minder_array_merge(array('' => '') , $this->minder->getOptionsList('ADDRESSES'));        
                    
                    $this->view->payer                  = 'SENDER';
                    $this->view->carrierServiceList     = $this->minder->getPoShipServiceList();
                    $this->view->carrierService         = 'GEN';        
                    $this->view->palletOwnerList        = minder_array_merge(array('NONE' => 'NONE'), $this->minder->getPalletOwnerList());
                    $this->view->palletOwner            = 'NONE';
                    $this->view->qtyAddressLabels       = $this->minder->defaultControlValues['DEFAULT_CONNOTE_QTY_LABELS'];        
                    
                    break;
                
                case 'ACCEPT':
                   
                   $transaction            =   new Transaction_DSOTS();
                   
                   $palletQty = $this->_request->getParam('palletQty');
                   if(!empty($palletQty)) {
                    $palletQty = str_repeat('0', 4 - strlen($palletQty)) . $palletQty;
                   } else {
                    $palletQty = '0000';
                   }
                   
                   $cartonQty = $this->_request->getParam('totalCartons');
                   if(!empty($cartonQty)) {
                    $cartonQty = str_repeat('0', 4 - strlen($cartonQty)) . $cartonQty;  
                   } else {
                    $cartonQty = '0000';
                   }
                   
                   $satchelQty = $this->_request->getParam('totalSatchels');
                   if(!empty($satchelQty)) {
                    $satchelQty = str_repeat('0', 4 - strlen($satchelQty)) . $satchelQty;  
                   } else {
                    $satchelQty = '0000'; 
                   }
                   
                   $totalWeight = $this->_request->getParam('totalWeight');
                   if(!empty($totalWeight)) {
                    $totalWeight = str_repeat('0', 5 - strlen($totalWeight)) . $totalWeight;  
                   } else {
                    $totalWeight = '00000'; 
                   }
                   
                   $totalVolume = $this->_request->getParam('totalVolume');
                   if(!empty($totalVolume)) {
                    $totalVolume = str_repeat('0', 5 - strlen($totalVolume)) . $totalVolume;  
                   } else {
                    $totalVolume = '00000'; 
                   }
                   
                   $payerFlag = $this->_request->getParam('payer');
                   if(!empty($totalVolume)) {
                    $payerFlag = substr($payerFlag, 0, 1);
                    $transaction->payerFlag = $payerFlag;
                   }
                   $palletOwner = $this->_request->getParam('palletOwner'); 
                   if(!empty($totalVolume)) {
                    $palletOwner = $palletOwner . str_repeat(' ', 10 - strlen($palletOwner));  
                   } else {
                    $palletOwner = str_repeat(' ', 10); 
                   }
                   $currierId = $this->_request->getParam('carrier');
                   if(empty($currierId)) {
                    $currierId = $this->minder->defaultControlValues['DEFAULT_CARRIER_ID'];  
                    $transaction->carrierId = $currierId;
                   } else {
                    $transaction->carrierId = $currierId;   
                   }
                   
                   $qtyAddressLabel =   $this->getRequest()->getParam('qtyAddressLabels');
                   
                   $accountNo = $this->_request->getParam('accountNo');
                   $conNoteNo = $this->_request->getParam('consignment');
                   
                   $transaction->objectId       =   $conNoteNo . $accountNo;
                   $transaction->orderNo        =   $this->session->orderNo; 
                   
                   $transaction->cartonQty      =   $cartonQty;
                   $transaction->satchelQty     =   $satchelQty;
                   $transaction->totalWeight    =   $totalWeight;
                   $transaction->totalVolume    =   $totalVolume;
                   $transaction->printerId      =   $this->minder->limitPrinter;
                   $transaction->packType       =   $this->minder->defaultControlValues['DEFAULT_CONNOTE_PACK'];
                   $transaction->palletOwnerId  =   $palletOwner;
                   $transaction->labelQty       =   $qtyAddressLabel;
                   $transaction->palletQty      =   $palletQty;
                   
                   
                  
                    if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBKKKKSK', '', 'MASTER    '))) { 
                        $this->addError('Error while DSOTS transaction ');
                    } else {
                        
                        $transaction    =    new Transaction_DSOLO();
                    
                        $transaction->objectId    =    $conNoteNo;
                        $transaction->qty        =    $qtyAddressLabel;    
                         
                         if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSBSSSKSS', '', 'MASTER    '))) { 
                            $this->addError('Error while DSOLO transaction ');
                        } else {
                            $this->addMessage($result);
                        }
                    }
                    
                    // update Order data
                    $pickOrder  =   new stdClass();
                    
                    $pickOrder->pickOrder   =   $this->session->orderNo;
                    $pickOrder->remarks1    =   $this->_request->getParam('label_comment1');      
                    $pickOrder->remarks2    =   $this->_request->getParam('label_comment2');
                    
                    $result =   $this->minder->updatePickOrder($pickOrder, 'despatch');
                    if(!$result){
                        $this->addError('Error while update order data');    
                    }
                                                   
                    
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('index',
                                             'despatch'
                                            );
                    
                    break;
                case 'GET ADDRESS':
                
                    $orderNo    =   $this->getRequest()->getParam('contact_instance_id');
                    $jsonData   =   array();
                    if(!empty($orderNo)){
                     
                        // get MESSAGE_ID_GEN
                        $messageId  =   $this->minder->runDbGenerator('MESSAGE_ID_GEN');
                        
                        // populate WEB_REQUESTS
                        $result     =   $this->minder->populateWebRequests($messageId, 'GCNA');
                        if($result){
                            // call ADD_TRAN_V4
                        
                            $transaction    =   new Transaction_GCNAS();
                            $transaction->reference =   '<ContactInstanceID>' . $orderNo . $this->minder->defaultControlValues['LABEL_FIELD_WRAPPER'];
                            
                            $result =   $this->minder->doTransactionV4Response($transaction, $messageId); 
                            // update WEB_REQUESTS
                            $result     =   $this->minder->updateWebRequests($messageId, array('REQUEST_STATUS' => 'WS'));
                            // starting listning for DB event
                            if($result){
                                
                            }
                        }
                    } else {
                        $jsonData['result']  =   false;
                        $jsonData['message'] =   'Please enter Order No.';
                    }
                    
                    echo json_encode($jsonData);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;
                    break;
                
                case 'LOAD_ADDRESS_DATA':
                    
                    $fieldName  =   $this->getRequest()->getParam('field_name');
                    $fieldType  =   explode('|', $fieldName);
                    $fieldType  =   $fieldType[0];
                    
                    $orderNo    =   $this->session->orderNo;
                    $orderData  =   $this->minder->getPickOrder($orderNo, 'ALL');
                    
                    $jsonData           =   array();
                    $jsonData['type']   =   strtoupper($fieldType);   
                    
                    switch(strtoupper($fieldType)){
                        case 'MT':
                            $jsonData['PERSON_ID']      =   $orderData->pPersonId;
                            $jsonData['PERSON_TYPE']    =   $orderData->pPersonType;
                            $jsonData['FIRST_NAME']     =   $orderData->pFirstName;
                            $jsonData['LAST_NAME']      =   $orderData->pLastName;
                            $jsonData['CITY']           =   $orderData->pCity;
                            $jsonData['STATE']          =   $orderData->pState;
                            $jsonData['POST_CODE']      =   $orderData->pPostCode;
                            $jsonData['COUNTRY']        =   $orderData->pCountry;
                            $jsonData['PHONE']          =   $orderData->pPhone;
                            $jsonData['ADDRESS_LINE1']  =   $orderData->pAddressLine1;
                            $jsonData['ADDRESS_LINE2']  =   $orderData->pAddressLine2;
                            $jsonData['ADDRESS_LINE3']  =   $orderData->pAddressLine3;
                            $jsonData['ADDRESS_LINE4']  =   $orderData->pAddressLine4;
                            $jsonData['ADDRESS_LINE5']  =   $orderData->pAddressLine5;
                            $jsonData['TITLE']          =   $orderData->pTitle;
                            $jsonData['CONTACT_NAME']   =   $orderData->contactName;
                            $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo; 
                            break;
                        
                        case 'OF':
                            $jsonData['PERSON_ID']      =   $orderData->sPersonId;
                            $jsonData['PERSON_TYPE']    =   $orderData->sPersonType;
                            $jsonData['FIRST_NAME']     =   $orderData->sFirstName;
                            $jsonData['LAST_NAME']      =   $orderData->spLastName;
                            $jsonData['CITY']           =   $orderData->sCity;
                            $jsonData['STATE']          =   $orderData->sState;
                            $jsonData['POST_CODE']      =   $orderData->sPostCode;
                            $jsonData['COUNTRY']        =   $orderData->sCountry;
                            $jsonData['PHONE']          =   $orderData->sPhone;
                            $jsonData['ADDRESS_LINE1']  =   $orderData->sAddressLine1;
                            $jsonData['ADDRESS_LINE2']  =   $orderData->sAddressLine2;
                            $jsonData['ADDRESS_LINE3']  =   $orderData->sAddressLine3;
                            $jsonData['ADDRESS_LINE4']  =   $orderData->sAddressLine4;
                            $jsonData['ADDRESS_LINE5']  =   $orderData->sAddressLine5;
                            $jsonData['TITLE']          =   $orderData->sTitle;
                            $jsonData['CONTACT_NAME']   =   $orderData->contactName;
                            $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo; 
                            break;
                        
                        case 'DT':
                            $jsonData['PERSON_ID']      =   $orderData->dPersonId;
                            $jsonData['PERSON_TYPE']    =   $orderData->dPersonType;
                            $jsonData['FIRST_NAME']     =   $orderData->dFirstName;
                            $jsonData['LAST_NAME']      =   $orderData->dLastName;
                            $jsonData['CITY']           =   $orderData->dCity;
                            $jsonData['STATE']          =   $orderData->dState;
                            $jsonData['POST_CODE']      =   $orderData->dPostCode;
                            $jsonData['COUNTRY']        =   $orderData->dCountry;
                            $jsonData['PHONE']          =   $orderData->dPhone;
                            $jsonData['ADDRESS_LINE1']  =   $orderData->dAddressLine1;
                            $jsonData['ADDRESS_LINE2']  =   $orderData->dAddressLine2;
                            $jsonData['ADDRESS_LINE3']  =   $orderData->dAddressLine3;
                            $jsonData['ADDRESS_LINE4']  =   $orderData->dAddressLine4;
                            $jsonData['ADDRESS_LINE5']  =   $orderData->dAddressLine5;
                            $jsonData['TITLE']          =   $orderData->dTitle;
                            $jsonData['CONTACT_NAME']   =   $orderData->contactName;
                            $jsonData['CUSTOMER_PO_WO'] =   $orderData->customerPoWo;  
                            break;
                    }
                    
                    echo json_encode($jsonData);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;
                
                case 'SAVE_ADDRESS_DATA':
                    
                    $fieldName  =   $this->getRequest()->getParam('field_name');
                    $fieldType  =   explode('|', $fieldName);
                    $fieldType  =   $fieldType[0];
                    
                    $pickOrder  =   new stdClass();
                    
                    $pickOrder->pickOrder   =   $this->session->orderNo;
                    
                    
                    $jsonData   =   array();
                    switch(strtoupper($fieldType)){
                        case 'MT':
                            $pickOrder->pPersonId       =   $this->getRequest()->getParam('person_id');
                            $pickOrder->pPersonType     =   $this->getRequest()->getParam('person_type');
                            $pickOrder->pFirstName      =   $this->getRequest()->getParam('first_name');
                            $pickOrder->ppLastName      =   $this->getRequest()->getParam('last_name');
                            $pickOrder->pCity           =   $this->getRequest()->getParam('city');
                            $pickOrder->pState          =   $this->getRequest()->getParam('state');
                            $pickOrder->pPostCode       =   $this->getRequest()->getParam('post_code');
                            $pickOrder->pCountry        =   $this->getRequest()->getParam('country');
                            $pickOrder->pPhone          =   $this->getRequest()->getParam('phone');
                            $pickOrder->pAddressLine1   =   $this->getRequest()->getParam('address_line1');
                            $pickOrder->pAddressLine2   =   $this->getRequest()->getParam('address_line2');
                            $pickOrder->pAddressLine3   =   $this->getRequest()->getParam('address_line3');
                            $pickOrder->pAddressLine4   =   $this->getRequest()->getParam('address_line4');
                            $pickOrder->pAddressLine5   =   $this->getRequest()->getParam('address_line5');
                            $pickOrder->pTitle          =   $this->getRequest()->getParam('title');
                            break;
                        case 'OF':
                            $pickOrder->sPersonId       =   $this->getRequest()->getParam('person_id');
                            $pickOrder->sPersonType     =   $this->getRequest()->getParam('person_type');
                            $pickOrder->sFirstName      =   $this->getRequest()->getParam('first_name');
                            $pickOrder->spLastName      =   $this->getRequest()->getParam('last_name');
                            $pickOrder->sCity           =   $this->getRequest()->getParam('city');
                            $pickOrder->sState          =   $this->getRequest()->getParam('state');
                            $pickOrder->sPostCode       =   $this->getRequest()->getParam('post_code');
                            $pickOrder->sCountry        =   $this->getRequest()->getParam('country');
                            $pickOrder->sPhone          =   $this->getRequest()->getParam('phone');
                            $pickOrder->sAddressLine1   =   $this->getRequest()->getParam('address_line1');
                            $pickOrder->sAddressLine2   =   $this->getRequest()->getParam('address_line2');
                            $pickOrder->sAddressLine3   =   $this->getRequest()->getParam('address_line3');
                            $pickOrder->sAddressLine4   =   $this->getRequest()->getParam('address_line4');
                            $pickOrder->sAddressLine5   =   $this->getRequest()->getParam('address_line5');
                            $pickOrder->sTitle          =   $this->getRequest()->getParam('title');
                            break;
                        case 'DT':
                            $pickOrder->dPersonId       =   $this->getRequest()->getParam('person_id');
                            $pickOrder->dFirstName      =   $this->getRequest()->getParam('first_name');
                            $pickOrder->dLastName      =   $this->getRequest()->getParam('last_name');
                            $pickOrder->dCity           =   $this->getRequest()->getParam('city');
                            $pickOrder->dState          =   $this->getRequest()->getParam('state');
                            $pickOrder->dPostCode       =   $this->getRequest()->getParam('post_code');
                            $pickOrder->dCountry        =   $this->getRequest()->getParam('country');
                            $pickOrder->dPhone          =   $this->getRequest()->getParam('phone');
                            $pickOrder->dAddressLine1   =   $this->getRequest()->getParam('address_line1');
                            $pickOrder->dAddressLine2   =   $this->getRequest()->getParam('address_line2');
                            $pickOrder->dAddressLine3   =   $this->getRequest()->getParam('address_line3');
                            $pickOrder->dAddressLine4   =   $this->getRequest()->getParam('address_line4');
                            $pickOrder->dAddressLine5   =   $this->getRequest()->getParam('address_line5');
                            $pickOrder->dTitle          =   $this->getRequest()->getParam('title');
                            break;
                    }
               
                    $result         = $this->minder->updatePickOrder($pickOrder, 'despatch');
                    $jsonResponse   = array();  
                    if($result){
                        $jsonResponse['result'] =   true;    
                    } else {
                        $jsonResponse['result'] =   false;    
                    }
                       
                    echo json_encode($jsonResponse);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;
                
                case 'PRINT_ADDRESS':
                    
                    $addressType    =   $this->getRequest()->getParam('address_type');
                    $labelName      =   $this->getRequest()->getParam('label_name');
                    
                    $orderNo    =   $this->session->orderNo;
                    $orderData  =   $this->minder->getPickOrder($orderNo, 'ALL');
                    
                    $jsonResponse   =   array();
                    $printData      =   array();
                    
                    switch(strtoupper($addressType)){
                        case 'MT':
                            $printData['PERSON_ID']      =   $orderData->pPersonId;
                            $printData['PERSON_TYPE']    =   $orderData->pPersonType;
                            $printData['FIRST_NAME']     =   $orderData->pFirstName;
                            $printData['LAST_NAME']      =   $orderData->pLastName;
                            $printData['CITY']           =   $orderData->pCity;
                            $printData['STATE']          =   $orderData->pState;
                            $printData['POST_CODE']      =   $orderData->pPostCode;
                            $printData['COUNTRY']        =   $orderData->pCountry;
                            $printData['PHONE']          =   $orderData->pPhone;
                            $printData['ADDRESS_LINE1']  =   $orderData->pAddressLine1;
                            $printData['ADDRESS_LINE2']  =   $orderData->pAddressLine2;
                            $printData['ADDRESS_LINE3']  =   $orderData->pAddressLine3;
                            $printData['ADDRESS_LINE4']  =   $orderData->pAddressLine4;
                            $printData['ADDRESS_LINE5']  =   $orderData->pAddressLine5;
                            $printData['TITLE']          =   $orderData->pTitle;
                            break;
                        case 'OF':
                            $printData['PERSON_ID']      =   $orderData->sPersonId;
                            $printData['PERSON_TYPE']    =   $orderData->sPersonType;
                            $printData['FIRST_NAME']     =   $orderData->sFirstName;
                            $printData['LAST_NAME']      =   $orderData->spLastName;
                            $printData['CITY']           =   $orderData->sCity;
                            $printData['STATE']          =   $orderData->sState;
                            $printData['POST_CODE']      =   $orderData->sPostCode;
                            $printData['COUNTRY']        =   $orderData->sCountry;
                            $printData['PHONE']          =   $orderData->sPhone;
                            $printData['ADDRESS_LINE1']  =   $orderData->sAddressLine1;
                            $printData['ADDRESS_LINE2']  =   $orderData->sAddressLine2;
                            $printData['ADDRESS_LINE3']  =   $orderData->sAddressLine3;
                            $printData['ADDRESS_LINE4']  =   $orderData->sAddressLine4;
                            $printData['ADDRESS_LINE5']  =   $orderData->sAddressLine5;
                            $printData['TITLE']          =   $orderData->sTitle;
                            break;
                        case 'DT':
                            $printData['PERSON_ID']      =   $orderData->dPersonId;
                            $printData['PERSON_TYPE']    =   $orderData->dPersonType;
                            $printData['FIRST_NAME']     =   $orderData->dFirstName;
                            $printData['LAST_NAME']      =   $orderData->dLastName;
                            $printData['CITY']           =   $orderData->dCity;
                            $printData['STATE']          =   $orderData->dState;
                            $printData['POST_CODE']      =   $orderData->dPostCode;
                            $printData['COUNTRY']        =   $orderData->dCountry;
                            $printData['PHONE']          =   $orderData->dPhone;
                            $printData['ADDRESS_LINE1']  =   $orderData->dAddressLine1;
                            $printData['ADDRESS_LINE2']  =   $orderData->dAddressLine2;
                            $printData['ADDRESS_LINE3']  =   $orderData->dAddressLine3;
                            $printData['ADDRESS_LINE4']  =   $orderData->dAddressLine4;
                            $printData['ADDRESS_LINE5']  =   $orderData->dAddressLine5;
                            $printData['TITLE']          =   $orderData->dTitle;
                            break;
                    }
                    
                    
                    $printerObj =    $this->minder->getPrinter();
                    $result     =    $printerObj->printAddressLabel($printData, strtoupper($labelName));
                   
                    if($result['RES'] <= 0){
                         $jsonResponse['result']  =   true;
                    } else {
                         $jsonResponse['result']  =   false;    
                         $jsonResponse['message'] =   'Error while print label(s): ' . $result['ERROR_TEXT'];    
                    }
                    
                    echo json_encode($jsonResponse);
                    $this->_helper->viewRenderer->setNoRender(true);
                    return;        
                default:
                    
            }
     
        $this->render('/waiting/despatch');
    
    }
    

    public function linesAction()
    {

        $this->_preProcessNavigation();
        
        if (!empty($_POST['action1'])) {
            $action = strtoupper($_POST['action1']); 
            
            switch ($action) {
                

                case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
                
                
                    $this->view->headers = array(
                                                    'PICK_ORDER'            => 'Order #',
                                                    'PICK_LABEL_NO'         => 'Label #',
                                                    'PICK_ORDER_LINE_NO'    => 'Line #',
                                                    'PROD_ID'               => 'Product ID',
                                                    'SSN_ID'                => 'SSN',
                                                    'DESCRIPTION'           => 'Description',
                                                    'QTY_PICKED'            => 'Qty',
                                                    'WH_ID'                 => 'WH',
                                                    'DESPATCH_LOCATION'     => 'Location',
                                                    'PICK_LINE_STATUS'      => 'Status',
                                                    'SPECIAL_INSTRUCTIONS2' => 'Special Instructions (Internal)' 
                                                    );

                    $ordersList    = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList = $this->minder->getPickItemsByPickOrders($ordersList);
                    $this->view->data = array();
                    foreach($pickItemsList as $key => $value) {
                        if(in_array($key, $selectedLines)) {
                             $this->view->data[] =   $value;
                        }    
                    }
                 
                    $this->_processReportTo($action);
                    return;
                
            }
        }

        /**
         * Calculate and mark stuff for Pick Orders.
         */

        $id     = $this->_getParam('id');
        $method = $this->_getParam('method');

        // @TODO: Change this to $this->_preProcessNavigation() when code be moved from indexAction().
        if (!isset($this->session->navigation[$this->_controller]['connote'])) {
            $this->session->navigation[$this->_controller]['connote']['show_by']      = $this->_showBy;
            $this->session->navigation[$this->_controller]['connote']['pageselector'] = $this->_pageSelector;
        }

        $allowed = array(
            'PICK_ORDER'            => 'PICK_ORDER = ? AND ',
            'CONTACT_NAME'          => 'CONTACT_NAME = ? AND ',
            'CUSTOMER_PO_WO'        => 'CUSTOMER_PO_WO = ? AND ',
            'PERSON_ID'             => 'PERSON_ID = ? AND ',
            'P_FIRST_NAME'          => 'P_FIRST_NAME = ? AND ',
            'COMPANY_ID'            => 'COMPANY_ID = ? AND ',
            'PICK_DUE_DATE'         => 'PICK_DUE_DATE = ? AND ',
            'PICK_STATUS'           => 'PICK_STATUS = ? AND ',
            'CREATED_BY'            => 'CREATED_BY = ? AND ',
            'PICK_PRIORITY'         => 'PICK_PRIORITY = ? AND ',
            'SHIP_VIA'              => 'SHIP_VIA = ? AND ',
            'DESPATCH_LOCATION'     => 'DESPATCH_LOCATION = ? AND ',
            'D_CITY'                => 'D_CITY = ? AND ',
            'D_STATE'               => 'D_STATE = ? AND ',
            'D_COUNTRY'             => 'D_COUNTRY = ? AND ',
            'FROM_CREATE_DATE'      => 'FROM_CREATE_DATE = ? AND ',
            'TO_CREATE_DATE'        => 'TO_CREATE_DATE = ? AND ',
            'SPECIAL_INSTRUCTIONS1' => 'SPECIAL_INSTRUCTIONS1 = ? AND ',
            'SPECIAL_INSTRUCTIONS2' => 'SPECIAL_INSTRUCTIONS2 = ? AND ',
            'PICK_LABEL_NO'         => 'PICK_LABEL_NO = ? AND ',
        );

        if (!isset($this->session->conditions[$this->_controller]['index'])) {
            $this->session->conditions[$this->_controller]['index'] = array();
        }

        $clause = array_intersect_key($this->session->conditions[$this->_controller]['index'], $allowed);
        $clause = array_merge(array("(PICK_ITEM.PICK_LINE_STATUS = 'DS' OR 
                                      PICK_ITEM.PICK_LINE_STATUS = 'DL' OR 
                                      PICK_ITEM.PICK_LINE_STATUS = 'DC' OR
                                      PICK_ITEM.PICK_LINE_STATUS = 'DI') AND " => '',
                                    ), $clause);
        $pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];  
        
        $fieldsData = $this->minder->getUserHeaders('SCN_AWDESP');
        $lines      = $this->view->minder->getDespatchedPickOrders($clause, $pageSelector, $showBy, $fieldsData);
    
        $lines      = $lines['data'];
        $numRecords = count($lines);

        $conditions = $this->_getConditions('index');
      
        if ($id == 'select_all') {
            if ('true' == $method) {
                foreach($lines as $key => $line) {
                    $conditions[$key] = $key;
                    $this->session->params[$this->_controller]['index']['pick_orders'] = array_unique(array_merge($this->session->params[$this->_controller]['index']['pick_orders'], array($key)));
                }
                arsort($this->session->params[$this->_controller]['index']['pick_orders']);
            } elseif ('false' == $method) {
                foreach($lines as $key => $line) {
                    unset($conditions[$key]);
                    if (false !== ($key1 = array_search($key, $this->session->params[$this->_controller]['index']['pick_orders']))) {
                        unset($this->session->params[$this->_controller]['index']['pick_orders'][$key1]);
                    }
                }
            }
        } else {
            $value = $this->getRequest()->getParam('value');
            if (is_null($value)) {
                $value = $id;
            }
            if ('true' == $method) {
                $conditions[$id] = $value;
                $this->session->params[$this->_controller]['index']['pick_orders'] = array_unique(array_merge($this->session->params[$this->_controller]['index']['pick_orders'], array($value)));
                arsort($this->session->params[$this->_controller]['index']['pick_orders']);
            } elseif ('false' == $method) {
                unset($conditions[$id]);
                if (false !== ($key = array_search($id, $this->session->params[$this->_controller]['index']['pick_orders']))) {
                    unset($this->session->params[$this->_controller]['index']['pick_orders'][$key]);
                }
            }
        }
        $this->_setConditions($conditions, 'index');
    
        $pickOrdersCount = 0;
        foreach ($lines as $key => $line) {
            if (in_array($key, $conditions)) {
                $pickOrdersCount++;
            }
        }
       
        /**
         * Calculate and mark stuff for Pick Items (Lines).
         */

        $this->calcLinesAction();
        if (!isset($this->view->pickItems)) {
            $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
            $this->view->pickItems = array();
            if (count($pickOrders)) {
                $clause = array('PICK_ITEM_DETAIL.QTY_PICKED > 0 AND ' => ' ');
                $this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders, $clause);
            }
        }
        if (isset($this->view->counters)) {
            $this->view->counters += array('orders_selected' => $pickOrdersCount);
        } else {
            $this->view->counters = array('orders_selected' => $pickOrdersCount);
        }

        $this->view->headers = array(
                                           'PICK_ORDER'            => 'Order #',
                                           'PICK_LABEL_NO'         => 'Label #',
                                           'PICK_ORDER_LINE_NO'    => 'Line #',
                                           'PROD_ID'               => 'Product ID',
                                           'SSN_ID'                => 'ISSN',
                                           'DESCRIPTION'           => 'Description',
                                           'QTY_PICKED'            => 'Qty',
                                           'WH_ID'                 => 'WH',
                                           'DESPATCH_LOCATION'     => 'Location',
                                           'PICK_LINE_STATUS'      => 'Status',
                                           'SPECIAL_INSTRUCTIONS2' => 'Special Instructions (Internal)' 
                                     );
        
        $this->_postProcessNavigation($this->view->pickItems);

        $this->view->pickItems = array_slice($this->view->pickItems,
        $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
        $this->view->maxno);
        
        $this->render('/waiting/lines');
        
    }

    public function calcLinesAction()
    {
        // We have received only $_GET variables. They will sent to Controller only in one case - when
        // new Pick Order is selected (checked | unchecked).
        $method = $this->getRequest()->getQuery('method');
        $id     = $this->getRequest()->getQuery('id');
        if (is_null($id)) {
            $id = 'select_all';
        }
        if (is_null($method)) {
            $method = 'init';
        }
        $this->_action = 'lines';

        if (!isset($this->session->conditions[$this->_controller][$this->_action])) {
            $this->session->conditions[$this->_controller][$this->_action] = array();
        }
        if (!isset($this->session->navigation[$this->_controller][$this->_action])) {
            $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $this->_showBy;
            $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $this->_pageSelector;
        }

        $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
        $this->view->pickItems = array();
        if (count($pickOrders)) {
            $clause                = array('PICK_ITEM_DETAIL.QTY_PICKED > 0 AND ' => ' ');
            $this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders, $clause);
        }
        $pickItems          =   $this->view->pickItems;
        $itemWithDsStatus   =   0; 
        foreach($pickItems as $item) {
            if($item['PICK_LINE_STATUS'] === 'DS') {
                $itemWithDsStatus++;
            }  
        }
        
        if($id != 'select_complete'){
            $this->view->conditions = $this->_markSelected($this->view->pickItems, $id, null, $method, $this->_action);
        } else {
            $conditions =   $this->_getConditions($this->_action);
          
            if($method == 'true'){
                foreach($pickItems as $item){
                    $conditions[$item->items['PICK_LABEL_NO']]  =   $item->items['PICK_LABEL_NO'];
                }
                    $conditions =   array_merge($conditions, array('select_complete' => true));    
            } else {
                foreach($pickItems as $item){
                    unset($conditions[$item->items['PICK_LABEL_NO']]);
                }
                    unset($conditions['select_complete']);    
            }
            
            $this->view->conditions =   $conditions;
            $this->_setConditions($conditions, $this->_action);
        }

        $this->view->counters = array(
                                        'lines_selected'    => 0,
                                        'products_selected' => 0,
                                        'issns_selected'    => 0,
                                        'total_selected'    => 0,
                                        'products_displayed'=> 0,
                                        'issns_displayed'   => 0,
                                        'total_displayed'   => 0,
                                        'with_status_ds'    => 0
                                    );
        foreach ($this->view->pickItems as $id => $pickItem) {
            if (false !== array_key_exists($id, $this->view->conditions)) {
                $this->view->counters['lines_selected']++;

                if (!empty($pickItem->items['SSN_ID'])) {
                    $this->view->counters['issns_selected']++;
                }
                if (!empty($pickItem->items['PROD_ID'])) {
                    $this->view->counters['products_selected']++;
                }

                $this->view->counters['total_selected'] +=  $pickItem->items['LINE_TOTAL'];

            }
            if (!empty($pickItem->items['SSN_ID'])) {
                $this->view->counters['issns_displayed']++;
            }
            if (!empty($pickItem->items['PROD_ID'])) {
                $this->view->counters['products_displayed']++;
            }

            $this->view->counters['total_displayed'] +=  $pickItem->items['LINE_TOTAL'];

        }
        
        $this->view->counters['with_status_ds']  = $itemWithDsStatus;
        $this->view->counters['total_selected']  = round($this->view->counters['total_selected'], 2);
        $this->view->counters['total_displayed'] = round($this->view->counters['total_displayed'], 2);
        
    }
   
   
   // ----------------------------------- Order Awaiting Exit from Warehouse -----------------------------------------------//
   
   public function awaitingExitAction() {
        
      
        if (($from = $this->getRequest()->getPost('from')) && $from == 'exit-lines') {
            $this->_action = 'exit-lines';
            $this->_preProcessNavigation();
            $this->_action = 'awaiting-exit';
        } else {
            $this->_preProcessNavigation();
        }
       
        $this->view->headers = array('DESPATCH_ID'       => 'ID #',
                                     'AWB_CONSIGNMENT_NO'=> 'Connote #',
                                     'PICKD_CHARGE_TO'   => 'A/C',
                                     'PICKD_CARRIER_ID'  => 'Ship Via',
                                     'PICKD_SERVICE_TYPE'=> 'Service',
                                     'PICKD_SUBURB'      => 'Suburb',
                                     'PICKD_POST_CODE'   => 'Post Code',
                                     'PICKD_PALLET_QTY'  => 'Pallets',
                                     'PICKD_PALLET_OWNER'=> 'Owner',
                                     'PICKD_CARTON_QTY'  => 'Cartons',
                                     'PICKD_SATCHEL_QTY' => 'Satchels',
                                     'PICKD_WT_ACTUAL'   => 'Weight',
                                     'PICKD_VOL_ACTUAL'  => 'Vol.',
                                     'PICKD_ADDRESS_QTY' => 'Qty Labels');
        
        $allowed = array(
                            'connote_no'  => 'PICK_DESPATCH.AWB_CONSIGNMENT_NO LIKE ? AND ',
                            'ship_by'     => 'PICK_DESPATCH.PICKD_CARRIER_ID = ? AND ',
                            'suburb'      => 'PICK_DESPATCH.PICKD_SUBURB LIKE ? AND ',
                            'payer'       => 'PICK_DESPATCH.PICKD_CHARGE_TO = ? AND ',
                            'post_code'   => 'PICK_DESPATCH.PICKD_POST_CODE LIKE ? AND '
                        );
        
        $conditions         =   $this->_setupConditions(null, $allowed);
        $linesConditions    =   $this->_getConditions('exit-lines');
        $conditions         =   array_merge($conditions, $linesConditions);
        $clause             =   $this->_makeClause($conditions, $allowed);
        $clause             =   array_merge($clause, array("PICK_DESPATCH.DESPATCH_STATUS = 'DC' AND "   =>  ''));
        
        $parserObj   = new ParserSql('PICK_DESPATCH.PICKD_POST_CODE');
        $contactName = $this->getRequest()->getParam('post_code');
        if(!empty($contactName)) {
            $parserObj->setupStr($contactName);
            $parserObj->parse();
            if(!$parserObj->lastError) {
                $clause[$parserObj->parsedStr] = '';
            } else {
                $this->view->posCodeError = $parserObj->errorMsg;
            }
        }
  
        $pageSelector     = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy           = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
        $awaitingExitList = $this->minder->getPickDespatchList($clause, $pageSelector, $showBy);
        
        
         if (!empty($_POST['action'])) {
            $action = strtoupper($_POST['action']); 
            switch ($action) {
                

                case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
                
                
                    $dataList         = $awaitingExitList['data'];   
                    $this->view->data = array();
                    foreach($dataList as $key => $value) {
                        if(in_array($value['DESPATCH_ID'], $linesConditions)) {
                             $this->view->data[] =   $value;
                        }                         
                    }
                    
                    $this->_processReportTo($action);
                    return;
                
                case 'DESPATCH EXIT':
                    
                    $selectedConnote    =   $linesConditions;   
                    $allList            =   $awaitingExitList['all'];
                    $result             =   false;
                    $result             =   false;
                   
                    if(count($selectedConnote) > 0) {
                        
                        foreach($allList as $line){
                            if(in_array($line['DESPATCH_ID'], $selectedConnote)) {
                                
                                $transaction = new Transaction_DSDXL();
                                
                                $transaction->reference = $line['AWB_CONSIGNMENT_NO'];
                                $transaction->qty       = $line['PICKD_ADDRESS_QTY'];  
                                
                                $result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSK', '', 'MASTER    ');
                                $result&= $result;
                             
                                unset($selectedConnote[$line['DESPATCH_ID']]);
                                
                                $this->_setConditions($selectedConnote, 'exit-lines');
                                
                                $awaitingExitList = $this->minder->getPickDespatchList($clause, $pageSelector, $showBy);
             
                            }
                        }
                        if(false === $result){
                            $this->addError('Error while DSDXL transaction ');
                        } else {
                            $this->addMessage('Transaction DSDXL - ' . $result);
                        }
                    }
                    
                    break;
             
                case 'PRINT LABELS':
                    
                    $selectedConnote    =   $linesConditions;   
                    $allList            =   $awaitingExitList['all'];
                    $printerObj         =   $this->minder->getPrinter();
                    $printError         =   false;
                    $printedTotal       =   0;
                    
                    if(count($selectedConnote) > 0) { 
                        foreach($allList as $line){
                            if(in_array($line['DESPATCH_ID'], $selectedConnote)) {
                                $printTools    = new Minder_PackIdPrintTools();
                                $printResult   = $printTools->printLabels($line['DESPATCH_ID'], $printerObj);
                                $printedTotal += $printResult->printedTotal;

                                $this->addMessage($printResult->messages);
                                $this->addWarning($printResult->warnings);

                                if (count($printResult->errors) > 0) {
                                    $this->addError($printResult->errors);

                                    $printError  =   true;
                                    break;
                                }
                            }
                        }
                        
                        if(!$printError){
                            if ($printedTotal > 0)
                                $this->addMessage('Print request send.');
                        }    
                    }
                
                    break;
                
                case 'REPRINT LABEL':
                    
                    $clause       = $this->_getConditions('exit-lines');
                    
                    $pageSelector = $this->session->navigation[$this->_controller]['exit-lines']['pageselector'];
                    $showBy       = $this->session->navigation[$this->_controller]['exit-lines']['show_by'];
                    
                    $packIdList   = $this->minder->getPackIdList($clause, $pageSelector, $showBy);
                    
                    $selected     = $this->getRequest()->getParam('pack_id');
                    $printerObj   = $this->minder->getPrinter();
                    $printError   = false;
                    
                    $printedTotal = 0;
               
                    if (empty($selected)) {
                        $this->addError('Select labels to reprint.');
                        break;
                    }
               
                    foreach($packIdList['data'] as $value){
                        if(in_array($value['PACK_ID'], $selected)){
                            $printTools    = new Minder_PackIdPrintTools();
                            $printResult   = $printTools->reprintLabel($value['PACK_ID'], $printerObj);
                            $printedTotal += $printResult->printedTotal;

                            $this->addMessage($printResult->messages);
                            $this->addWarning($printResult->warnings);

                            if (count($printResult->errors) > 0) {
                                $this->addError($printResult->errors);
                                $printError  =   true;
                                break;
                            }
                        }
                    }
                    
                    if(!$printError){
                        if ($printedTotal > 0)
                            $this->addMessage('Print request send.');
                    }    
                     
                    break;
            }
        } 
   
        $this->_postProcessNavigation($awaitingExitList);
        
        
        $this->view->conditions   = $this->_getConditions();
        $this->view->shipByList   = minder_array_merge(array('' => ''), $this->minder->getShipByList());
        $this->view->payerList    = minder_array_merge(array('' => ''), $this->minder->getPayerList());
          
        $this->view->connoteNo    = isset($_POST['connote_no']) ? $_POST['connote_no'] : '';
        $this->view->shipBy       = isset($_POST['ship_by']) ? $_POST['ship_by'] : '';
        $this->view->suburb       = isset($_POST['suburb']) ? $_POST['suburb'] : '';
        $this->view->postCode     = isset($_POST['post_code']) ? $_POST['post_code'] : '';
        $this->view->payer        = isset($_POST['payer']) ? $_POST['payer'] : '';
        
        $this->view->despatchList = $awaitingExitList['data'];
        $this->view->conditions   = $conditions;
        
        $this->session->params['awaitingExitList'] = $awaitingExitList['data'];
        
        $this->view->exitButton     =   '';
        if($this->minder->defaultControlValues['SEND_DSDX'] == 'T'){
            $this->view->exitButton =   'disabled="disabled"';
        } 
  
        $this->render('awaiting/awaiting-exit'); 
   }
   
   public function exitLinesAction() {
       
       
        $this->_preProcessNavigation();
        
        /**
         * Calculate and mark stuff for Pick Orders.
         */
        $id     = $this->_getParam('id');
        $method = $this->_getParam('method');
        
        $awaitingExit =  $this->session->params['awaitingExitList'];
        $conditions   =  $this->_getConditions('exit-lines');  
     
        if($method == 'true') {
            if($id != 'on') {
                $conditions[$id]    =   $id;
            } else {
                foreach($awaitingExit as $item) {
                    $conditions[$item['DESPATCH_ID']] = $item['DESPATCH_ID'];       
                }     
            }    
        } else {
            if($id != 'on') {
                unset($conditions[$id]);   
            } else {
                $conditions = null;    
            }
        }
        $clause = $conditions;
        $this->_setConditions($conditions, 'exit-lines');
      
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
     
        $packIdList   = $this->minder->getPackIdList($clause, $pageSelector, $showBy);  
        $this->view->packIdList = $packIdList['data']; 
     
      
        $this->view->headers = array(
                                            'DESPATCH_ID'       =>  'Despatch #',
                                            'PACK_ID'           =>  'Pack ID #',
                                            'DESPATCH_LABEL_NO' =>  'Despatch Label #',
                                            'LABEL_PRINTED_DATE'=>  'Label Printed Date',
                                            'PROD_ID'           =>  'Product #',
                                            'ISSN_DESCRIPTION'   => 'Description',
                                            'QTY'               =>  'Total',
                                            'SSN_ID'            =>  'SSN' 
                                     );
        
        if (!empty($_POST['action'])) {
            $action = strtoupper($_POST['action']); 
            
            switch ($action) {
                
                case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
                    $data       =   array();
                    $selected   =   $this->getRequest()->getParam('pack_id');
                    foreach($packIdList['data'] as $value){
                        if(in_array($value['PACK_ID'], $selected)){
                            $data[] =   $value;
                        }
                    }
                    
                    $this->view->data   =   $data;
                              
                    $this->_processReportTo($action);
                    return;
                    break;
            }
        }
     
        $this->_postProcessNavigation($packIdList);
        $this->view->counters = array('total_showed'        =>  $packIdList['total'],
                                      'despatch_selected'   =>  count($conditions));
       
        $this->render('/awaiting/exit-lines');
   }
   
   // ----------------------------------- Order Awaiting Exit from Warehouse -----------------------------------------------//
   
   // ----------------------------------- Despatched Orders -----------------------------------------------//
   
   public function despatchedOrdersAction(){
   
   		if (($from = $this->getRequest()->getPost('from')) && $from == 'issn-despatch') {
			$this->_action = 'issn-despatch';
			$this->_preProcessNavigation();
			$this->_action = 'despatched-orders';
   		} elseif(($from = $this->getRequest()->getPost('from')) && $from == 'pack-despatch') {
			$this->_action = 'pack-despatch';
			$this->_preProcessNavigation();
			$this->_action = 'despatched-orders';	
		} else {
			$this->_preProcessNavigation();
		}	
       
       $allowed = array(
                            'order_no'          => 'PICK_ITEM.PICK_ORDER LIKE ? AND ',
                            'exit_date_from'    => 'PICK_DESPATCH.PICKD_EXIT >= ? AND ',
                            'exit_date_to'      => 'PICK_DESPATCH.PICKD_EXIT <= ? AND ',
                            'connote'           => 'PICK_DESPATCH.AWB_CONSIGNMENT_NO LIKE ? AND ',
                            'ship_by'           => 'PICK_DESPATCH.PICKD_CARRIER_ID = ? AND ',
                            'product'           => 'PACK_ID.PROD_ID = ? AND ',
                            'order_type'        => 'PICK_ORDER.PICK_ORDER_TYPE = ? AND ', 
                            'customer_po_wo'    => 'PICK_ORDER.CUSTOMER_PO_WO LIKE ? AND ',
                            'issn'              => 'PACK_ID.SSN_ID LIKE ? AND ' 
                       );
       
       $this->view->headers = array();
       
       $fieldsData = $this->minder->getUserHeaders('SCN_DESPOR');
    
       $this->view->headers = $fieldsData['headers'];    
       
       $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
       $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
     
       $conditions   =   $this->_setupConditions(null, $allowed);
       $clause       =   $this->_makeClause($conditions, $allowed);
	   $clause 		 =   array_merge(array("PICK_DESPATCH.DESPATCH_STATUS = 'DX' AND " => ''), $clause);
        	
   	    
   	   if(!empty($clause['PICK_DESPATCH.PICKD_EXIT <= ? AND '])) {
       	$date = $clause['PICK_DESPATCH.PICKD_EXIT <= ? AND '];
        $clause['PICK_DESPATCH.PICKD_EXIT <= ? AND ']	=	date('Y-m-d', strtotime($date. '+1 day'));
	   }
       
       try {
            $despatchedOrderList = $this->minder->getDespatchedOrderList($clause, $pageSelector, $showBy, $fieldsData);
       } catch (Exception $e) {
            $this->addError('Error occured while searching: ' . $this->minder->lastError);
       }
       $this->_postProcessNavigation($despatchedOrderList);
       	
       $this->view->despatchList 				  =  $despatchedOrderList['data'];  
       $this->session->params['despatchedOrders'] =  $despatchedOrderList['data']; 	
       
   	   $conditions = $this->view->conditions = $this->_getConditions('mark-lines');
	   $count	   = 0;
	   $data	   = array();		
       foreach($despatchedOrderList['data'] as $value){
	   	if(in_array($value['DESPATCH_ID'], $conditions)){
	   		$count++;
	   		$data[]  =	$value; 
	   	}
	   }
	   if($count == count($despatchedOrderList['data'])){
	   	$this->view->upperChecked	=	'checked="checked"';
	   }
       
       $action = !empty($_POST['action']) ? strtoupper($_POST['action']) : '';
       if(!empty($action)){
	       	switch($action){
	       		case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
	       			
	       			$this->view->data =	$data;
	       			$this->_processReportTo($action); 
	       			return;
                
                case 'CLEAR SELECTION':
                    $this->_setConditions(array(), 'mark-lines');
                    $conditions =   $this->_getConditions('mark-lines');
                    break;
	       	}
       }

       $this->view->totalSelected= count($conditions);
	   $this->view->orderNo         = isset($_POST['order_no'])        ? $_POST['order_no']        : '';
       $this->view->customerPoWo    = isset($_POST['customer_po_wo'])  ? $_POST['customer_po_wo']  : '';
       $this->view->exitDateFrom    = isset($_POST['exit_date_from'])  ? $_POST['exit_date_from']  : '';
       $this->view->exitDateTo      = isset($_POST['exit_date_to'])    ? $_POST['exit_date_to']    : '';
       $this->view->connote         = isset($_POST['connote'])         ? $_POST['connote']         : '';
       $this->view->shipBy          = isset($_POST['ship_by'])         ? $_POST['ship_by']         : '';
       $this->view->product         = isset($_POST['product'])         ? $_POST['product']         : '';
       $this->view->issn            = isset($_POST['issn'])            ? $_POST['issn']            : '';
       $this->view->orderType       = isset($_POST['order_type'])      ? $_POST['order_type']      : '';
     
       $this->view->productList	 = $this->minder->getFildListFromAnyTable('PACK_ID', 'PROD_ID');
       $this->view->shipByList	 = $this->minder->getFildListFromAnyTable('PICK_DESPATCH', 'PICKD_CARRIER_ID');
       $this->view->orderTypeList= array_merge(array('' => ''), $this->minder->getOrderTypeList('T'));
       
   	   // load or issnDespatch screen or packDespatch screen
       if(isset($this->session->params['load'])){
       	$this->view->load	=	$this->session->params['load'];
       } else {
       	$this->view->load	=	$this->session->params['load']	=	null;
       }
       
       $this->render('/despatched/despatched-orders');
   }
   
   public function editDespatchedAction(){
   
       if(!empty($_POST['action'])){
            $action = strtoupper($_POST['action']);
       		switch($action){
                case 'SAVE':
                    
                	$allowed = array('order_no'					=>	'PICK_ORDER	 = ?',
                				     'order_type'				=>	'PICK_ORDER_TYPE = ?',
                					 'person_id'				=>	'PERSON_ID = ?',
                					 'contact_name'				=>	'CONTACT_NAME = ?',
                				     'customer_po_wo'			=>	'CUSTOMER_PO_WO = ?',
                					 'cost_center'				=>	'COST_CENTER = ?',
                					 'company_id'				=>	'COMPANY_ID = ?',
                				     'division_id'				=>	'DIVISION_ID = ?',
                					 'pick_priority'			=>	'PICK_PRIORITY = ?',
                					 'delivery_run_no'			=>	'DELIVERY_RUN_NO = ?',
                				     'pick_due_date'			=>	'PICK_DUE_DATE = ?',
                					 'special_instructions1'	=>	'SPECIAL_INSTRUCTIONS1 = ?',
                					 'special_instructions2'	=>	'SPECIAL_INSTRUCTIONS2 = ?',
                				     'inv_with_goods'			=>	'INV_WITH_GOODS = ?',
                					 'ship_via'					=>	'SHIP_VIA = ?',
                					 'despatch_location'		=>	'DESPATCH_LOCATION = ?',
                					 'create_date'				=>	'CREATE_DATE = ?',
                				     'create_by'				=>	'CREATED_BY = ?',
                					 'pick_started'				=>	'PICK_STARTED = ?',
                					 'pick_status'				=>	'PICK_STATUS = ?',
                				     'partial_pick_allowed'		=>	'PARTIAL_PICK_ALLOWED = ?',
                					 'ship_service'				=>	'SHIP_SERVICE = ?',
                					 'p_person_type'			=>	'P_PERSON_TYPE = ?',
                				     'p_first_name'				=>	'P_FIRST_NAME = ?',
                					 'p_last_name'				=>	'P_LAST_NAME = ?',
                					 'p_address_line2'			=>	'P_ADDRESS_LINE2 = ?',
                				     'p_city'					=>	'P_CITY = ?',
                					 'p_state'					=>	'P_STATE = ?',
                					 'p_post_code'				=>	'P_POST_CODE = ?',
                				     'p_country'				=>	'P_COUNTRY = ?',
                					 'p_phone'					=>	'P_PHONE = ?',
                					 'terms'					=>	'TERMS = ?',
                					 'payment_method'			=>	'PAYMENT_METHOD = ?',
                				     'sub_total_amount'			=>	'SUB_TOTAL_AMOUNT = ?',
                					 'freight'					=>	'FREIGHT = ?',
                					 'amount_paid'				=>	'AMOUNT_PAID = ?',
                				     'tax_rate'					=>	'TAX_RATE = ?',
                					 'admin_fee_rate'			=>	'ADMIN_FEE_RATE = ?',
                					 'p_person_id'				=>	'P_PERSON_ID = ?',
                				     'p_same_as_invoice_to'		=>	'P_SAME_AS_INVOICE_TO = ?',
                					 'admin_fee_amount'			=>	'ADMIN_FEE_AMOUNT = ?',
                					 'tax_amount'				=>	'TAX_AMOUNT = ?',
                				     'due_amount'				=>	'DUE_AMOUNT = ?',
                					 'approved_date'			=>	'APPROVED_DATE = ?',
                					 'approved_by'				=>	'APPROVED_BY = ?',
                				     'last_line_no'				=>	'LAST_LINE_NO = ?',
                					 'sum_std_amount'			=>	'SUM_STD_AMOUNT = ?',
                					 'sum_sale_amount'			=>	'SUM_SALE_AMOUNT = ?',
                					 'imported'					=>	'IMPORTED = ?',
                					 'importe_errors'			=>	'IMPORT_ERRORS = ?',
                					 'appproved_desp_date'		=>	'APPROVED_DESP_DATE = ?',	
                					 'approved_desp_by'			=>	'APPROVED_DESP_BY = ?',
                					 'default_sale_price'		=>	'DEFAULT_SALE_PRICE = ?',
                					 'wip_ordering'				=>	'WIP_ORDERING = ?',
                					 'permanent_transfer'		=>	'PERMANENT_TRANSFER = ?',	
                					 'return_date'				=>	'RETURN_DATE = ?',
                					 'printed_date'				=>	'PRINTED_DATE = ?',
                					 'printed_status'			=>	'PRINTED_STATUS = ?',
                					 'pick_retrieve_status'		=>	'PICK_RETRIEVE_STATUS = ?',
                					 'p_address_line1'			=>	'P_ADDRESS_LINE1 = ?',
                					 'p_title'					=>	'P_ADDRESS_LINE2 = ?',
                					 'p_address_line3'			=>	'P_ADDRESS_LINE3 = ?',
                					 'p_address_line4'			=>	'P_ADDRESS_LINE4 = ?',	
                					 'p_aust_post_4state_id'	=>	'P_AUST_POST_4STATE_ID = ?',
                					 'supplier_list'			=>	'SUPPLIER_LIST = ?',
                					 'special_instructions'		=>	'SPECIAL_INSTRUCTIONS = ?',
                					 'wh_id'					=>	'WH_ID = ?',	
                					 'update_id'				=>	'UPDATE_ID = ?',
                					 'assembly_started'			=>	'ASSEMBLY_STARTED = ?',
                					 'last_update_date'			=>	'LAST_UPDATE_DATE = ?',
                					 'order_cancelled'			=>	'ORDER_CANCELLED = ?',
                					 'p_address_line5'			=>	'P_ADDRESS_LINE5 = ?',
                					 'other1'					=>	'OTHER1 = ?',
                					 'other2'					=>	'OTHER2 = ?',
                					 'other3'					=>	'OTHER3 = ?',	
                					 'other4'					=>	'OTHER4 = ?',
                					 'other5'					=>	'OTHER5 = ?',
                					 'other6'					=>	'OTHER6 = ?',
                					 'other7'					=>	'OTHER7 = ?',	
                					 'other8'					=>	'OTHER8 = ?',
                					 'other9'					=>	'OTHER9 = ?',
                					 'freight_tax_amount'		=>	'FREIGHT_TAX_AMOUNT = ?',
                					 'net_weight'				=>	'NET_WEIGHT = ?',
                					 'pallet_base'				=>	'PALLET_BASE = ?',
                					 'over_sized'				=>	'OVER_SIZED = ?',
                					 'label_printed_date'		=>	'LABEL_PRINTED_DATE = ?',
                					 'other_num1'				=>	'OTHER_NUM1 = ?',	
                					 'other_num2'				=>	'OTHER_NUM2 = ?',
                					 'remarks1'					=>	'REMARKS1 = ?',
                					 'remarks2'					=>	'REMARKS2 = ?',
                					 'remarks3'					=>	'REMARKS3 = ?',	
                					 'remarks4'					=>	'REMARKS4 = ?',
                					 'remarks5'					=>	'REMARKS5 = ?',
                					 'remarks6'					=>	'REMARKS6 = ?',
                					 'footer1'					=>	'FOOTER1 = ?',	
                					 'footer2'					=>	'FOOTER2 = ?',
                					 'footer3'					=>	'FOOTER3 = ?',
                					 'footer4'					=>	'FOOTER4 = ?',
                					 'footer5'					=>	'FOOTER5 = ?',	
                					 'over_sized_reason'		=>	'OVER_SIZED_REASON = ?',
                					 'address_label_date'		=>	'ADDRESS_LABEL_DATE = ?',
                					 'pick_order_started'		=>	'PICK_ORDER_STARTED = ?',
                					 'export_category'			=>	'EXPORT_CATEGORY = ?',	
                					 'd_first_name'				=>	'D_FIRST_NAME = ?',
                					 'd_last_name'				=>	'D_LAST_NAME = ?',
                					 'd_city'					=>	'D_CITY = ?',
                					 'd_state'					=>	'D_STATE = ?',	
                					 'd_post_code'				=>	'D_POST_CODE = ?',
                					 'd_country'				=>	'D_COUNTRY = ?',
                					 'd_phone'					=>	'D_PHONE = ?',
                					 'd_address_line1'			=>	'D_ADDRESS_LINE1 = ?',	
                					 'd_address_line2'			=>	'D_ADDRESS_LINE2 = ?',
                					 'd_address_line3'			=>	'D_ADDRESS_LINE3 = ?',
                					 'd_address_line4'			=>	'D_ADDRESS_LINE4 = ?',
                					 'd_address_line5'			=>	'D_ADDRESS_LINE5 = ?',	
                					 'd_title'					=>	'D_TITLE = ?',
                					 'supplier_id'				=>	'SUPPLIER_ID = ?',
                					 's_person_id'				=>	'S_PERSON_ID = ?',
                					 's_person_type'			=>	'S_PERSON_TYPE = ?',	
                					 's_first_name'				=>	'S_FIRST_NAME = ?',
                					 's_last_name'				=>	'S_LAST_NAME = ?',
                					 's_city'					=>	'S_CITY = ?',
                					 's_state'					=>	'S_STATE = ?',	
                					 's_post_code'				=>	'S_POST_CODE = ?',
                					 's_country'				=>	'S_COUNTRY = ?',
                					 's_phone'					=>	'S_PHONE = ?',
                					 's_address_line1'			=>	'S_ADDRESS_LINE1 = ?',	
                					 's_address_line2'			=>	'S_ADDRESS_LINE2 = ?',
                					 's_address_line3'			=>	'S_ADDRESS_LINE3 = ?',
                					 's_address_line4'			=>	'S_ADDRESS_LINE4 = ?',
                					 's_address_line5'			=>	'S_ADDRESS_LINE5 = ?',	
                					 's_title'					=>	'S_TITLE = ?',
                					 's_same_as_sold_from'		=>	'S_SAME_AS_SOLD_FROM = ?',
                					 'd_person_id'				=>	'D_PERSON_ID = ?',	
                					 'so_legacy_consignment'	=>	'SO_LEGACY_CONSIGNMENT = ?',
                					 'so_legacy_project'		=>	'SO_LEGACY_PROJECT = ?',
                					 'so_legacy_internal_id'	=>	'SO_LEGACY_INTERNAL_ID = ?',
                					 'so_legacy_last_modified'	=>	'SO_LEGACY_LAST_MODIFIED = ?',	
                					 'so_legacy_picl_wh_id'		=>	'SO_LEGACY_PICK_WH_ID = ?',
                					 'so_legacy_pick_wh_name'	=>	'SO_LEGACY_PICK_WH_NAME = ?',
                					 'so_legacy_memo'			=>	'SO_LEGACY_MEMO = ?',	
                					 'so_legacy_status_id'		=>	'SO_LEGACY_STATUS_ID = ?',
                					 'so_legacy_status'			=>	'SO_LEGACY_STATUS = ?',
                					 'ship_via_name'			=>	'SHIP_VIA_NAME = ?',
                					 'so_legacy_create_date'	=>	'SO_LEGACY_CREATE_DATE = ?',	
                					 'po_material_safety_data'	=>	'PO_MATERIAL_SAFETY_DATA = ?'
                				);
                	$conditions   =   $this->_setupConditions(null, $allowed);
       				$clause       =   $this->_makeClause($conditions, $allowed);
	   				
       				$orderNo	  =	  $this->getRequest()->getPost('pick_order_no');	  		
       				$result		  =	  $this->minder->updateOrder($clause, $orderNo);
       				
       				if($result){
       					$this->addMessage('Update information successfully');
       				} else {
       					$this->addError('Error while update information');
       				}
       				
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('despatched-orders',
                                             'despatch'
                                            );
                    break;
                case 'DISCARD':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('despatched-orders',
                                             'despatch'
                                            );
                    break;
            } 
       }
       
       $order = $this->getRequest()->getParam('order');
       if(!empty($order)){
       	$this->view->order	=	$order;
       	$orderType 			=	substr($order, 0, 2);
        $orderData			=	$this->minder->getPickOrder($order, $orderType);
        
        $this->view->orderData	=	$orderData;
       }
       
       $this->render('/despatched/edit_despatched');
   }
   
   
   public function markLinesAction(){
		
   		
        $id     = $this->_getParam('id');
        $method = $this->_getParam('method');
   
   	    $despatchedOrders =  $this->session->params['despatchedOrders'];
        $conditions   	  =  $this->_getConditions('mark-lines');
        if(!isset($conditions)){
        	$conditions = array();  
        }

        if($method == 'true') {
            if($id != 'on') {
                $conditions[$id]    =   $id;
            } else {
                foreach($despatchedOrders as $item) {
                    $conditions[$item['DESPATCH_ID']] = $item['DESPATCH_ID'];       
                }     
            }    
        } else {
            if($id != 'on') {
                unset($conditions[$id]);   
            } else {
                $conditions = array();    
            }
        }
        
        $this->view->selected = array('total_selected' => count($conditions), 
									  'total_items'	   => count($despatchedOrders),
                                      'what_view'      => $this->session->params['load']);
        $this->_setConditions($conditions, 'mark-lines');
		
		$this->render('/despatched/mark-lines');
   }
   
   public function packDespatchAction(){
   
   		$this->session->params['load']	=	'pack-despatch';
   		$this->_preProcessNavigation();
   
   		$this->view->headers = array('DESPATCH_ID'			=>	'Despatch #',
		   							 'PACK_ID'				=>	'Pack ID #',
		   							 'DESPATCH_LABEL_NO'	=>	'Despatched Label #',
		   							 'LABEL_PRINTED_DATE'	=>	'Label Printed Date',
		   							 'PROD_ID'				=>	'Product #',
		   							 'ISSN_DESCRIPTION'		=>	'Description',
		   							 'QTY'					=>	'Total',
		   							 'SSN_ID'				=>	'ISSN'
   		);
   		
   		$conditions   	  =  $this->_getConditions('mark-lines');

   		$pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];

        $clause		  =	$conditions;
        $packList	  = $this->minder->getPackIdList($clause, $pageSelector, $showBy);
		
        $action = !empty($_POST['action']) ? strtoupper($_POST['action']) : '';
        if(!empty($action)){
	       	switch($action){
	       		case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
	       			
                	$data	=	array();
                	foreach($packList['data'] as $packLine){
                		if(in_array($packLine['PACK_ID'], $_POST)){
                			$data[]	=	$packLine;	
                		}
                	}

                	$this->view->data	=	$data;
                	$this->_processReportTo($action); 
	       			
                	return;
                case 'REPRINT LABEL':
                	echo 'REPRINT';
                	break;
	       	}
        }
   		
        $this->view->packList	=	$packList['data'];	
        $this->_postProcessNavigation($packList);

        $this->render('/despatched/pack-despatch');
   }
   
   public function issnDespatchAction(){
   		
        $this->session->params['load']	=	'issn-despatch';
   		$this->_preProcessNavigation();
   		
   		$conditions   	  =  $this->_getConditions('mark-lines');

   		$pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        $fieldsData             = $this->minder->getUserHeaders('SCN_D_ISSN');
        $this->view->headers    = $fieldsData['headers'];
    
           
        $clause		  =	$conditions;
        $packList	  = $this->minder->getPickItemDetailList($clause, $pageSelector, $showBy, $fieldsData);	 
   		
        $action = !empty($_POST['action']) ? strtoupper($_POST['action']) : '';
        if(!empty($action)){
	       	switch($action){
	       		case 'REPORT: CSV':
                case 'REPORT: XLS':
                case 'REPORT: XML':
                case 'REPORT: TXT':
	       			$data	=	array();
                	foreach($packList['data'] as $packLine){
                		if(in_array($packLine['SSN_ID'], $_POST)){
                			$data[]	=	$packLine;	
                		}
                	}

                	$this->view->data	=	$data;
                	$this->_processReportTo($action); 
	       			
                	return;
	       	    case 'RETURNED':
                	break;
                case 'PARTIAL RETURNED':
                	break;
	       	}
        }
        
        $this->view->issnList	=	$packList['data'];
     
        $this->_postProcessNavigation($packList);

        $this->render('/despatched/issn-despatch');
   }
   
   public function returnedAction(){
       
       $firstSelect =   $this->getRequest()->getParam('first_select');
       
       if($firstSelect){
        $this->session->params['first_load']    =   false;
        $this->session->params['returned_list'] =   array();    
       }
       
       $action = !empty($_POST['action']) ? strtoupper($_POST['action']) : '';
       
       switch($action){
           
           case 'LOAD_FORM':
                
                $selectedId   = explode('|', $this->getRequest()->getParam('selected_id'));
                
                $this->session->params['returned_list'] =   $selectedId;
           
                $clause        =   array('STORE_TYPE = ? ' => 'RC');
                     
                $this->view->locationList  =   minder_array_merge(array('' => ''), $this->minder->getLocationListByClause($clause));
                $this->view->reasonList    =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('RET_REASON'));
                $this->view->showResult    =   false;
                  
                break;     
           
           case 'ALL_ACCEPT':
                
                $location   =   $this->getRequest()->getParam('returned_location');
                $qty        =   $this->getRequest()->getParam('returned_qty');
                $reason     =   $this->getRequest()->getParam('returned_reason');
                
                $selectedId =   $this->session->params['returned_list'];
                $action     =   'LOAD_FORM';
                
                if(!isset($location)){
                    $location = '';    
                }
                if(!isset($reason)){
                    $reason =   '';
                }
                if($this->minder->limitWarehouse == 'all'){
                    $whId   =   current($this->minder->getListByField('SYS_USER.DEFAULT_WH_ID'));
                } else {
                    $whId   =   $this->minder->limitWarehouse;       
                }
                
                $this->view->positiveResults    =   array();
                $this->view->negativeResults    =   array();
                
                if($selectedId > 0){
                    
                    foreach($selectedId as $ssnId){
                        
                        
                        $pickItemDetail =   $this->minder->getPickItemDetails($ssnId);
                        
                        $transaction    =   new Transaction_TRBKA();
                        
                        $transaction->objectId  =   $ssnId;
                        $transaction->whId      =   $whId;
                        $transaction->locnId    =   $location;
                        $transaction->quantity  =   abs($pickItemDetail['QTY_PICKED']);
                        $transaction->reference =   $reason;
                        $transaction->subLocnId =   $this->minder->limitPrinter;
        	        $transaction->companyId = $pickItemDetail['COMPANY_ID'];; // want the company of the issn
        	        $transaction->prodId = $pickItemDetail['PROD_ID']; // want the prod  of the issn
        	        $transaction->orderNo = $pickItemDetail['PICK_ORDER'];; // want the order no for the issns pick_order
                      
                        //if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSS', '', 'MASTER    '))) { 
                        if (false === ($result = $this->minder->doTransactionResponseV6($transaction ))) {
                            $this->view->negativeResults[]['result']   =   false;
                            $this->view->negativeResults[]['message']  =   $this->minder->lastError . ': ' . $ssnId;
                        } else {
                            $this->view->positiveResults[]['result']   =   true;
                            $this->view->positiveResults[]['message']  =   $result;
                                 
                        }
                        
                    }
                    
                    $this->session->params['returned_list'] =   array();
              
                } else {
                    $this->view->negativeResults[]['result']    =   false;
                    $this->view->negativeResults[]['message']   =   'Please, select items.';
                }
                
                $this->view->showResult =   true;
                
                break;
           
           case 'ALL_CANCEL':
           case 'CANCEL':
                
                $this->session->params['first_load']    =   false;
                
                break;
           
           case 'ACCEPT':
           
                $ssnId      =   $this->getRequest()->getParam('returned_ssn_id');
                $location   =   $this->getRequest()->getParam('returned_location');
                $qty        =   $this->getRequest()->getParam('returned_qty');
                $reason     =   $this->getRequest()->getParam('returned_reason');
                
                if(!isset($location)){
                    $location = '';    
                }
                if(!isset($reason)){
                    $reason =   '';
                }
                if($this->minder->limitWarehouse == 'all'){
                    $whId   =   current($this->minder->getListByField('SYS_USER.DEFAULT_WH_ID'));
                } else {
                    $whId   =   $this->minder->limitWarehouse;       
                }
               
                $transaction    =   new Transaction_TRBKA();
                
                $transaction->objectId  =   $ssnId;
                $transaction->whId      =   $whId;
                $transaction->locnId    =   $location;
                $transaction->quantity  =   $qty;
                $transaction->reference =   $reason;
                $transaction->subLocnId =   $this->minder->limitPrinter;
              
                if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSS', '', 'MASTER    '))) { 
                    $this->view->result     =   false;
                    $this->view->message    =   $this->minder->lastError;
                } else {
                    $this->view->result     =   true;
                    $this->view->message    =   $result;    
                }    
       
           default:
           
                $selectedId   = explode('|', $this->getRequest()->getParam('selected_id'));
                
                if(!$this->session->params['first_load']){
                    $this->session->params['returned_list'] =   $selectedId;
                    
                    $this->session->params['first_load']    =   true;
                 
                } 
                
                $returnedList   =   &$this->session->params['returned_list'];
            
                if(count($returnedList) > 0){
                     $currentItem   =   array_shift($returnedList);
                     $data          =   $this->minder->getPackItemDetail($currentItem);
                 
                     $clause        =   array('STORE_TYPE = ? ' => 'RC');
                     
                     $this->view->locationList  =   minder_array_merge(array('' => ''), $this->minder->getLocationListByClause($clause));
                     $this->view->reasonList    =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('RET_REASON'));
                     
                     $this->view->returnedItem  =   $data['SSN_ID'];
                     $this->view->isLast        =   false;
                     $this->view->returnedQty   =   $data['QTY_PICKED'];
                     $this->view->title         =   'RETURNED:';
                      
                } else {
                    $this->session->params['first_load']    =   false;    
               
                    $this->view->isLast                     =   true;
                }   
       }
       
       if($action == 'LOAD_FORM'){
        $this->render('/despatched/returned-all');    
       } else{
            $this->render('/despatched/returned');    
       }
   }
   
   public function editIssnAction(){
       
        $this->view->pageTitle = 'ISSN Edit';
        $this->view->issnObj   = current($this->minder->getIssns(array('SSN_ID = ?' => $this->getRequest()->getParam('edit_issn_ssn_id'))));
                                 
        if (count($this->getRequest()->getPost('action')) > 0) {
    
            switch ($this->getRequest()->getPost('action')) {
                case 'DISCARD':
                    
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('despatched-orders', 'despatch');
                    return;
                    break;
                
                case 'SAVE':
                    $result      = true;
                    $tryToChange = false;
                    
                    
                    
                    if ($this->getRequest()->getPost('edt_issn_original_qty') != $this->view->issnObj->items['ORIGINAL_QTY']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.ORIGINAL_QTY',
                                                                   $this->getRequest()->getPost('edt_issn_original_qty'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_issn_status') != $this->view->issnObj->items['ISSN_STATUS']) {
                        $transaction             = new Transaction_UISTA();
                        $transaction->objectId   = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId       = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId     = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->issnStatus = $this->getRequest()->getPost('edt_issn_issn_status');
                        $transaction->qty        = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult       = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_prod_id') != $this->view->issnObj->items['PROD_ID']) {

                        $transaction                = new Transaction_UIPCA();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->prodIdValue   = $this->getRequest()->getPost('edt_issn_prod_id');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange              = true;
                        $result                   = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_company_id') != $this->view->issnObj->items['COMPANY_ID']) {

                        $transaction            = new Transaction_UICOA();
                        $transaction->objectId  = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId      = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId    = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->companyId = $this->getRequest()->getPost('edt_issn_company_id');
                        $transaction->qty       = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult      = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange            = true;
                        $result                 = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_division_id') != $this->view->issnObj->items['DIVISION_ID']) {

                        $transaction             = new Transaction_UIDIA();
                        $transaction->objectId   = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId       = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId     = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->divisionId = $this->getRequest()->getPost('edt_issn_division_id');
                        $transaction->qty        = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult       = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other1') != $this->view->issnObj->items['OTHER1']) {

                        $transaction                = new Transaction_UIO1A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other1Value   = $this->getRequest()->getPost('edt_issn_other1');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other2') != $this->view->issnObj->items['OTHER2']) {

                        $transaction                = new Transaction_UIO2A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other2Value   = $this->getRequest()->getPost('edt_issn_other2');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    /**/
                    if ($this->getRequest()->getPost('edt_issn_serial_number') != $this->view->issnObj->items['SERIAL_NUMBER']) {

                        $transaction                = new Transaction_UISNA();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locationId    = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->serialNo      = $this->getRequest()->getPost('edt_issn_serial_number');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_status_code') != $this->view->issnObj->items['STATUS_CODE']) {
                        $transaction             = new Transaction_UISCA();
                        $transaction->objectId   = $this->view->issnObj->items['SSN_ID'];
                        $transaction->statusCode = $this->getRequest()->getPost('edt_issn_status_code');
                        try {
                            $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                       'ISSN.STATUS_CODE',
                                                                       $this->getRequest()->getPost('edt_issn_status_code'));
                            //-- commented until problem with response from ADD_TRANSACTION_RESPONSE not solved
                            //$currentResult           = $this->minder->doTransactionResponse($transaction);
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_kitted') != $this->view->issnObj->items['KITTED']) {

                        $transaction              = new Transaction_UIKTA();
                        $transaction->objectId    = $this->view->issnObj->items['SSN_ID'];;
                        $transaction->kittedValue = $this->getRequest()->getPost('edt_issn_kitted');
                        try {
                            $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                       'ISSN.KITTED',
                                                                       $this->getRequest()->getPost('edt_issn_kitted'));
                            //-- commented until problem with response from ADD_TRANSACTION_RESPONSE not solved
                            //$currentResult           = $this->minder->doTransactionResponse($transaction);
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                  = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_current_qty') != $this->view->issnObj->items['CURRENT_QTY']) {
                        
                        if($this->minder->isStockAdjust){
                            
                            $transaction                  = new Transaction_UICQA();
                            $transaction->whId            = $this->view->issnObj->items['WH_ID'];
                            $transaction->locationId      = $this->view->issnObj->items['LOCN_ID'];
                            $transaction->objectId        = $this->view->issnObj->items['SSN_ID'];
                            $transaction->currentQty      = $this->getRequest()->getPost('edt_issn_current_qty');
                            
                            try {
                                $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                            } catch (exception $e) {
                                $currentResult = false;
                            }
                        
                            $tryToChange   = true;
                            $result        = $result && $currentResult;
                            if (false == $currentResult) {
                                $this->addError($this->minder->lastError);
                            }
                                
                        }
                        
                    }

                    if ($this->getRequest()->getPost('edt_issn_wh_id') != $this->view->issnObj->items['WH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.WH_ID',
                                                                   $this->getRequest()->getPost('edt_issn_wh_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_locn_id') != $this->view->issnObj->items['LOCN_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.LOCN_ID',
                                                                    $this->getRequest()->getPost('edt_issn_locn_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_into_date') != $this->view->issnObj->items['INTO_DATE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.INTO_DATE',
                                                                   $this->getRequest()->getPost('edt_issn_into_date'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_pick_order') != $this->view->issnObj->items['PICK_ORDER']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PICK_ORDER',
                                                                   $this->getRequest()->getPost('edt_issn_pick_order'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_audited') != $this->view->issnObj->items['AUDITED']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.AUDITED',
                                                                   $this->getRequest()->getPost('edt_issn_audited'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_prev_package_type') != $this->view->issnObj->items['ISSN_PREV_PACKAGE_TYPE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.ISSN_PREV_PACKAGE_TYPE',
                                                                   $this->getRequest()->getPost('edt_issn_prev_package_type'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_package_type') != $this->view->issnObj->items['ISSN_PACKAGE_TYPE']) {
                        
                        $transaction                  = new Transaction_UIPTA();
                        $transaction->whId            = $this->view->issnObj->items['WH_ID'];
                        $transaction->locationId      = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->objectId        = $this->view->issnObj->items['SSN_ID'];
                        $transaction->qty             = $this->view->issnObj->items['CURRENT_QTY'];
                        $transaction->issnPackageType = $this->getRequest()->getPost('edt_issn_package_type');
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                    
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_pack_id') != $this->view->issnObj->items['PACK_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PACK_ID',
                                                                   $this->getRequest()->getPost('edt_issn_pack_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('edt_issn_despatch_id') != $this->view->issnObj->items['DESPATCH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.DESPATCH_ID',
                                                                   $this->getRequest()->getPost('edt_issn_despatch_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_prev_prod_id_update') != $this->view->issnObj->items['PREV_PREV_PROD_ID_UPDATE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PREV_PROD_ID_UPDATE',
                                                                   $this->getRequest()->getPost('prev_prev_prod_id_update'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('despatched_date') != $this->view->issnObj->items['DESPATCHED_DATE']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.DESPATCHED_DATE',
                                                                   $this->getRequest()->getPost('despatched_date'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_reserved_no') != $this->view->issnObj->items['PREV_PICK_ORDER']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PICK_ORDER',
                                                                   $this->getRequest()->getPost('prev_reserved_no'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('picked_qty') != $this->view->issnObj->items['PICKED_QTY']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PICKED_QTY',
                                                                   $this->getRequest()->getPost('picked_qty'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_prev_resvd_no') != $this->view->issnObj->items['PREV_PREV_PICK_ORDER']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PREV_PICK_ORDER',
                                                                   $this->getRequest()->getPost('prev_prev_resvd_no'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_pack_id') != $this->view->issnObj->items['PREV_PACK_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PACK_ID',
                                                                   $this->getRequest()->getPost('prev_pack_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_despatch_id') != $this->view->issnObj->items['PREV_DESPATCH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_DESPATCH_ID',
                                                                   $this->getRequest()->getPost('prev_despatch_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('prev_prev_desp_id') != $this->view->issnObj->items['PREV_PREV_DESPATCH_ID']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.PREV_PREV_DESPATCH_ID',
                                                                   $this->getRequest()->getPost('prev_prev_desp_id'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('issn_description') != $this->view->issnObj->items['ISSN_DESCRIPTION']) {
                        $currentResult = $this->minder->updateISSN($this->view->issnObj->items['SSN_ID'],
                                                                   'ISSN.ISSN_DESCRIPTION',
                                                                   $this->getRequest()->getPost('issn_description'));
                        $tryToChange   = true;
                        $result        = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                   
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other4') != $this->view->issnObj->items['OTHER4']) {

                        $transaction                = new Transaction_UIO4A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other4Value   = $this->getRequest()->getPost('edt_issn_other4');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange             = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    /**/
                    if ($this->getRequest()->getPost('edt_issn_other3') != $this->view->issnObj->items['OTHER3']) {

                        $transaction                = new Transaction_UIO3A();
                        $transaction->objectId      = $this->view->issnObj->items['SSN_ID'];
                        $transaction->whId          = $this->view->issnObj->items['WH_ID'];
                        $transaction->locnId        = $this->view->issnObj->items['LOCN_ID'];
                        $transaction->other3Value   = $this->getRequest()->getPost('edt_issn_other3');
                        $transaction->qty           = $this->view->issnObj->items['CURRENT_QTY'];
                        
                        try {
                            $currentResult           = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKSKSKSS', '', 'MASTER    ');
                        } catch (exception $e) {
                            $currentResult = false;
                        }
                        $tryToChange           = true;
                        $result                = $result && $currentResult;
                        if (false == $currentResult) {
                            $this->addError($this->minder->lastError);
                        }
                    }
                    
                    

                    if ($tryToChange == true) {
                        if ($result === true) {
                            $this->addMessage('Record ' . $this->view->issnObj->items['SSN_ID'] . ' updated successfully');
                        } else {
                            $this->addError('Record ' . $this->view->issnObj->items['SSN_ID'] . '. Error occured during update');
                        }
                    }
                    break;

            }

            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)->goto('despatched-orders', 'despatch');
                
            return;
        }

        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        $this->view->other2Name = $this->minder->getFieldFromSsnGroup('FIELD22');
        $this->view->other4Name = $this->minder->getFieldFromSsnGroup('FIELD24');
        $this->view->other3Name = $this->minder->getFieldFromSsnGroup('FIELD23');

        $tempArray = array_keys($this->minder->getProductList()); 
        $tempArray = minder_array_merge(array('' => ' '), array_combine($tempArray, $tempArray));
        if (!array_key_exists($this->view->issnObj->items['PROD_ID'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['PROD_ID'] => $this->view->issnObj->items['PROD_ID']), $tempArray);
        }
        $this->view->productList =  $tempArray;

        $tempArray = minder_array_merge(array('' => ' '), $this->minder->getIssnStatusList());
        if (!array_key_exists($this->view->issnObj->items['ISSN_STATUS'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['ISSN_STATUS'] => $this->view->issnObj->items['ISSN_STATUS']), $tempArray);
        }
        $this->view->issnStatusList = $tempArray;

        $tempArray = minder_array_merge(array('' => ' '), $this->minder->getDivisionList());
        if (!array_key_exists($this->view->issnObj->items['DIVISION_ID'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['DIVISION_ID'] => $this->view->issnObj->items['DIVISION_ID']), $tempArray);
        }
        $this->view->divisionList = $tempArray;

        $tempArray = $this->minder->getCompanyList();
        if (!array_key_exists($this->view->issnObj->items['COMPANY_ID'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['COMPANY_ID'] => $this->view->issnObj->items['COMPANY_ID']), $tempArray);
        }
        $this->view->companyList = $tempArray;

        $this->view->warehouseList = $this->minder->getWarehouseListLimited();

        $tempArray = $this->minder->getAuditCodeList();
        if (!array_key_exists($this->view->issnObj->items['AUDITED'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['AUDITED'] => $this->view->issnObj->items['AUDITED']), $tempArray);
        }
        $this->view->auditCodeList = $tempArray;

        $tempArray = $this->minder->getSsnStatusList();
        if (!array_key_exists($this->view->issnObj->items['STATUS_CODE'], $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->issnObj->items['STATUS_CODE'] => $this->view->issnObj->items['STATUS_CODE']), $tempArray);
        }
        $this->view->ssnStatusList = $tempArray;

        $this->view->packageTypeList = $this->minder->getPackageTypeList();

        if (key($this->view->productList) != null) {
            $this->view->ssnTypeList = $this->minder->getSsnTypeListFromProdProfile(current($this->view->productList));
        } else {
            $this->view->ssnTypeList = $this->minder->getSsnTypeListFromSsnType();
        }
        $this->view->brandList   = minder_array_merge(array('' => ''), $this->minder->getBrandList());
        $this->view->varietyList = minder_array_merge(array('' => ''), array());
        
        
        $this->render('/despatched/issn-edit');
   }
   
   // ----------------------------------- Despatched Orders -----------------------------------------------//
   
   // ----------------------------------- Reports -----------------------------------------------//
   
   
   /**
     * Diplay the reports homepage (/reports/index)
     *
     * This page lists the reports.
     *
     * @return void
     */
    public function reportsAction()
    {
        $this->view->pageTitle = "Reports";
        if ($this->minder->isAdmin) {
            switch ($this->getRequest()->getPost('action')) {
                case 'NEW':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('new', 'despatch', '', array());
                    break;
                case 'DELETE':
                    $reportIdList = array();
                    if (count($this->getRequest()->getPost('report_id')) > 0) {
                        foreach($this->getRequest()->getPost('report_id') as $key => $val) {
                            if ($val != '') {
                                $reportIdList[] = $val;
                            }
                        }
                    }
                    $this->session->reportIdList = $reportIdList;
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('delete', 'despatch', '', array('confirm' => 'MASS DELETE'));
                    break;
                default:
                  break;
           }
        }

        //-- @todo: code need rewrite to optimize page by page navigation
        if ($this->getRequest()->getParam('old_start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('old_start_item');
        } else {
            $this->view->startItem = 0;
        }
        if ($this->view->startItem == 0 && $this->getRequest()->getParam('start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('start_item');
        }
        if ($this->getRequest()->getPost('show_by') !== null) {
            $this->view->showBy    = $this->getRequest()->getPost('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } elseif ($this->getRequest()->getParam('show_by') != null) {
            $this->view->showBy    = $this->getRequest()->getParam('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } else {
            $this->view->showBy = 15;
        }
        if ($this->getRequest()->getParam('pageselector') !== null) {
            if ($this->getRequest()->getParam('show_by') === $this->getRequest()->getParam('old_show_by')) {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->view->showBy;
                $this->view->pageSelector = $this->getRequest()->getParam('pageselector');
            } else {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->getRequest()->getParam('old_show_by');
                $this->view->pageSelector = floor($this->view->startItem / $this->view->showBy);
                $this->view->startItem    = $this->view->pageSelector * $this->view->showBy;
            }
        } else {
            $this->view->pageSelector = floor($this->view->startItem /$this->view->showBy);
        }
        settype($this->view->showBy, 'integer');
        settype($this->view->pageSelector, 'integer');
        //-- end process input navigation values
        $this->view->conditions = array();

        $this->view->headers = array('REPORT_ID'        => 'Report ID',
                                     'NAME'             => 'Name',
                                     'DESCRIPTION'      => 'Description',
                                     'COMPANY_ID'       => 'Company',
                                     'LAST_UPDATE_DATE' => 'Last Update',
                                     'LAST_UPDATE_BY'   => 'Last Update By',
                                     'REPORT_TYPE'      => 'Type'
                                    );
        $clause  = array("REPORT_TYPE = 'DS'" => '');
        $reports = $this->minder->getReports($clause);
        if($this->minder->userId != 'Admin') {
            $filteredReports = array();
            foreach ($reports as $value) {
                $pattern = "/INSER|UPDATE|DELETE/";
                if(!preg_match($pattern, $value->items['QUERY'])) {
                    $filteredReports[] = $value;
                }
            }
        } else {
            $filteredReports = $this->minder->getReports($clause);
        }
        $this->view->reports = $filteredReports;//$this->minder->getReports();

        $this->view->numRecords  = count($this->view->reports);

        //-- @todo: code need a tunning for logic
        //-- post process navigation
        if ($this->view->startItem > count($this->view->reports)) {
            $this->view->startItem = count($this->view->reports) - $this->view->showBy;
        }
        if ($this->view->startItem < 0) {
            $this->view->startItem = 0;
        }
        if (($this->view->startItem + $this->view->showBy) > count($this->view->reports)) {
            $this->view->maxno = count($this->view->reports) - $this->view->startItem;
        } else {
            $this->view->maxno = $this->view->showBy;
        }
        //-- end post process

        $this->view->numRecords = count($this->view->reports);
        $this->view->pages      = array();
        for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->showBy); $i++) {
            $this->view->pages[] = $i;
        }
        $this->view->reports = array_slice($this->view->reports,
                                           $this->view->startItem,
                                           $this->view->maxno);
        
        $this->render('/reports/index');

    }

    /**
     * Create a new reports (/reports/new)
     *
     * Displays the form for creating a new report.
     *
     * @return void
     */
    public function newAction()
    {
        if ($this->minder->isAdmin) {
            $this->view->pageTitle = "New Report";

            switch ($this->getRequest()->getPost('action')) {
                case 'SAVE':
                    $new = new ReportLine();

                    $new->save($this->getRequest()->getPost());
                    if ($this->minder->reportCreate($new)) {
                        $params = array('report_id' => $new->id);
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('show', 'despatch', '', $params);
                    } else {
                        $this->view->flashMessenger->addMessage('Unaible to create report. Try again.');
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('new', 'despatch', '', array());
                    }
                    break;
                case 'DISCARD':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('reports', 'despatch', '', array());
                    break;
                default:
                    break;
            }


            if ($this->session->report !== null) {
                $this->view->report = $this->session->report;
                $this->session->report = null;
            } else {
                $this->view->report = new ReportLine();
            }
        } else {
            $this->view->flashMessenger->addMessage('Only Admin allowed to create Report');
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('reports', 'despatch', '', array());
        }
        $this->view->companyList = minder_array_merge(array('' => ''), $this->minder->getCompanyList());
        
        $this->render('/reports/new');
    }

    /**
     * Run a single report specified by id
     * (/reports/show/report_id/id)
     *
     * Displays the table with report data.
     *
     * @return void
     */
    public function showAction()
    {
        $this->view->report = $this->minder->getReport($this->getRequest()->getParam('report_id'));
        $this->view->pageTitle = "Report: #" . $this->view->report->id . ' - ' . $this->view->report->items['NAME'];
        $params = array('report_id' => $this->view->report->id);
        switch ($this->getRequest()->getPost('action')) {
            case 'COPY':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('copy', 'despatch', '', $params);
                break;
            case 'DELETE':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('delete', 'despatch', '', $params);
                break;
            case 'EDIT':
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('edit', 'despatch', '', $params);
                break;
        }
        //-- @todo: code need rewrite to optimize page by page navigation
        if ($this->getRequest()->getParam('old_start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('old_start_item');
        } else {
            $this->view->startItem = 0;
        }
        if ($this->view->startItem == 0 && $this->getRequest()->getParam('start_item') !== null) {
            $this->view->startItem = $this->getRequest()->getParam('start_item');
        }
        if ($this->getRequest()->getPost('show_by') !== null) {
            $this->view->showBy    = $this->getRequest()->getPost('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } elseif ($this->getRequest()->getParam('show_by') != null) {
            $this->view->showBy    = $this->getRequest()->getParam('show_by');
            $this->view->startItem = floor($this->view->startItem / $this->view->showBy)  * $this->view->showBy;
        } else {
            $this->view->showBy = 15;
        }
        if ($this->getRequest()->getParam('pageselector') !== null) {
            if ($this->getRequest()->getParam('show_by') === $this->getRequest()->getParam('old_show_by')) {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->view->showBy;
                $this->view->pageSelector = $this->getRequest()->getParam('pageselector');
            } else {
                $this->view->startItem    = $this->getRequest()->getParam('pageselector') * $this->getRequest()->getParam('old_show_by');
                $this->view->pageSelector = floor($this->view->startItem / $this->view->showBy);
                $this->view->startItem    = $this->view->pageSelector * $this->view->showBy;
            }
        } else {
            $this->view->pageSelector = floor($this->view->startItem /$this->view->showBy);
        }
        settype($this->view->showBy, 'integer');
        settype($this->view->pageSelector, 'integer');
        //-- end process input navigation values

        //-- Try to handle exception if SQL incorrect.

        // restore original handlers
        //restore_exception_handler();
        //restore_error_handler();
        try {
            $listOfPatterns = array();
            if (isset($this->session->reportPrompts)) {
                foreach ($this->session->reportPrompts as $key => $val) {
                    $reportDetail = $this->minder->getReportDetail($key);
                    $listOfPatterns[] = array($reportDetail->items['QUERY_FIELD'] => $val);
                }
            }

            if(!$this->minder->isAdmin) {
                $report = $this->minder->getReport($this->view->report->id);
                $pattern = "/^INSER|UPDATE|DELETE?/";
                if(preg_match($pattern, $report->items['QUERY'])) {
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->view->flashMessenger->addMessage("This report does not exist");
                    $this->_redirector->setCode(303)
                                      ->goto('reports', 'despatch', '', $params);
                }


            }
            $data = $this->minder->reportRun($this->view->report->id, $listOfPatterns);
        } catch (Exception $e) {
            // restore main handlers for project
            //set_exception_handler('exceptionHandler');
            //set_error_handler('errorHandler');
            if ($this->minder->isAdmin) {
                $this->view->flashMessenger->addMessage("Exception while REPORT execution. Please fix it." . $this->minder->lastError );
                $this->_redirector->setCode(303)
                                  ->goto('edit', 'despatch', '', $params);
            } else {
                $this->view->flashMessenger->addMessage("Exception while REPORT execution. Please contact your Admin.");
                $this->_redirector->setCode(303)
                                  ->goto('reports', 'despatch', '', $params);
            }
        }
        if (count($data['table']) > 0) {
            $this->view->data = $data['table'];
        } else {
            $this->view->data = array();
        }
            $this->view->headers = $data['fields'];
            $this->view->reportHeader = $data['report_header'];
            $this->view->reportFooter = $data['report_footer'];
        $this->view->numRecords  = count($this->view->data);

        //-- @todo: code need a tunning for logic
        //-- post process navigation
        if ($this->view->startItem > count($this->view->data)) {
            $this->view->startItem = count($this->view->data) - $this->view->showBy;
        }
        if ($this->view->startItem < 0) {
            $this->view->startItem = 0;
        }
        if (($this->view->startItem + $this->view->showBy) > count($this->view->data)) {
            $this->view->maxno = count($this->view->data) - $this->view->startItem;
        } else {
            $this->view->maxno = $this->view->showBy;
        }
        //-- end post process

        $this->view->numRecords = count($this->view->data);
        $this->view->pages      = array();
        for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->showBy); $i++) {
            $this->view->pages[] = $i;
        }

        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('/reports/report-csv');
                return;
                break;

            case 'REPORT: XML':
                $response = $this->getResponse();
                $response->setHeader('Content-type', 'application/octet-stream');
                $response->setHeader('Content-type', 'application/force-download');
                $response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
                $this->render('/reports/report-xml');
                return;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('/reports/report-xls');
                return;

            case 'REPORT: TXT':
                $this->getResponse()->setHeader('Content-Type', 'text/plain')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.txt"');
                $this->render('/reports/report-txt'); 
                return;
                break;

            case 'REPORT: PDF':
                if ($this->_getParam('step2')) {
                    $this->_exportToPdf();
                } else {
                    $this->view->fonts = array(
                        'courier'      => 'Courier',
                        'courierB'     => 'Courier-Bold',
                        'courierI'     => 'Courier-Oblique',
                        'courierBI'    => 'Courier-BoldOblique',
                        'helvetica'    => 'Helvetica',
                        'helveticaB'   => 'Helvetica-Bold',
                        'helveticaI'   => 'Helvetica-Oblique',
                        'helveticaBI'  => 'Helvetica-BoldOblique',
                        'times'        => 'Times-Roman',
                        'timesB'       => 'Times-Bold',
                        'timesI'       => 'Times-Italic',
                        'timesBI'      => 'Times-BoldItalic',
                    );

                    $this->view->orientations = array(
                        'p' => 'Portrait',
                        'l' => 'Landscape',
                    );
                    $this->view->orientation = 'p';

                    $this->view->formats = array(
                        'a3' => 'A3',
                        'a4' => 'A4',
                        'a5' => 'A5',
                        'letter' => 'Letter',
                        'legal' => 'Legal',
                    );

                    $this->view->size = 11;
                    $this->view->sizes = array(
                        8  => 8,
                        9  => 9,
                        10 => 10,
                        11 => 11,
                        12 => 12,
                        14 => 14,
                        16 => 16,
                        18 => 18,
                        20 => 20,
                    );
                    $this->render('/reports/report-pdf');
                }
                return;

            default:
                break;
        }

        if ($this->getRequest()->getParam('fmt') == null) {
            $this->view->data = array_slice($this->view->data,
                                            $this->view->startItem,
                                            $this->view->maxno);
        } elseif (strtolower($this->getRequest()->getParam('fmt')) == 'txt')  {
        } elseif (strtolower($this->getRequest()->getParam('fmt')) == 'csv') {
        }
        
        $this->render('/reports/show'); 
    }

    /**
     * Allows user to edit existing report specified by id
     * (/reports/edit/report_id/id)
     *
     * Displays the form for editing report.
     *
     * @return void
     */
    public function editAction()
    {
        if ($this->minder->isAdmin) {
            if (false !== ($this->view->report = $this->minder->getReport($this->getRequest()->getParam('report_id')))) {
                $this->view->pageTitle = "Edit Report: #" . $this->view->report->id . ' - ' . $this->view->report->items['NAME'];
                $params = array('report_id' => $this->view->report->id);
                switch ($this->getRequest()->getPost('action')) {
                    case 'SAVE':
                        $this->view->report->save($this->getRequest()->getPost());
                    
                        if(($this->minder->userId != 'Admin')) {
                            $upperQuery = strtoupper($this->view->report->items['QUERY']);
                            $pattern = "/INSERT|DELETE|UPDATE/";
                            if(preg_match($pattern, $upperQuery)) {
                                $this->view->flashMessenger->addMessage('Unable to save report. Try again.' . $this->minder->lastError);
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)
                                                  ->goto('edit', 'despatch', '', $params);
                            }
                        }

                        if ($this->minder->reportUpdate($this->view->report)) {
                            $this->_redirector = $this->_helper->getHelper('Redirector');
                            $this->_redirector->setCode(303)
                                              ->goto('edit', 'despatch', '', $params);
                        } else {
                            $this->view->flashMessenger->addMessage('Unable to save report. Try again.' . $this->minder->lastError);
                            $this->_redirector = $this->_helper->getHelper('Redirector');
                            $this->_redirector->setCode(303)
                                              ->goto('edit', 'despatch', '', $params);
                        }
                        break;
                    case 'DISCARD':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('reports', 'despatch', '', array());
                        break;
                    case 'COPY':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('copy', 'despatch', '', $params);
                        break;
                    case 'DELETE':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('delete', 'despatch', '', $params);
                        break;
                    case 'SHOW':
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('show', 'despatch', '', $params);
                        break;
                    default:
                        break;
                }
            } else {
                $this->view->flashMessenger->addMessage('No report with REPORT_ID = ' . $this->getRequest()->getParam('report_id'));
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('reports', 'despatch', '', array());
            }
        } else {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('reports', 'despatch', '', array());
        }

        $this->view->companyList = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

        $this->view->headers = array('REPORT_DETAIL_ID'  => 'Prompt ID',
                                     'SEQUENCE'          => 'Sequence',
                                     'QUERY_FIELD'       => 'Placeholder #',
                                     'QUERY_PROMPT'      => 'Prompt Message',
                                     'QUERY_DB_FIELD'    => 'Fieldname',
                                     'QUERY_PROMPT_TYPE' => 'Type'
                                    );
        $this->view->prompts = $this->minder->getReportDetails($this->getRequest()->getParam('report_id'));
        $this->render('/reports/edit'); 
    }

    /**
     * Delete existing report specified by id
     * (/reports/delete/report_id/id)
     *
     * @return void
     */
    public function deleteAction()
    {   
        if ($this->minder->isAdmin) {
            switch ($this->getRequest()->getParam('confirm')) {
                case 'YES':
                    $this->view->pageTitle = "Delete Report:" . $this->getRequest()->getParam('report_id');
                    if ($this->getRequest()->getParam('report_id') != null) {
                        $this->minder->reportDelete($this->getRequest()->getParam('report_id'));
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('reports', 'despatch', '', array());

                    }
                    break;
                case 'MASS DELETE';
                    if (count($this->session->reportIdList) > 0) {
                        foreach($this->session->reportIdList as $key => $val) {
                            if (false === $this->minder->reportDelete($val)) {
                                $this->view->flashMessenger->addMessage('Unable to delete report with REPORT_ID ' . $val);
                            }
                        }
                    }
                    $this->session->reportIdList = array();
                    break;
                case 'NO':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('reports', 'despatch', '', array());
                default:
                    $this->view->report = $this->minder->getReport($this->getRequest()->getParam('report_id'));
                    $this->render('/reports/delete');
                    return;
                break;
            }
        }
        
        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_redirector->setCode(303)
                          ->goto('reports', 'despatch', '', array());
        $this->render('/reports/delete'); 
    }

    /**
     * Copy existing report specified by id and
     * open it for editing as new report
     * (/reports/copy/report_id/id)
     *
     * @return void
     */
    public function copyAction()
    {
        if ($this->minder->isAdmin) {
            $new = $this->minder->getReport($this->getRequest()->getParam('report_id'));
            $this->session->report = $new;

            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('new', 'despatch', '', array());
        } else {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('reports', 'despatch', '', array());
        }
        
        $this->render('/reports/copy'); 
    }

    public function savePromptAction()
    {
        $obj = new ReportDetail();
        $obj->save($this->getRequest()->getParams());
        $obj->id = $obj->items['REPORT_DETAIL_ID'];
        switch (strtolower($this->getRequest()->getParam('method'))) {
            case 'save':
                if (null != $obj->id) {
                    if ($this->minder->reportDetailUpdate($obj)) {
                        $data['message'] = 'Prompt updated successfully';
                        $data['action'] = 'update';
                        $data['id'] = $obj->id;
                        $data['data'] = $obj->items;
                    } else {
                        $data['message'] = 'Prompt not updated.' + "\n" + $this->minder->lastError;
                        $data['id'] = false;
                    }
                } else {
                    if ($this->minder->reportDetailCreate($obj)) {
                        $data['message'] = 'Prompt added successfully';
                        $data['action'] = 'insert';
                        $data['id'] = $obj->id;
                        $data['data'] = $obj->items;
                    } else {
                        $data['message'] = 'Prompt not added.' + "\n" + $this->minder->lastError;
                        $data['id'] = false;
                    }
                }
                break;
            case 'delete':
                if (null != $obj->id) {
                    if ($this->minder->reportDetailDelete($obj->id)) {
                        $data['message'] = 'Prompt deleted successfully';
                        $data['action'] = 'delete';
                        $data['id'] = $obj->id;
                        $data['data'] = $obj->items;
                    } else {
                        $data['message'] = 'Prompt not deleted.' + "\n" + $this->minder->lastError;
                        $data['id'] = false;
                    }
                } else {
                    $data['message'] = 'Prompt has no ID and can not be deleted.';
                    $data['id'] = false;
                }
                break;
            default:
                $data['message'] = 'Internal error occured.' . strtolower($this->getRequest()->getParam('action'));
                $data['id'] = false;
            break;
        }

        $this->view->data = $data;
        
        $this->render('/reports/save-prompt'); 
    }

    public function getPromptsAction()
    {
        $report_id = $this->getRequest()->getParam('report_id');
        $prompts = $this->minder->getReportDetails($report_id);
        $this->view->prompts = $prompts;
        $this->view->report_id = $report_id;
        
        $this->render('/reports/get-prompts'); 
    }

    public function setPromptsAction()
    {
        $prompts = array();
        $data = array();
        if (count($this->getRequest()->getParams()) > 3) {
            $data['response'] = true;
            $data['message'] = "Validation success. Press 'Go' to run Report";
            foreach ($this->getRequest()->getParams() as $key => $val) {
                if ('id' == substr($key, 0, 2)) {
                    $obj = $this->minder->getReportDetail(substr($key, 2, strlen($key) - 2));
                    if (false == $obj) {
                        $data['response'] = false;
                        $data['message'] = "Can't retrieve prompt with ID:" . substr($key, 2, strlen($key) - 2);
                        break;
                    } else {
                        if (null == $val) {
                            $data['response'] = false;
                            $data['message'] = "Value '" . $obj->items['QUERY_PROMPT'] . "' can't be EMPTY";
                            break;
                        } else {
                            $prompts[substr($key, 2, strlen($key) - 2)] = $val;
                        }
                    }
                }
            }
        } else {
            $data['response'] = true;
            $data['message'] = "No input required. Press 'Go' to run Report";
        }
        $this->session->reportPrompts = $prompts;
        $data['count'] = count($prompts);
        $this->view->data = $data;
        $this->render('/reports/save-prompt'); 
    }

    protected function _exportToPdf()
    {
        $pdf = new HtmlToPdf($this->_getParam('orientation'), 'mm', $this->_getParam('format'));

        // Append logo in the header.
        if ($logo = imagecreatefromstring(Minder::getInstance()->getLogo())) {
            $filename = rtrim(sys_get_temp_dir(), '\\/') . '/' . md5(uniqid(rand(), true)) . '.png';
            imagepng($logo, $filename);
        } else {
            $this->view->flashMessenger->addMessage('The error occur during image execution.');
            $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
        }
        $pdf->setLogo($filename);

        // Setup margins.
        $margins = (array) $this->_getParam('margins');
        if (!isset($margins['left'])) {
            $margins['left'] = $pdf->lMargin;
        } else {
            $margins['left'] = intval($margins['left']);
        }
        if (!isset($margins['right'])) {
            $margins['right'] = $pdf->rMargin;
        } else {
            $margins['right'] = intval($margins['right']);
        }
        if (!isset($margins['top'])) {
            $margins['top'] = $pdf->tMargin;
        } else {
            $margins['top'] = intval($margins['top']);
        }
        if (!isset($margins['bottom'])) {
            $margins['bottom'] = $pdf->bMargin;
        } else {
            $margins['bottom'] = intval($margins['bottom']);
        }
        $pdf->SetMargins($margins['left'], $margins['top'], $margins['right']);
        $pdf->SetAutoPageBreak(true, $margins['bottom']);   // Bottom margin.

        $pdf->addPage();

        // Setup font.
        if (!$fontSize = $this->_getParam('size')) {
            $fontSize = 11;
        }
        if (!$font = $this->_getParam('font')) {
            $font = 'Arial';
        }
        $pdf->SetFont($font, '', $fontSize);

        $headers = $this->view->headers;
        $i = 0;
        $width = array();
        foreach ($headers as $cell) {
            $width[$i] = $pdf->GetStringWidth($cell);
            $i++;
        }

        $data = array();
        foreach ($this->view->data as $row) {
            $newrow = array();
            $i = 0;
            foreach($row as $cell) {
                $newrow[] = $cell;
                $width[$i] = max($pdf->GetStringWidth($cell), $width[$i]);
                $i++;
            }
            $data[] = $newrow;
        }

        $sumWidth = array_sum($width);
        $ratio = $sumWidth / ($pdf->w - $pdf->lMargin - $pdf->rMargin);
        if ($ratio == 0) {
            $ratio = 1;
        }
        foreach ($width as $k => $v) {
            $width[$k] = $v / $ratio;
        }

        $fontRatio = ($sumWidth + count($width) * 6) / ($pdf->w - $pdf->lMargin - $pdf->rMargin);
        if ($fontRatio == 0) {
            $fontRatio = 1;
        }
        $pdf->SetFont($font, '', $fontSize / $fontRatio);

        $this->_parseTags($this->view->reportHeader)
             ->_parseTags($this->view->reportFooter);

        // Output all content.
        $pdf->writeHTML($this->view->reportHeader);
        $pdf->writeHTML('<br>');
        $pdf->writeTable($headers, $data, $width);
        $pdf->writeHTML($this->view->reportFooter);

        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/octet-stream');
        $response->setHeader('Content-type', 'application/force-download');
        $response->setHeader('Content-Disposition', 'attachment; filename="report.pdf"');
        echo $pdf->render();
        $this->_helper->viewRenderer->setNoRender();
        @unlink($filename);
    }

    protected function _parseTags(&$html)
    {
        if (preg_match_all('#<subreport>(\d+?)</subreport>#i', $html, $matches)) {
            foreach ($matches[1] as $match) {
                try {
                    $subreport = $this->minder->reportRun($match);
                    if (false !== $subreport) {
                        if (is_array($subreport['table']) && ($count = count($subreport['table']))) {
                            if ($count > 1) {
                                $this->view->flashMessenger->addMessage('To many rows, must be only one.');
                                $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                            } else {
                                if (is_array($subreport['table'][0]) && ($count = count($subreport['table'][0]))) {
                                    if ($count > 1) {
                                        $this->view->flashMessenger->addMessage('To many columns, must be only one.');
                                        $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                                    } else {
                                        $result = $subreport['table'][0][key($subreport['table'][0])];
                                        $html = str_replace('<subreport>' . $match . '</subreport>', $result, $html);
                                    }
                                } else {
                                    $this->view->flashMessenger->addMessage('Empty result set.');
                                    $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                                }
                            }
                        } else {
                            $this->view->flashMessenger->addMessage('Empty result set.');
                            $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                        }
                    } else {
                        $this->view->flashMessenger->addMessage('Empty result set.');
                        $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                    }
                } catch (Exception $e) {
                    $this->view->flashMessenger->addMessage($this->view->escape('Invalid format of <subreport> tag.'));
                    $this->_redirect($this->getRequest()->getRequestUri(), array('prependBase' => false));
                }
            }
        }
        return $this;
    }
   
   
   // ----------------------------------- Reports -----------------------------------------------//
    
    /*protected function _setupShortcuts()
    {
        $shortcuts = array();

        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
            $shortcuts['Assembly']                          =   $this->view->url(array('controller' => 'trolley', 'action' => 'index'), null, true);
            $shortcuts['OTC-Issues/Returns']                =   $this->view->url(array('action' => 'index', 'controller' => 'otc'), null, true);
        } else {
            $shortcuts['Awaiting Checking']                 =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-checking', 'module' => 'despatches'), null, true);   
            $shortcuts['OTC-Issues/Returns']                =   $this->view->url(array('action' => 'index', 'controller' => 'otc'), null, true);
        }
        
        switch($this->_action) {
            
            case 'index':
            case 'connote':
//                  $shortcuts['<View Waiting Despatch>']     =   $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true);   
                  $shortcuts['Consignment Exit']            =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
                  $shortcuts['Scan Exit']                   =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);   
                  $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
                  $shortcuts['View Despatched Orders']      =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
                  $shortcuts['Despatch Activity Reports']   =   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);   
                break;
            case 'awaiting-exit':
//                  $shortcuts['View Waiting Despatch']      =   $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true);   
                  $shortcuts['<Consignment Exit>']          =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
                  $shortcuts['Scan Exit']                   =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);   
                  $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
                  $shortcuts['View Despatched Orders']     =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
                  $shortcuts['Despatch Activity Reports']  =   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);   
                break;
            case 'despatched-orders':
//                  $shortcuts['View Waiting Despatch']      =   $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true);   
                  $shortcuts['Consignment Exit']            =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
                  $shortcuts['Scan Exit']                   =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);   
                  $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
                  $shortcuts['<View Despatched Orders>']   =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
                  $shortcuts['Despatch Activity Reports']  =   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);   
                break;
            case 'reports':
            default:
//                  $shortcuts['View Waiting Despatch']     =   $this->view->url(array('action' => 'index', 'controller' => 'despatch'), null, true);   
                  $shortcuts['Consignment Exit']            =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
                  $shortcuts['Scan Exit']                   =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);   
                  $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
                  $shortcuts['View Despatched Orders']     =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
                  $shortcuts['<Despatch Activity Reports>']=   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);   
                break;
        }
        
        $shortcuts['Person Details']                        =   array(
            'PERSON'                                        =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
        );

        $this->view->shortcuts = $shortcuts;

        return $this;
        
    }*/
}
