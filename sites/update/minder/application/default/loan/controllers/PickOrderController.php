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
 * @todo      refactoring
 *
 */

include_once('functions.php');


class PickOrderController extends Minder_Controller_Action
{
	protected $_showBy = 5;

	public function init() {
        
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
	public function indexAction() {
        
        $this->view->pageTitle     = 'Sales Orders';
        if(empty($this->session->pickOrders)) {
            $this->session->pickOrders = array();
        }
        if(empty($this->session->mode)) {
            $this->session->mode = null;
        }
        // fill Order # field by new order number
        if(isset($this->session->newPickOrder)){
            $this->view->pickOrder      =   $this->session->newPickOrder;
            $this->session->newPickOrder=   null;   
        }
		if (isset($_POST['action'])) {
			switch(strtolower($_POST['action'])) {
				case 'confirm':
					$pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
					foreach ($pickOrders as $pickOrder) {
						if (!$this->minder->pickOrderConfirm($pickOrder)) {
							$this->addWarning('Order ' . $pickOrder . ' was not confirmed.');
						}
					}
					$this->_redirect('pick-order');
					break;

				case 'allocate orders':
					$this->_redirector = $this->_helper->getHelper('Redirector');
					$this->_redirector->setCode(303)->goto('allocate-orders', 'pick-order', '');
					break;

				case 'allocate products':
					$this->_redirector = $this->_helper->getHelper('Redirector');
					$this->_redirector->setCode(303)->goto('allocate-products', 'pick-order', '');
					break;

				case 'approve pick':
					$pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
					foreach ($pickOrders as $pickOrder) {
						if (!$this->minder->pickOrderApprovePick($pickOrder)) {
							$this->addWarning('Order ' . $pickOrder . ' was not approved.');
						}
					}
					$this->_redirect('pick-order');
					break;

				case 'approve despatch':
					$pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
					foreach ($pickOrders as $pickOrder) {
						if (!$this->minder->pickOrderApproveDespatch($pickOrder)) {
							$this->addWarning('Order ' . $pickOrder . ' was not dispatched.');
						}
					}
					$this->_redirect('pick-order');
					break;

				case 'hold':
					$pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
					foreach ($pickOrders as $pickOrder) {
						if (!$this->minder->pickOrderHold($pickOrder)) {
							$this->addWarning('Order ' . $pickOrder . ' was not held.');
						}
					}
					$this->_redirect('pick-order');
					break;
					
                case 'run_pick_mode':
                
                	
						// reset all 
                        unset($this->session->mode);
                        unset($this->session->pickModeName);
                        unset($this->session->pickModeCriteria);
                        unset($this->session->pickOrders);
                
                	
                        $mode = $this->getRequest()->getParam('pick_proc');
                        $this->view->pick_mode = substr($mode, -2);
                        
                        // reset pick mode
                        if($mode != null && $mode == 'reset') {
                            unset($this->session->mode);
                            unset($this->session->pickModeName);
                            unset($this->session->pickModeCriteria);
                            unset($this->session->pickOrders);
                            break;
                        } elseif ($mode == null) {
                            return;
                        }

                        
                        $order_types    = $this->getRequest()->getParam('PICK_PARAM_TYPE');
                        $order_modes    = $this->getRequest()->getParam('PICK_PARAM_MODE');
                        $orders         = $this->getRequest()->getParam('PICK_PARAM_ORDNO');
                        $order_statuses = $this->getRequest()->getParam('PICK_PARAM_STATUS');
                        $order_prioritys= $this->getRequest()->getParam('PICK_PARAM_PRIORITY');
                        $ids            = $this->getRequest()->getParam('PICK_PARAM_ID');
                        $one_or_accept  = $this->getRequest()->getParam('one_or_accept');
                         
                         
                        $pickModesList = array();
                        $pickModes     = $this->minder->getPickModes();
                        foreach ($pickModes['data'] as $pickMode) {
                            $pickModesList[$pickMode['PICK_MODE_NO']] = $pickMode['DESCRIPTION'];
                        }
                        
                        $order_types = 'SO';
                        $order_modes = array_search($order_modes, $pickModesList);
                        $this->session->mode = $this->view->pick_mode = $order_modes;
                        
                        if ($orders =='' ) {
                            $orders = 'GETALL';
                        }
                        if ($order_statuses =='' ) {
                            $order_statuses = 'GETALL';
                        }
                        if ($order_prioritys =='' ) {
                            $order_prioritys = 'GETALL';
                        }
                        if ($ids =='' ) {
                            $ids = 'GETALL';
                        }
                        
                        // #416
			            foreach($pickModes['data'] as $pickMode){
                            if($pickMode['MODE_NO'] == $mode){
                                $mode = $pickMode['PROCEDURE_NAME'];
                                break;
                            }
                        }
                        
                        $filterdIds = $this->minder->pickMode($mode, $order_types, $order_modes, $orders, $order_statuses, $order_prioritys, $ids);
                        $ordersList = '';
                        
            			$pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
            			$showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];

            			
                        $counter = 0;
                        
                        foreach($filterdIds['data'] as $value ) {
                            $ordersList .= sprintf("'%s'", $value['WK_ORDER']) . ',';
                        	$counter++;
                        	if ($counter>1450) {
                        		break;    
                        	}
                        }
                        
                        if(!empty($ordersList)) {                        
                        	$this->session->pickModeCriteria['PICK_ORDER.PICK_ORDER IN (' . substr($ordersList, 0, -1) . ') AND '] = '';
                        } else {
                        	$this->session->pickModeCriteria['1=2 AND '] = '';
                        }
                        
                    break;     
				
                case 'cancel':
					$reason = trim($this->_request->getPost('reason'));
					if (empty($reason)) {
						$this->addMessage('Please enter a reason for cancelling.');
					} else {
						$pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
						foreach ($pickOrders as $pickOrder) {
							if (!$this->minder->pickOrderCancel($pickOrder, $reason)) {
								$this->addWarning('Order ' . $pickOrder . ' was not canceled.');
							} else {
								$this->addMessage('Order ' . $pickOrder . ' was canceled.');
								$key = array_search($pickOrder, $this->session->params[$this->_controller]['index']['pick_orders']);
								unset($this->session->params[$this->_controller]['index']['pick_orders'][$key]);
							}
						}
					}
					$this->_redirect('pick-order');
					break;
                
                case 'add':
                    
                    $addProduct     =   $this->_request->getParam('add_product');    
                    $addNonProduct  =   $this->_request->getParam('add_non_product');
                    
                    // if clicked on ADD Product button
                    if($addProduct){
                    
                        $qty    = $this->_request->getParam('product_qty');
                        $what   = $this->_request->getParam('product_options');
                        $name   = $this->_request->getParam('product_name');
             
                        // example 1 in specs
                        if(empty($name) && !empty($qty) && $what == 'product_code'){
                            $mode = 1;
                        }
                        // example 2 in specs
                        if(!empty($name) && !empty($qty) && $what == 'product_code'){
                            $mode = 3;    
                        }
                        // example 3 in specs
                        if(!empty($name) && eregi('%', $name) && !empty($qty) && $what == 'product_code'){
                            $mode = 2;
                        }
                        // example 4 in specs
                        if(!empty($name) && $what == 'product_description'){
                            $mode = 2;
                        }
                        
                        
                         reset($this->session->params[$this->_controller]['index']['pick_orders']);
                        $pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
                        
                        $pickOrderData  =   $this->minder->getPickOrder($pickOrder);
                        
                        switch($mode){
                            case 1:
                                
                                $stdClass               =   new stdClass();
                                $stdClass->makeSearch   =   false;
                                $stdClass->qty          =   $qty;
                                $stdClass->from         =   'pick-order';
                                $stdClass->pickOrder    =   $pickOrder;
                                $stdClass->productName  =   $name; 
                                $stdClass->warehouse    =   $pickOrderData->whId; 
                                $stdClass->company      =   $pickOrderData->companyId;
                                
                                if($what == 'product_code'){
                                    $stdClass->thisIs       =   'product_code';
                                } else {
                                    $stdClass->thisIs       =   'product_description';    
                                }
                                
                                
                                $this->session->params['product_options'] = $stdClass;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('index', 'products', 'warehouse');
                                
                                
                                break;
                            
                             case 2:
                                
                                $stdClass               =   new stdClass();
                                $stdClass->makeSearch   =   true;
                                $stdClass->qty          =   $qty;
                                $stdClass->from         =   'pick-order';
                                $stdClass->pickOrder    =   $pickOrder;
                                $stdClass->productName  =   $name;    
                                $stdClass->warehouse    =   $pickOrderData->whId; 
                                $stdClass->company      =   $pickOrderData->companyId;
                                
                                if($what == 'product_code'){
                                    $stdClass->thisIs       =   'product_code';
                                } else {
                                    $stdClass->thisIs       =   'product_description';    
                                }
                                
                                
                                $this->session->params['product_options'] = $stdClass;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('index', 'products', 'warehouse');
                                
                                
                                break;
                            
                            case 3:
                          
                                $product =   $this->minder->checkAvailableProduct($name, $pickOrderData->companyId, $pickOrderData->whId);
                                
                                if($product['AVAILABLE_QTY'] > 0){
                                    
                                    if($product['AVAILABLE_QTY'] >= $qty){
                                        
                                        $clause  =   array('PROD_ID = ? AND ' => $name); 
                                        $product =   $this->minder->getProductLine1s($clause);
                                        
                                        $this->session->params['pick-order']['index']['pick_items']                 =   $product;
                                        $this->session->params['pick-order']['index']['pick_items']['required_qty'] =   $qty;
                                        $this->session->params['pick-order']['index']['pick_items']['case']         =   1;
                                        $this->session->params['pick-order']['index']['pick_items']['pick_order']   =   $pickOrder;
                                        $this->_setParam('redirect', 'pick-order');   
                                 
                                        $this->addIssnItemsAction();
                                        
                                        $result = $this->session->params['add_issn_item_result'];    
                                    } else {
                                        $this->addError("Can't Add Product - insufficient Available Quantity. (Available = " . $product['AVAILABLE_QTY'] . "): " . $name);        
                                    }
                                } else {
                                    $this->addError("Product Code is not listed in PRODUCT PROFILE: " . $name);   
                                }
                                
                                break;
                            default:
                        }
                   
                    // if clicked on ADD Non-Product button     
                    } elseif($addNonProduct){
                       
                        $qty    = $this->_request->getParam('non_product_qty');
                        $what   = $this->_request->getParam('non_product_options');
                        $name   = $this->_request->getParam('non_product_name');
                        
                        // example 1 in specs
                        if(empty($name) && !empty($qty) && $what == 'issn'){
                            $mode = 1;
                        }
                        // example 1 in specs
                        if(empty($name) && !empty($qty) && $what == 'description'){
                            $mode = 1;
                        }
                        // example 2 in specs
                        if(!empty($name) && !empty($qty) && $what == 'issn'){
                            $mode = 3;    
                        }
                        // example 2 in specs
                        if(!empty($name) && !empty($qty) && $what == 'issn' && eregi('%', $name)){
                            $mode = 2;    
                        }
                        // example 4 in specs
                        if(!empty($name) && $what == 'description'){
                            $mode = 2;
                        }
                        // example 3 in specs
                        if(!empty($name) && eregi('%', $name) && !empty($qty) && $what == 'description'){
                            $mode = 2;
                        }
                        
                
                        reset($this->session->params[$this->_controller]['index']['pick_orders']);
                        $pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
                
                        $pickOrderData  =   $this->minder->getPickOrder($pickOrder);
                                
                
                        switch($mode){
                            case 1:
                            
                                $stdClass                   =   new stdClass();
                                $stdClass->makeSearch       =   false;
                                $stdClass->qty              =   $qty;
                                $stdClass->from             =   'pick-order';
                                $stdClass->pickOrder        =   $pickOrder;
                                $stdClass->company          =   $pickOrderData->companyId;
                                $stdClass->whId             =   $pickOrderData->whId;
                                $stdClass->nonProductName   =   $name;    
                                
                                if($what == 'description'){
                                    $stdClass->thisIs       =   'description';    
                                } else {
                                    $stdClass->thisIs       =   'issn';    
                                } 
                                
                                $this->session->params['non_product_options'] = $stdClass;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('index', 'ssn', 'warehouse');
                                
                                break;
                            
                            case 2:
                                
                                $stdClass                 =   new stdClass();
                                $stdClass->makeSearch     =   true;
                                $stdClass->qty            =   $qty;
                                $stdClass->from           =   'pick-order';
                                $stdClass->pickOrder      =   $pickOrder;
                                $stdClass->company        =   $pickOrderData->companyId;
                                $stdClass->whId           =   $pickOrderData->whId;
                                $stdClass->nonProductName =   $name;    
                                
                                if($what == 'description'){
                                    $stdClass->thisIs       =   'description';    
                                } else {
                                    $stdClass->thisIs       =   'issn';     
                                }
                                
                                 
                                $this->session->params['non_product_options'] = $stdClass;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('index', 'ssn', 'warehouse');
                                
                                break;
                            
                            case 3:
                            
                                $clause     =   array('SSN.SSN_ID = ? AND ' => $name);
                                $nonProduct =   $this->minder->getSsns($clause);
                                
                                if(!empty($nonProduct)){
                                    
                                    $this->session->params['pick-order']['index']['pick_items']                 =   $nonProduct;
                                    $this->session->params['pick-order']['index']['pick_items']['pick_order']   =   $pickOrder;
                                    $this->_setParam('redirect', 'pick-order');   
                             
                                    $this->addIssnItemsAction();
                                    
                                    $result = $this->session->params['add_issn_item_result'];        
                                } else {
                                    $this->addError("Non-Product is not listed in SSN: " . $name);    
                                }
                                
                                
                                
                                break;
                            default:
                        }
                    }
                        
                    break;

			}
		}
        
        $action = strtolower($this->_getParam('do_action'));
		switch($action) {
			case 'get_order':
				$soNumber = strtoupper($this->_getParam('so_number'));
				$this->getOrder($soNumber);

				$conditions             = $this->_getConditions('index');
				$conditions[$soNumber]  = $soNumber;
				$this->session->params[$this->_controller]['index']['pick_orders'] = array_unique(array_merge($this->session->params[$this->_controller]['index']['pick_orders'], array($soNumber)));
				arsort($this->session->params[$this->_controller]['index']['pick_orders']);
				$this->_setConditions($conditions);

				break;
		}

		// @TODO: move to linesAction().
		if (($from = $this->getRequest()->getPost('from')) && $from == 'lines-action') {
			$this->_action = 'lines';
			$this->_preProcessNavigation();
			$this->_action = 'index';
		} else {
			$this->_preProcessNavigation();
		}
        
        
		$this->view->pickStatusDescription = $this->minder->getPickOrderStatusList();
		$this->view->statusList            = minder_array_merge(array('' => ''), $this->minder->getPickOrderStatusList());
		$this->view->shipViaList           = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $this->view->despatchLocationList  = minder_array_merge(array('' => ''), $this->minder->getDespatchLocationList());
		$this->view->companyList           = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

        $this->view->productRegExp         = $this->minder->getValidationParams('PROD_13');    
        $this->view->nonProductRegExp      = $this->minder->getValidationParams('SSN_CODE');    
      
        $this->view->pickLabelNo          = $this->_getParam('pick_label_no');
		
        $this->view->headers = array();
        
        //$fieldsData = $this->minder->getUserHeaders('SCN_SORDER');
        //$this->view->headers    =   $fieldsData['headers'];
        $this->view->headers        = $this->minder->getSelectField('PICK_ORDER');
        $this->view->editInputs     = $this->minder->getEditInputs('PICK_ORDER');
        $this->view->tabList        = $this->minder->getTabList('PICK_ORDER');
        
        list($searchInputs, $dropDownList1, $dropDownList2, $dropDownListNames, $allowed) = $this->minder->getSearchInputs('PICKORDER');
        
        $this->view->dropDownList1      =   array_merge(array('' => ''), $dropDownList1);   
        $this->view->dropDownList2      =   array_merge(array('' => ''), $dropDownList2);
        $this->view->dropDownListNames  =   $dropDownListNames; 
        
        
        $conditions = $this->_makeConditions($allowed);
        $this->view->searchInputs       =   $this->_saveSearchedValue($searchInputs, $conditions);   
        
        $criteria   = $this->_makeClause($conditions, $allowed);
        
        $criteria   = array_merge($criteria, array('PICK_ORDER_TYPE = ? AND ' => 'SO'));
        
        if($this->getRequest()->getParam('field_name1')){
            $criteria   = array_merge($criteria, array($this->getRequest()->getParam('field_name1') . ' = ? AND ' => $this->getRequest()->getParam('field_value1')));
            
            $this->view->fieldName1  =    $this->getRequest()->getParam('field_name1');   
            $this->view->fieldValue1 =    $this->getRequest()->getParam('field_value1');   
        }
        if($this->getRequest()->getParam('field_name2')){
            $criteria   = array_merge($criteria, array($this->getRequest()->getParam('field_name2') . ' = ? AND ' => $this->getRequest()->getParam('field_value2')));
            
            $this->view->fieldName2  =    $this->getRequest()->getParam('field_name2');   
            $this->view->fieldValue2 =    $this->getRequest()->getParam('field_value2');
        }
        
        if($this->session->mode != null) {
            $criteria              = array_merge($criteria, $this->session->pickModeCriteria);
            $this->view->pick_mode = substr($this->session->mode, -2);
        }
       
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
        
        $this->view->pickOrders = array();
        try {
            //$pickOrders                 = $this->minder->getPickOrders($criteria, $pageSelector, $showBy, $fieldsData);
            $pickOrders                 = $this->minder->getPickOrders($criteria, $pageSelector, $showBy );
            $this->view->pickOrders     = $pickOrders['data'];
            //$this->session->pickOrders  = array_slice($pickOrders['all'],0 ,1450); // $this->session->pickOrders  = $pickOrders['all'];
            $this->session->pickOrders  = array_slice($pickOrders['data'],0 ,14); // $this->session->pickOrders  = $pickOrders['all'];
             
        } catch (Exception $e) {
            $this->session->conditions[$this->_controller][$this->_action] = $this->view->conditions = array();
            $this->addError('Error occured while searching.');
        }
        
        $this->_postProcessNavigation($pickOrders);
        $this->view->conditions = $this->_getConditions();
        
        $pickModesList = array();
        $pickModes     = $this->minder->getPickModes();
        foreach ($pickModes['data'] as $pickMode) {
            $pickModesList[$pickMode['PICK_MODE_NO']] = $pickMode['DESCRIPTION'];
        }
        $this->view->listPickModes = array_merge(array(' '=>' '), $pickModesList);
        $this->view->pickModes     = $pickModes['data'];
        
        if(!empty($this->session->mode)) { 
            $this->session->pickModeName = substr($pickModesList[substr($this->session->mode, -2)], 2, strlen($pickModesList[substr($this->session->mode, -2)]));
        }
        
        $this->view->conditions = array_merge($this->_getConditions(), $this->_getConditions('lines'));
    }
	/**
	 * Allows editing of an order (/pick-order/edit)
	 *
	 * Displays an order for editing or saves the changes submitted
	 *
	 * @return void
	 */
	public function editAction() {
        
		$pickOrder  = 'ERROR';
		$request    = $this->getRequest();
		$params     = $request->getParams();
		if (isset($params['pick_order'])) {
			$pickOrder = $params['pick_order'];
		}

		if (!empty($_POST['action'])) {
			switch(strtolower($_POST['action'])) {
				case 'cancel':
					$this->_redirector = $this->_helper->getHelper('Redirector');
					$this->_redirector->setCode(303)->goto('index', 'pick-order');
					break;

				case 'save':
					$pickOrder = $this->view->minder->getPickOrder($params['pick_order']);
					if ($pickOrder->save($_POST)) {
						// Check if user has rights for change Status field. If does not - default UC (UnConfirmed).
						if (isset($_POST['pick_status'])) {
							if ($_POST['pick_status'] != 'UC' && !$this->minder->isAdmin && !$this->minder->isCreditManagerT()) {
								$_POST['pick_status'] = 'UC';
							}
						}
						$pickOrder->imported = 'N';
						$pickOrder->importErrors = 0;

						$pickOrder->sSameAsSoldFrom     = !empty($_POST['s_same_as_sold_from']) ? 'T' : 'F';
						$pickOrder->pSameAsInvoiceTo    = !empty($_POST['p_same_as_invoice_to']) ? 'T' : 'F';
						$pickOrder->supplierList        = !empty($_POST['supplier_list']) ? 'T' : 'F';
						$pickOrder->invWithGoods        = !empty($_POST['inv_with_goods']) ? 'T' : 'F';
						$pickOrder->partialPickAllowed  = 'T';
						$pickOrder->overSized = !empty($_POST['over_sized']) ? 'T' : 'F';


						if (!$this->minder->pickOrderUpdate($pickOrder)) {
							$this->addError('Error occured.');
						} else {
							$this->addMessage('Order ' . $pickOrder->pickOrder . ' was updated.');
						}
						$this->_redirector = $this->_helper->getHelper('Redirector');
						$this->_redirector->setCode(303)->goto('index', 'pick-order');
					}
					break;
			}
		} else {
			$this->view->pageTitle = 'Sales Order: ' . $pickOrder;
			$this->view->pickOrder = $this->view->minder->getPickOrder($pickOrder, 'SO');
		}
		$notes = $this->minder->getPickOrderNotesList();
		$this->view->notes = array();
		foreach ($notes as $k => $v) {
			$this->view->notes[$k] = explode('|', $v);
		}
	}

	/**
	 * Create a new sales order (/pick-order/new)
	 *
	 * Display the form for creating a new sales order. If the user is submitting a previously
	 * filled out form then create the order.
	 *
	 * @return void
	 */
	public function newAction() {
        
		$request = $this->getRequest();
		$params  = $request->getParams();

		if (!isset($params['pick_order_type']) || $params['pick_order_type'] != 'SO') {
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->setCode(303)->goto('index', 'pick-order');
		}
		$pickOrderType = $params['pick_order_type'];

		if (!empty($_POST['action'])) {
			$pickOrder = null;
			switch (strtolower($_POST['action'])) {
				case 'cancel':
					$this->_redirector = $this->_helper->getHelper('Redirector');
					$this->_redirector->setCode(303)->goto('index', 'pick-order');
					break;

				case 'add':
					$pickOrder = new PickOrder();
					// Check if user has rights for change Status field. If does not - default UC (UnConfirmed).
					if (isset($_POST['pick_status'])) {
						if ($_POST['pick_status'] != 'UC' && !$this->minder->isAdmin && !$this->minder->isCreditManagerT()) {
							$_POST['pick_status'] = 'UC';
						}
					}
					if ($pickOrder->save($_POST)) {
						$pickOrder->imported        = 'N';
						$pickOrder->importErrors    = 0;

						$pickOrder->sSameAsSoldFrom     = !empty($_POST['s_same_as_sold_from']) ? 'T' : 'F';
						$pickOrder->pSameAsInvoiceTo    = !empty($_POST['p_same_as_invoice_to']) ? 'T' : 'F';
						$pickOrder->supplierList        = !empty($_POST['supplier_list']) ? 'T' : 'F';
						$pickOrder->invWithGoods        = !empty($_POST['inv_with_goods']) ? 'T' : 'F';
						$pickOrder->partialPickAllowed  = !empty($_POST['partial_pick_allowed']) ? 'T' : 'F';
						$pickOrder->overSized           = !empty($_POST['over_sized']) ? 'T' : 'F';

						if (!$this->minder->pickOrderCreate($pickOrder)) {
							$this->addError('Error occured.');
						} else {
							$this->session->params['pick-order']['index']['pick_items']['pick_order'] = $pickOrder->pickOrder;
                            $this->session->newPickOrder    =   $pickOrder->pickOrder;   
                            $this->add = 1;
							$this->addIssnItemsAction();
							unset($this->add);
						}
					}
					$this->_redirect('pick-order');
					break;
			}
			$this->view->pageTitle = 'New Sales Orders';
			if ($pickOrder == null) {
				$this->view->pickOrder = $this->view->minder->newPickOrder();
			} else {
				$this->view->pickOrder = $pickOrder;
			}
		} else {
			$this->view->pageTitle = 'New Sales Orders';
			$this->view->pickOrder = $this->view->minder->newPickOrder();
		}
		$notes = $this->minder->getPickOrderNotesList();
		$this->view->notes = array();
		$this->view->pickOrder->pickOrderType = $pickOrderType;
		foreach ($notes as $k => $v) {
			$this->view->notes[$k] = explode('|', $v);
		}

		$this->view->withLines = isset($this->session->params['pick-order']['index']['pick_items']) && count($this->session->params['pick-order']['index']['pick_items']) > 1;

        $sysUserData      = $this->minder->getSysUserData();
        $productOwnerList = $this->minder->getProductOwnerList();
        
        if(!empty($sysUserData['COMPANY_ID'])){
            $productOwnerList   =   minder_array_merge(array($sysUserData['COMPANY_ID'] => $sysUserData['COMPANY_ID']), $productOwnerList);        
        } else {
            $productOwnerList   =   minder_array_merge(array($this->minder->defaultControlValues['COMPANY_ID'] => $this->minder->defaultControlValues['COMPANY_ID'], $productOwnerList));           
        }
        
        $this->view->productOwnerList = $productOwnerList;
        
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->renderScript('header.phtml', 'header');
			$this->render();
			$this->renderScript('footer.phtml', 'footer');
		}
	}

	public function linesAction() {

		$this->_preProcessNavigation();
        
        if (!empty($_POST['action1'])) {
			switch (strtolower($_POST['action1'])) {
				case 'add non-product':
					reset($this->session->params[$this->_controller]['index']['pick_orders']);
					$pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
					$this->session->from = array('module' => 'default', 'controller' => 'pick-order', 'action' => 'index', 'pick_order' => $pickOrder, 'product' => 0);
					$this->_redirect('warehouse/ssn/index/from/pick-order/without/1');
					break;

				case 'add product line':
					reset($this->session->params[$this->_controller]['index']['pick_orders']);
					$pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
					$this->session->from = array('module' => 'default', 'controller' => 'pick-order', 'action' => 'index', 'pick_order' => $pickOrder, 'product' => 1);
					$this->_redirect('warehouse/products/index/from/pick-order/without/1');
					break;

				case 'delete line':
					$conditions = $this->_getConditions('calc');
                    $pickOrders = current($this->session->params[$this->_controller]['index']['pick_orders']);
                    
                    try {
                        foreach ($conditions as $id) {
                            if (false === ($pickItem = $this->minder->getPickItemById($id))) {
                                $this->addError('Line ' . $id . ' does not exists.');
                            } else {
                                $this->minder->pickItemDelete($pickItem->pickOrder, $pickItem->pickLabelNo);
                                $this->addMessage('Line ' . $id . ' was deleted from order ' . $pickItem->pickOrder . '.');
                                unset($conditions[$id]);
                            }
                        }
                        $this->_setConditions($conditions, 'calc');
                    } catch (Exception $e) {
                        $this->_helper->flashMessenger->addMessage('Error occured during delete lines.');
                    }
                    $this->_redirect('pick-order');
					break;

				case 'report: csv':
                case 'report: xls':
                case 'report: xml':
                case 'report: txt':
                
                    $this->view->headers = array(
                                                    'PICK_ORDER'         => 'Order #',
                                                    'PICK_LABEL_NO'      => 'Label #',
                                                    'PICK_ORDER_LINE_NO' => 'Line #',
                                                    'PROD_ID'            => 'Product ID',
                                                    'SSN_ID'             => 'ISSN',
                                                    'DESCRIPTION'        => 'Description',
                                                    'SALE_PRICE'         => 'Price',
                                                    'PICK_LINE_STATUS'   => 'Status',
                                                    'DEVICE_ID'          => 'DV',
                                                    'TAX_RATE'           => 'Tax Rate',
                                                    'PICK_ORDER_QTY'     => 'Qty',
                                                    'DISCOUNT'           => 'Disc %',
                                                    'LINE_TOTAL'         => 'Total'
                                                  );

                    $ordersList    = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList = $this->minder->getPickItemsByPickOrders($ordersList);
                    $data          = array();

                    if (is_array($pickItemsList)) {
                        $data = array_intersect_key($pickItemsList, $selectedLines);
                    }
                    $this->view->data = $data;
                    $this->_processReportTo(strtoupper($_POST['action1']));
                    break;

				case 'save changes':
					if (!is_array($salePrice = $this->_request->getPost('sale_price'))) {
						$salePrice = array();
					}
					if (!is_array($pickOrderQty = $this->_request->getPost('pick_order_qty'))) {
						$pickOrderQty = array();
					}
					if (!is_array($discount = $this->_request->getPost('discount'))) {
						$discount = array();
					}

					// Make array of indexes.
					$indexes = array_unique(array_merge(
					array_unique(array_merge($salePrice, $pickOrderQty)),
					$discount));

					// Walk through the array.
					reset($indexes);
					$errors = 0;
					while (list($key, ) = each($indexes)) {
						$update = array();
						if (array_key_exists($key, $salePrice)) {
							$update['sale_price'] = trim($salePrice[$key]);
						}
						if (array_key_exists($key, $pickOrderQty)) {
							$update['pick_order_qty'] = trim($pickOrderQty[$key]);
						}
						if (array_key_exists($key, $discount)) {
							$update['discount'] = trim($discount[$key]);
						}

						if (count($update)) {
							$update['pick_label_no'] = $key;
							$pickItem = $this->minder->getPickItemById($key);

							if (!$this->minder->isAdmin && ($pickItem->pickLineStatus == 'UC' || $pickItem->pickLineStatus == 'OP' || $pickItem->pickLineStatus == 'CF' || $pickItem->pickLineStatus == 'UP')) {
								continue;
							}

							$pickItem->save($update);

							try {
								$pickItem->id = $key;
								$pickItem->lineTotal = round($pickItem->salePrice * $pickItem->pickOrderQty * (1 - $pickItem->discount / 100), 2);
								$this->minder->updatePickItem($pickItem);
							} catch (Exception $e) {
								$errors = 1;
								$this->addError('Error occured during save lines.');
								break;
							}
						}
					}
					if (!$errors) {
						$this->addMessage('The lines have been saved.', 'lines');
					}
					$this->_redirect('pick-order');
					break;
			}
		}

		/**
		 * Calculate and mark stuff for Pick Orders.
		 */

		$id     = $this->_getParam('id');
		$method = $this->_getParam('method');

		// @TODO: Change this to $this->_preProcessNavigation() when code be moved from indexAction().
		if (!isset($this->session->navigation[$this->_controller]['index'])) {
			$this->session->navigation[$this->_controller]['index']['show_by']      = $this->_showBy;
			$this->session->navigation[$this->_controller]['index']['pageselector'] = $this->_pageSelector;
		}

		$allowed = array(
                            'pick_order'            => 'PICK_ORDER.PICK_ORDER = ? AND ',
                            'contact_name'          => 'PICK_ORDER.CONTACT_NAME = ? AND ',
                            'customer_po_wo'        => 'PICK_ORDER.CUSTOMER_PO_WO = ? AND ',
                            'person_id'             => 'PICK_ORDER.PERSON_ID = ? AND ',
                            'p_first_name'          => 'PICK_ORDER.P_FIRST_NAME = ? AND ',
                            'company_id'            => 'PICK_ORDER.COMPANY_ID = ? AND ',
                            'pick_due_date'         => 'PICK_ORDER.PICK_DUE_DATE = ? AND ',
                            'pick_status'           => 'PICK_ORDER.PICK_STATUS = ? AND ',
                            'created_by'            => 'PICK_ORDER.CREATED_BY = ? AND ',
                            'pick_priority'         => 'PICK_ORDER.PICK_PRIORITY = ? AND ',
                            'ship_via'              => 'PICK_ORDER.SHIP_VIA = ? AND ',
                            'despatch_location'     => 'PICK_ORDER.DESPATCH_LOCATION = ? AND ',
                            'd_city'                => 'PICK_ORDER.D_CITY = ? AND ',
                            'd_state'               => 'PICK_ORDER.D_STATE = ? AND ',
                            'd_country'             => 'PICK_ORDER.D_COUNTRY = ? AND ',
                            'from_create_date'      => 'PICK_ORDER.PICK_DUE_DATE >= ? AND ',
                            'to_create_date'        => 'PICK_ORDER.RETURN_DATE <= ? AND ',
                            'special_instructions1' => 'PICK_ORDER.SPECIAL_INSTRUCTIONS1 = ? AND ',
                            'special_instructions2' => 'PICK_ORDER.SPECIAL_INSTRUCTIONS2 = ? AND ',
                            'pick_label_no'         => 'PICK_ITEM.PICK_LABEL_NO = ? AND '
		                 );

		$conditions = $this->_getConditions('index');
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge(array('PICK_ORDER_TYPE = ? AND ' => 'SO'), $clause);
        
        if($this->session->mode != null) {
            $clause = array_merge($clause, $this->session->pickModeCriteria);
        }

		$pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];  

        $lines      = $this->view->minder->getPickOrders($clause, $pageSelector, $showBy );
        $lines      = $lines['data'];
        $numRecords = count($lines);

		$conditions = $this->_getConditions('lines');

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
        
        $this->_setConditions($conditions, 'lines');
       
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
				$this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders);
			}
		}
		$this->_postProcessNavigation($this->view->pickItems);

		if (isset($this->view->counters)) {
			$this->view->counters += array('orders_selected' => $pickOrdersCount);
		} else {
			$this->view->counters = array('orders_selected' => $pickOrdersCount);
		}

        $this->view->reportButtonList      = $this->minder->getReportButtonList();                                      
        $this->view->headers = array(
                                        'PICK_ORDER'         => 'Order #',
                                        'PICK_LABEL_NO'      => 'Label #',
                                        'PICK_ORDER_LINE_NO' => 'Line #',
                                        'PROD_ID'            => 'Product ID',
                                        'SSN_ID'             => 'ISSN',
                                        'DESCRIPTION'        => 'Description',
                                        'SALE_PRICE'         => 'Price',
                                        'PICK_LINE_STATUS'   => 'Status',
                                        'DEVICE_ID'          => 'DV',
                                        'TAX_RATE'           => 'Tax Rate',
                                        'PICK_ORDER_QTY'     => 'Qty',
                                        'DISCOUNT'           => 'Disc %',
                                        'LINE_TOTAL'         => 'Total'
                                      );
        $this->view->editInputs     = $this->minder->getEditInputs('PICK_ITEM');
        
        $this->view->pickItems = array_slice($this->view->pickItems, $this->view->navigation['show_by'] * $this->view->navigation['pageselector'], $this->view->maxno);
	}

	public function calcLinesAction() {
        
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
            
            $this->session->navigation[$this->_controller]['calc']['show_by']      = $this->_showBy;
            $this->session->navigation[$this->_controller]['calc']['pageselector'] = $this->_pageSelector;
       } else {
            $this->session->navigation[$this->_controller]['calc']['show_by']      = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
            $this->session->navigation[$this->_controller]['calc']['pageselector'] = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];    
       }

		$pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
		$this->view->pickItems = array();
		if (count($pickOrders)) {
			$this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders);
		}
        
		$this->view->conditions = $this->_markSelected($this->view->pickItems, $id, null, $method, 'calc');

		$this->view->counters = array(
                                        'lines_selected'    => 0,
                                        'products_selected' => 0,
                                        'issns_selected'    => 0,
                                        'total_selected'    => 0,
                                        'products_displayed'=> 0,
                                        'issns_displayed'   => 0,
                                        'total_displayed'   => 0,
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
		$this->view->counters['total_selected']  = round($this->view->counters['total_selected'], 2);
		$this->view->counters['total_displayed'] = round($this->view->counters['total_displayed'], 2);
	}

	public function addIssnItemsAction() {
        
		$this->_helper->viewRenderer->setNoRender(true);
        $case = $this->session->params['pick-order']['index']['pick_items']['case'];
        
        if($case == 1){
            if($this->minder->defaultControlValues['CONFIRM_WITH_NO_PROD'] == 'T') {
                if (isset($this->session->params['pick-order']['index']['pick_items'])) {
                    try {
                        $result = $this->minder->addPickIssnItem($this->session->params['pick-order']['index']['pick_items']);
                        if (isset($this->session->params['pick-order']['index']['pick_items']['pick_order'])) {
                            if ($this->add) {
                                $this->addMessage('Order '
                                . $this->session->params['pick-order']['index']['pick_items']['pick_order']
                                . ' was added'
                                . (isset($this->session->params['pick-order']['index']['pick_items']) && count($this->session->params['pick-order']['index']['pick_items']) > 1 ? ' (with lines).' : '.'));
                            } else {
                                $this->addMessage('Lines were added in order '
                                . $this->session->params['pick-order']['index']['pick_items']['pick_order'] . '.');
                            }
                        }
                    } catch (Exception $e) {
                        $this->addError('Error occured during add item.');
                    }
                }
            } else {
                
                $pickItems  =   $this->session->params['pick-order']['index']['pick_items'];
                $message    =   '';
                foreach($pickItems as $item){
                    // for product
                    if(!empty($item->items['PROD_ID'])){
                        $message .= $item->items['PROD_ID'] . ' ';    
                    } else {
                        $message .= $item->items['SSN_ID'] . ' ';    
                    }
                }
                $message    =   substr($message, 0, -2); 
                
                $this->addError('Not able to add out of stock Product: ' . $message);   
            }    
        } else {
            if (isset($this->session->params['pick-order']['index']['pick_items'])) {
                try {
                    $result = $this->minder->addPickIssnItem($this->session->params['pick-order']['index']['pick_items']);
                    if (isset($this->session->params['pick-order']['index']['pick_items']['pick_order'])) {
                        if ($this->add) {
                            $this->addMessage('Order '
                            . $this->session->params['pick-order']['index']['pick_items']['pick_order']
                            . ' was added'
                            . (isset($this->session->params['pick-order']['index']['pick_items']) && count($this->session->params['pick-order']['index']['pick_items']) > 1 ? ' (with lines).' : '.'));
                        } else {
                            $this->addMessage('Lines were added in order '
                            . $this->session->params['pick-order']['index']['pick_items']['pick_order'] . '.');
                        }
                    }
                } catch (Exception $e) {
                    $this->addError('Error occured during add item.');
                }
            }        
        }
        
        unset($this->session->params['pick-order']['index']['pick_items']);

        if ($redirect = $this->_getParam('redirect')) {
            $this->_redirect($redirect);
        }    
	}

	public function searchAction() {
        
		$this->view->data   =   array();
        if ($_REQUEST['field'] === 'product_id') {
			$this->view->data = $this->minder->getProductList($_REQUEST['q']);
		}
		if ($_REQUEST['field'] === 'person_id') {
			$oldLimitCompany = null;
			if (isset($_REQUEST['company_id']) && !isset($_REQUEST['nolimit'])) {
				$oldLimitCompany = $this->minder->limitCompany;
				$this->minder->limitCompany = $_REQUEST['company_id'];
			}
			$this->view->data = $this->minder->getPersonList(array('CU', 'CO', 'IN', 'RP'), $_REQUEST['q'], 0, false);
			if ($oldLimitCompany != null) {
				$this->minder->limitCompany = $oldLimitCompany;
			}
		}
		if ($_REQUEST['field'] === 's_person_id') {
			$this->view->data = $this->minder->getPersonList(array('CS', 'CO'), $this->_getParam('q'), 0, true);
		}
		if ($_REQUEST['field'] === 'issn') {
			$this->view->data = $this->minder->getIssnList($_REQUEST['q']);
		}
		if ($_REQUEST['field'] === 'pick_order') {
			$this->view->data = $this->minder->getPickOrdersIdList($_REQUEST['q'], 'TO');
		}
		if ($_REQUEST['field'] === 'd_person_id') {
			$this->view->data = $this->minder->getPersonList('RP', $_REQUEST['q']);
		}
        if($_REQUEST['field'] === 'product_id') {
            $this->view->data = $this->minder->getProductList($_REQUEST['q']);
        }
   }

	public function lookupAction() {
        
		$companyId   = '';
		$warehouseId = '';
		
        try {
			if ($tmp = $this->_getParam('pick_order')) {
				$pickOrder   = $this->minder->getPickOrder($tmp);
				$companyId   = $pickOrder->companyId;
				$warehouseId = $pickOrder->whId;
			}
			if ($tmp = $this->_getParam('company_id')) {
				$this->view->data = $this->minder->getCompany($tmp);
			}
			if ($tmp = $this->_getParam('person_id')) {
				$this->view->data = $this->minder->getPerson($tmp);
			}
			if ($tmp = $this->_getParam('s_person_id')) {
				$this->view->data = $this->minder->getPerson($tmp, true);
			}
			if ($tmp = $this->_getParam('prod_id')) {
				$this->view->data = $this->minder->getProduct($tmp, $companyId, $warehouseId);
			}
			if ($tmp = $this->_getParam('tmp')) {
				$this->view->data = $this->minder->getProductByISSN($tmp);
			}
       } catch (Exception $e) {}
	}

	public function detailAction(){
        
		$this->view->pageTitle = $table = strtoupper($this->getRequest()->getParam('table'));
		if ('' == $table) {
			$this->_redirect('/');
		}

		$controller = $this->_controller;
		$action     = $this->_action;

		//
		// Get allowed.
		//
		$allowed = array();
		$temp    = $this->minder->getFieldList($table);
		if (false != $temp) {
			foreach ($temp as $key => $val) {
				$allowed[$key] = 'UPPER(' . $val . ') LIKE ?';
			}
		}

		//
		// Preprocess navigation.
		//
		if (isset($this->session->navigation[$controller][$action][$table])) {
			foreach ($this->session->navigation[$controller][$action][$table] as $key => $val) {
				if (!is_null($this->getRequest()->getParam($key))) {
					$this->session->navigation[$controller][$action][$table][$key] = (int)$this->getRequest()->getParam($key);
					$this->view->rowNumber = $this->session->navigation[$controller][$action][$table]['show_by'] *
					$this->session->navigation[$controller][$action][$table]['pageselector'] + 1;
				}
			}
		} else {
			$this->session->navigation[$controller][$action][$table]['show_by']      = $this->_showBy;
			$this->session->navigation[$controller][$action][$table]['pageselector'] = $this->_pageSelector;
		}
		if ($this->view->rowNumber > $this->session->navigation[$controller][$action][$table]['show_by'] *
		($this->session->navigation[$controller][$action][$table]['pageselector'] + 1)) {
			$this->session->navigation[$controller][$action][$table]['pageselector'] =
			floor($this->view->rowNumber / $this->session->navigation[$controller][$action][$table]['show_by']);
		} elseif (($this->view->rowNumber-1) >=0 &&
		floor(($this->view->rowNumber-1) / $this->session->navigation[$controller][$action][$table]['show_by']) <=
		($this->session->navigation[$controller][$action][$table]['pageselector'])) {
			$this->session->navigation[$controller][$action][$table]['pageselector'] =
			floor(($this->view->rowNumber-1) / $this->session->navigation[$controller][$action][$table]['show_by']);
		}

		$nav = $this->view->navigation;
		$nav[$table] = $this->session->navigation[$controller][$action][$table];
		$this->view->navigation = $nav;

		if (!isset($this->session->table[$table])) {
			$this->session->table[$table] = array();
		}

		//
		// Get condtitions.
		//
		$conditions = array();
		if (isset($this->session->table)) {
			$conditions = $this->session->table;
		}
		$this->session->table = $this->view->conditions = $conditions;

		//
		// Make clause.
		//
		$clause = $this->_makeClause($conditions[$table], $allowed);
		$clause = $clause + array('_limit_'  => $this->view->navigation[$table]['show_by'], '_offset_' => $this->view->navigation[$table]['show_by'] * $this->view->navigation[$table]['pageselector']);

		//
		// Fetch data.
		//
		$dataset         = $this->minder->getMasterTableDataSet($table, array('*'), $clause);
		if (!$dataset instanceof MasterTable_DataSet) {
			$this->_helper->flashMessenger->addMessage('Invalid table name.');
			$this->_redirect('admin');
		}
		$this->view->numRecords   = $this->session->table[$table]['total'] = $dataset->total();
		$this->view->skipped      = $dataset->skipped();
		$this->view->formId       = 'frm_' . $this->view->tableId = $table;

		//
		// Post process navigation.
		//
		$nav         = $this->view->navigation;
		$nav[$table] = $this->session->navigation[$controller][$action][$table];
		$this->view->navigation = $nav;

		if (($this->view->navigation[$table]['show_by'] * ($this->view->navigation[$table]['pageselector'] + 1)) > $this->view->numRecords) {
			// Recount number of pages.
			$this->view->navigation[$table]['pageselector'] = $this->session->navigation[$controller][$action][$table]['pageselector']
			= (int) floor($this->view->numRecords / $this->view->navigation[$table]['show_by']);
			if (!($this->view->maxno = $this->view->numRecords % $this->view->navigation[$table]['show_by']) &&
			$this->view->numRecords > 0) {
				$this->view->navigation[$table]['pageselector'] = $this->session->navigation[$controller][$action][$table]['pageselector'] -= 1;
				$this->view->maxno = $this->view->navigation[$table]['show_by'];
			}
		} else {
			$this->view->maxno = $this->view->navigation[$table]['show_by'];
		}

		$this->view->pages = array();
		for ($i = 1; $i <= ceil($this->view->numRecords / $this->view->navigation[$table]['show_by']); $i++) {
			$this->view->pages[] = $i;
		}

		$this->view->fieldList = $dataset->getFields();

		//
		// Setup headers.
		//
		$headers = $dataset->getUniqueConstraint();
		$i = count($headers);
		$fieldList = $dataset->getFields();
		foreach ($fieldList as $key => $val) {
			$headers[$key] = $val->name;
		}

		$this->view->headers = $headers;
		$this->view->dataset = $dataset;
		$this->view->row     = $dataset->current();
	}


	/**
	 * Edit a line to an order (/pick-order/edit-line
	 *
	 * Display the form allow the user to add a line to an order or add
	 * the line if the user is submitting the form
	 *
	 * @return void
	 */
	public function editLineAction() {
        
		if ($this->_request->isPost()) {
			$action = strtolower($this->_request->getPost('action'));
			switch ($action) {
				case 'cancel':
					$this->_redirect('pick-order');
					break;

				case 'save':
					$params = $this->_getAllParams();

					$pickItem = new PickItem();
					$valid = $pickItem->save($this->_request->getPost());

					$errors = 0;

					$pickItem->partialPickAllowed = strtoupper(trim($pickItem->partialPickAllowed));

					if (!empty($pickItem->items['PARTIAL_PICK_ALLOWED'])) {
						if ($pickItem->partialPickAllowed != 'T' && $pickItem->partialPickAllowed != 'F') {
							$this->addError("Allowed values for PARTIAL_PICK_ALLOWED field are: 'F', 'T'.");
							$errors = 1;
						}
					}

					$pickItem->allowSubstitute = strtoupper(trim($pickItem->allowSubstitute));
					if (!empty($pickItem->items['ALLOW_SUBSTITUTE'])) {
						if ($pickItem->allowSubstitute != 'T' && $pickItem->allowSubstitute != 'F') {
							$this->addError("Allowed values for ALLOW_SUBSTITUTE field are: 'F', 'T'.");
							$errors = 1;
						}
					}

					if (!$errors) {
						$tmpPickItem = $this->view->minder->getPickItem($params['pick_order'], $params['line_no']);

						// Assign read only fields.
						$pickItem->id                   = $pickItem->pickLabelNo = $tmpPickItem->pickLabelNo;
						$pickItem->pickOrder            = $tmpPickItem->pickOrder;
						$pickItem->pickOrderLineNo      = $tmpPickItem->pickOrderLineNo;
						$pickItem->pickRetrieveStatus   = $tmpPickItem->pickRetrieveStatus;
						$pickItem->despatchLocationGroup= $tmpPickItem->despatchLocationGroup;
						$pickItem->wipPrelocnOrdering   = $tmpPickItem->wipPrelocnOrdering;
						$pickItem->wipPostlocnOrdering  = $tmpPickItem->wipPostlocnOrdering;
						$pickItem->pickQtyDifference    = $tmpPickItem->pickQtyDifference;
						$pickItem->pickQtyDifference2   = $tmpPickItem->pickQtyDifference2;
						$pickItem->lastUpdateDate       = $tmpPickItem->lastUpdateDate;
						$pickItem->pickPickFinish       = $tmpPickItem->pickPickFinish;
						$pickItem->pickLocnSeq          = $tmpPickItem->pickLocnSeq;
						$pickItem->pickLabelDate        = $tmpPickItem->pickLabelDate;
						$pickItem->pickStarted          = $tmpPickItem->pickStarted;
						$pickItem->despatchTs           = $tmpPickItem->despatchTs;
						$pickItem->createDate           = $tmpPickItem->createDate;
						$pickItem->userId               = $tmpPickItem->userId;
						$pickItem->deviceId             = $tmpPickItem->deviceId;
						$pickItem->checkinStart         = $tmpPickItem->checkinStart;
						$pickItem->checkinFinish        = $tmpPickItem->checkinFinish;
						$pickItem->checkinUserId        = $tmpPickItem->checkinUserId;

						if (!empty($tmpPickItem->prodId)) {
							$pickItem->ssnId = $tmpPickItem->ssnId;
						} elseif (!empty($tmpPickItem->ssnId)) {
							$pickItem->prodId = $tmpPickItem->prodId;
						}

						$result = true;
						if ($valid) {
							try {
								$this->minder->updatePickItem($pickItem);
							} catch (Exception $e) {
								$this->addError('Error occured during save line.');
								$result = false;
							}
						} else {
							$this->addError('Error occured during save line.');
							$result = false;
						}
						if ($result) {
							$this->_redirect('pick-order');
						}
					}
					break;
			} // switch
		}

		if (!isset($params)) {
			$params = $this->_getAllParams();
		}
		$this->view->pageTitle = 'Edit Sales Orders Line';
		$this->view->pickOrder = $params['pick_order'];
		$this->view->pickItem  = isset($pickItem) ? $pickItem : $this->view->minder->getPickItem($params['pick_order'], $params['line_no']);

		if (empty($this->view->pickItem->taxRate)) {
			$this->view->pickItem->taxRate = $this->minder->getPickItemTaxRate($this->view->pickItem->pickLabelNo);
		}

		if (empty($this->view->pickItem->warrantyTerm)) {
			list($this->view->pickItem->warrantyTerm) = array_values($this->minder->getControlFields('DEFAULT_WARRANTY'));
		}

		$this->view->shipViaList          = $this->minder->getShipViaList();
		$this->view->productList          = $this->minder->getProductList();
		$this->view->warehouseList        = $this->minder->getWarehouseList(true);
		$this->view->despatchLocationList = $this->minder->getDespatchLocationList($this->view->pickItem->whId);
		$this->view->warrantyList         = $this->minder->getWarrantyList();
	}

	public function shippingServiceAction()	{
        
		if (isset($_REQUEST['ship_via'])) {
			$this->view->data = $this->minder->getShipServiceList($_REQUEST['ship_via']);
		} else {
			$this->view->data = array();
		}
	}

	public function despatchLocationAction() {
        
		if (isset($_REQUEST['wh_id'])) {
			$this->view->data = $this->minder->getDespatchLocationList($_REQUEST['wh_id']);
		} else {
			$this->view->data = array();
		}
	}

	/**
	 * Diplay the page allowing the user to allocate products (/pick-order/allocate-orders)
	 *
	 * This function handle allocating picks for an order.
	 *
	 * @return void
	 */
	public function allocateOrdersAction() {
       
		if (!empty($_POST) && !empty($_POST['allocate'])) {
			$this->_forward('allocate-picks');
		}
		$this->view->pageTitle = 'Allocate Orders';
		$this->view->headers = array('PICK_ORDER'       => 'Order #',
                                     'PICK_STATUS'      => 'Status',
                                     'PICK_PRIORITY'    => 'Priority',
                                     'PICK_DUE_DATE'    => 'Due Date',
                                     'PERSON_ID'        => 'Customer ID',
                                     'D_FIRST_NAME'     => 'Delivery To',
                                     'CUSTOMER_PO_WO'   => 'Customer Ref.',
                                     'SHIP_VIA'         => 'Ship Via',
                                     'WH_ID'            => 'WH',
                                     'COMPANY_ID'       => 'Company ID'
                                    );
		
        $dueDate = (isset($_POST['due_date']) ? $_POST['due_date'] : '');
        
        $this->_preProcessNavigation();
        
        $pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        $allowed    = array('due_date'  =>  'PICK_DUE_DATE = ? AND ');
        $conditions = $this->_setupConditions(null, $allowed);
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge($clause, array('PICK_ORDER.PICK_ORDER_TYPE = ? AND ' => 'SO'));
        
        $ordersList = '';
        if(count($this->session->pickOrders) > 0) {
            foreach($this->session->pickOrders as $filterPickItem) {
               $ordersList .= sprintf("'%s'", $filterPickItem['PICK_ORDER']) . ',';
            }
            $clause = array_merge($clause, array('PICK_ORDER.PICK_ORDER IN (' . substr($ordersList, 0, -1) . ') AND ' => ''));
            $pickItemToDislpay  = $this->view->minder->getPickOrdersForAllocating($clause, $pageNo, $resultsPerPage);
        } else {
            $pickItemToDislpay  = $this->view->minder->getPickOrdersForAllocating($clause, $pageNo, $resultsPerPage);
        }
        
     	$this->view->dueDate     = $dueDate ;
        $this->view->pickOrders  = $pickItemToDislpay['data'];
        if(empty($this->session->pickModeName)) {
            $this->view->pick_mode   = 'none';
        } else {
            $this->view->pick_mode   = $this->session->pickModeName;
        }
        
        $this->_postProcessNavigation($pickItemToDislpay);
	}

	/**
	 * Diplay the page allowing the user to allocate picks to a device (/pick-order/allocate-picks)
	 *
	 * This function handle allocating picks.
	 *
	 * @return void
	 */
	public function allocatePicksAction() {
        
        $this->view->userId   = $userId   = $this->getRequest()->getParam('user_id');
        $this->view->deviceId = $deviceId = $this->getRequest()->getParam('device_id');
        
        if (isset($_POST['action']) && $_POST['action'] == 'Allocate Picks') {
			if (isset($_POST['device_id'])) {
                
                    foreach($_POST['allocate'] as $allocate) {
                        $a = explode('|', $allocate);
                        $d = explode('|', $deviceId);
                        
                        // allocate orders
                        if ($a[0] == 'o') {
                            $t = new Transaction_PKALG();
                            $t->deviceId  = $d[0];
                            $t->pickOrder = $a[1];
                            $t->userId    = $d[1];
                            
                            $result = $this->minder->doTransactionResponse($t);
                            if($result === false) {
                                $_POST['allocate'] = '';
                                $this->addError($this->minder->lastError);
                                $this->_forward('allocate-orders');
                                return;
                                
                            }
                        }
                        
                        // allocate products   
                        if ($a[0] == 'p') {
                            
                            $arrayFileds   = array('MAX_PICK_ORDERS', 'MAX_PICK_PRODUCTS');
                            $controlFields = $this->minder->getControlFields($arrayFileds);
                            $maxOrders     = $controlFields['MAX_PICK_ORDERS'];
                            $maxProducts   = $controlFields['MAX_PICK_PRODUCTS'];
                            $totalOrders   = array();
                            $totalProducts = array();
                           
                            foreach($_POST['allocate'] as $allocate) {
                                $data                    = explode('|', $allocate);
                                $totalOrders[$data[4]]   = $data[4];
                                if($data[2] != 'none') {
                                    $totalProducts[] = $data[2];   
                                }
                            }
                            
                            if(count($totalProducts) <= $maxProducts) {
                                if(count($totalOrders) <= $maxOrders) {
                                    $t = new Transaction_PKALG();
                                    $t->userId      = $userId;
                                    $t->subLocnId   = $d[0];
                                    $t->deviceId    = $d[0];
                                    $t->pickOrder   = $a[1];
                                    $result = $this->minder->doTransactionResponse($t, 'Y', 'SSSSSSSSS', '', 'MASTER    ');
                                    
                                    if($result === false) {
                                        $_POST['allocate'] = '';
                                        $this->addError($this->minder->lastError);
                                        $this->_forward('allocate-products');
                                        return;
                                    }
                                 } else {
                                    $_POST['allocate'] = '';
                                    $this->addError('Count of selected orders > max. allocated orders ');
                                    $this->_forward('allocate-products');
                                    return;    
                                }
                            } else {
                                $_POST['allocate'] = '';
                                $this->addError('Count of selected products > max. allocated products');
                                $this->_forward('allocate-products');
                                return;
                            } 
                        }
                    }
                    $this->addMessage($result);
                    $this->_forward('index');
         	}
		}
		$this->view->pageTitle = 'Allocate Picks';
		$this->view->headers   = array('Device',
                                       'Description',
                                       'User',
                                       'Total'
                                       );
		$this->view->devices   = $this->minder->getDevicesForAllocating();
		$this->view->allocates = $_POST['allocate'];
	}

	/**
	 * Diplay the page allowing the user to allocate products (/pick-order/allocate-products)
	 *
	 * This function handle allocating picks for an order.
	 *
	 * @return void
	 */
	public function allocateProductsAction() {
        
		if (!empty($_POST) && !empty($_POST['allocate'])) {
            $this->_forward('allocate-picks');
        }
        $this->view->pageTitle = 'Allocate Products';
        $this->view->headers = array('PROD_ID'       => 'Product ID', 
                                     'SHORT_DESC'    => 'Description', 
                                     'PICK_ORDER'    => 'Order No', 
                                     'PICK_ORDER_QTY'=> 'Qty', 
                                     'PICK_PRIORITY' => 'Priority', 
                                     'PICK_DUE_DATE' => 'Due Date', 
                                     'WH_ID'         => 'WH', 
                                     'COMPANY_ID'    => 'Company ID', 
                                     'NAME'          => 'Delivery To'
                                    );
       
        $dueDate      = (isset($_POST['due_date']) ? $_POST['due_date'] : '');
        $productId    = (isset($_POST['product_id']) ? $_POST['product_id'] : '');
        
        $this->_preProcessNavigation();
        
        $pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        $allowed        = array('due_date'  =>  'PICK_DUE_DATE = ? AND ',
                                'product_id'=>  'PICK_ITEM.PROD_ID = ? AND ');
        
        $conditions = $this->_setupConditions(null, $allowed);
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge($clause, array('PICK_ORDER.PICK_ORDER_TYPE = ? AND ' => 'SO'));
       
        $ordersList = '';
        if(count($this->session->pickOrders) > 0) {
            foreach($this->session->pickOrders as $filterPickItem) {
               $ordersList .= sprintf("'%s'", $filterPickItem['PICK_ORDER']) . ',';
            }
            $clause = array_merge($clause, array('PICK_ORDER.PICK_ORDER IN (' . substr($ordersList, 0, -1) . ') AND ' => ''));
            $pickItemToDislpay  = $this->view->minder->getPickItemsForAllocating($clause, $pageNo, $resultsPerPage);
        } else {
            $pickItemToDislpay  = $this->view->minder->getPickItemsForAllocating($clause, $pageNo, $resultsPerPage);
        }
        
        $arrayFileds   = array('MAX_PICK_ORDERS', 'MAX_PICK_PRODUCTS');
        $controlFields = $this->minder->getControlFields($arrayFileds);
        $terminalId    = minder_array_merge(array(' ' => ''), $this->minder->getDeviceList('HH'));
        $deviceId      = minder_array_merge(array(' ' => ''), $this->minder->getDeviceList('TR'));
        $usersList     = minder_array_merge(array(' ' => ' '), $this->minder->getSysUserList()); 
        
        $this->view->maxOrders   = $controlFields['MAX_PICK_ORDERS'];
        $this->view->maxProducts = $controlFields['MAX_PICK_PRODUCTS'];
        $this->view->usersList   = $usersList;
        $this->view->terminals   = $terminalId;
        $this->view->devices     = $deviceId;
         
        
        $this->view->dueDate     = $dueDate ;
        $this->view->productId   = $productId;
        $this->view->pickItems   = $pickItemToDislpay['data'];
        if(empty($this->session->pickModeName)) {
            $this->view->pick_mode   = 'none';
        } else {
            $this->view->pick_mode   = $this->session->pickModeName;
        }
        
        $this->_postProcessNavigation($pickItemToDislpay);
	}

	public function addressAction()
	{
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
		$personId = $this->_getParam('person_id');
		$type = $this->_getParam('type');
		$result = $this->minder->getAddresses($type, $personId);
		if (empty($result)) {
			$result = (array) $this->minder->getPerson($personId);
			if (count($result)) {
				// Constrains by person type.
				if (!in_array($result['personType'], array('CU', 'CO', 'RP', 'IN'))) {
					$result = array();
				} else {
					if (empty($result['mailAddress1'])) {
						$result = array(array(
                            'recordId'  => null,
                            'firstName' => $result['firstName'],
                            'lastName'  => $result['lastName'],
                            'line1'     => $result['addressLine1'],
                            'line2'     => $result['addressLine2'],
                            'city'      => $result['city'],
                            'state'     => $result['state'],
                            'postcode'  => $result['postcode'],
                            'country'   => $result['country'],
                            'phone'     => $result['telephone'],
							'contact'	=> substr($result['firstName'].' '.$result['lastName'], 0, 50)
						));
					} else {
						$result = array(array(
                            'recordId'  => null,
                            'firstName' => $result['firstName'],
                            'lastName'  => $result['lastName'],
                            'line1'     => $result['mailAddress1'],
                            'line2'     => $result['mailAddress2'],
                            'city'      => $result['mailCity'],
                            'state'     => $result['mailState'],
                            'postcode'  => $result['mailPostcode'],
                            'country'   => $result['mailCountry'],
                            'phone'     => $result['telephone1'],
							'contact'	=> substr($result['firstName'].' '.$result['lastName'], 0, 50)
						));
					}
				}
			}
			$this->_helper->json($result);
		} else {
			$json = array();
			$person = (array) $this->minder->getPerson($personId);
			foreach ($result as $row) {
				$row = (array) $row;
				$json[] = array(
                    'recordId'  => $row['recordId'],
                    'firstName' => $row['firstName'],
                    'lastName'  => $row['lastName'],
                    'line1'     => $row['line1'],
                    'line2'     => $row['line2'],
                    'city'      => $row['city'],
                    'state'     => $row['state'],
                    'postcode'  => $row['postcode'],
                    'country'   => $row['country'],
                    'phone'     => $row['phone'],
					'contact'	=> substr($person['firstName'].' '.$person['lastName'], 0, 50)
				);
			}
			$this->_helper->json($json);
		}
	}

	/**
	 * @desc get order by SO Number
	 * @param int $poNumber
	 */
	private function getOrder($soNumber) {

		$soNumber             = strtoupper($this->_getParam('so_number'));
		$this->view->response = array('status' => '', 'message' => '', 'data' => array());
		// check if JavaScrip is off
		if(!isset($soNumber) || empty($soNumber)) {
			$this->view->response['status']  = false;
			$this->view->response['message'] = 'Please, enter SO number';

			$this->render('get-order');
			return;
		}

		$SoapPassport = Zend_Registry::get('SoapPassport');

		$soap           = new NetSuite_SoapWrapper();
		$soap->setSilentMode(true);
		$parser         = new NetSuite_Parser();
		$soap->Passport = $SoapPassport;
		$syn            = new NetSuite_Synchronizer($soap, $this->minder->userId, $this->minder->deviceId);
		$syn->setSilentMode(true);


		if ($soap->login()) {
			$this->session->netSuiteCookie = NetSuite_SoapWrapper::$cookie;
		} else {
			$this->view->response['status']  = false;
			$this->view->response['message'] = 'SOAP - Can\'t login';
		}

		// check is SOAP requests not blocked
		if(false == $soap->lockSoapTransaction()) {
			$this->view->response['status']  = false;
			$this->view->response['message'] = 'SOAP queries locked, try later';
		} else{
			// make SOAP request to NetSuite
			$xmlResp = $soap->searchTransactionByTranId($soNumber);
			if (false == ($xmlResp)) {
				$this->view->response['status']  = false;
				$this->view->response['message'] = 'Sales Order not found';
			} else {
				$obj = $parser->parseSearch($xmlResp);
				if(count($obj) == 0) {
					$this->view->response['status'] = false;
					$this->view->response['message'] = 'Order not found ';
				} else {
					if($syn->importData($obj, false) == 0) {
						$this->view->response['status']  = false;
						$this->view->response['message'] = 'Order not passed by a filter - ' . $syn->lastError;
					} else {
						$this->view->response['status']      = true;
						$this->view->response['message']     = '';
						$this->view->response['data']['url'] =  $this->view->url(array('controller' => 'pick-order', 'action' => ''), null, true);
					}
				}
			}
			$soap->unlockSoapTransaction();
		}

		$this->render('get-order');
		return;

	}
	
	public function addFromLocationAction()	{
		
		$conditions   = $this->_getConditions('index');
		
		$pageselector = $this->getRequest()->getParam('pageselector', 0);
		$show_by      = $this->getRequest()->getParam('show_by', 10);
		
		
		$ids = array();
		
		foreach ($conditions as $val => $item) {
			if ($val == $item) {
				$ids[] = $item;
			}
		}
		
		$id = $ids[0]; 
		
		$this->view->headers = array(
			                            'SSN_ID' => 'ISSN',
			                            'PROD_ID' => 'Product ID',
			                            'ISSN_DESCRIPTION' => 'SSN Description',
			                            'CURRENT_QTY' => 'Qty',
			                            'COMPANY_ID' => 'Company ID',
			                            'CREATE_DATE' => 'Created Date',
			                            ''=>'Processing Result'
		                            );
		
		$order = $this->minder->getPickOrder($id, 'SO');
		
//		var_dump($id, $order->despatchLocation); die();

		if ($order->despatchLocation==null && $order->despatchLocation=="") {
			$this->view->ds_null = 1;
			return;	
		}

		$despatchLocation = substr($order->despatchLocation,2);
		$this->session->params[$this->_controller]['addFromLocation']['despatch_location'] = $order->despatchLocation; 
		$whId             = $order->whId;
		
		$clause= array();
		
		$clause['PICK_ORDER IS NULL']		=	'';

		if ($whId!=null) {
			$clause['WH_ID = ?']		=	$whId;
		}
		
		
		if ($despatchLocation!=null) {
			$clause['LOCN_ID = ?']		=	$despatchLocation;
		}
		
		$issns = $this->minder->getIssns($clause);
		$this->view->pages = ceil(count($issns) / $show_by);
		
		$issns                      = array_slice ($issns, $pageselector*$show_by, $show_by);
		$this->view->products       = $issns;
		$this->view->pageselector   = $pageselector;
		$this->view->show_by        = $show_by;
		
		
		$this->view->desc1  = $order->whId.'-'.$despatchLocation; 
		$location           = $this->minder->getLocationListByClause(array('LOCN_ID = ?'=>$despatchLocation));
		$this->view->desc2  = $location[$despatchLocation];
		
		
		$this->session->params[$this->_controller]['addFromLocation']['issns']      = array();
		$this->session->params[$this->_controller]['addFromLocation']['wh_id']      = $whId;
		$this->session->params[$this->_controller]['addFromLocation']['pick_order'] = $id;
		$this->session->params[$this->_controller]['addFromLocation']['clause']     = $clause;
		
	}
	
	public function addFromLocationButtonAction() {
        
		if (isset ($this->session->params[$this->_controller]['addFromLocation']['issns'])) {
			$issns = $this->session->params[$this->_controller]['addFromLocation']['issns'];
		} else {
			return; 
		}

		$jsonObject = new stdClass();
		$result     = array();
		
		foreach ($issns as $id) {
			
			
			$issn = $this->minder->getIssn($id);
			$qty  = $issn['CURRENT_QTY'];
			
			$transaction = new Transaction_PKTRD();

			$whId       = $this->session->params[$this->_controller]['addFromLocation']['wh_id'];
			$locnId     = $this->session->params[$this->_controller]['addFromLocation']['despatch_location']; 
			$pickOrder  = $this->session->params[$this->_controller]['addFromLocation']['pick_order'];
			
			$transaction->whId 	    = $whId; 
			$transaction->locnId    = $locnId; 
			$transaction->objectId  = $id;
			if (strlen($pickOrder) <= 10) {
				$transaction->subLocnId = $pickOrder;
			}
			$transaction->reference = "Picked by Transfer to Despatch |" . $pickOrder;
			$transaction->qty       = $qty;
			
			$row = array();
			
			if ($locnId!=null) { 
				$response = $this->minder->doTransactionResponse($transaction );
				$row['response'] = $this->minder->lastError;
			} else {
				$row['response'] = 'DESPATCH_LOCATION is Null';
			}
			$row['wh_id']       = $whId;
			$row['locn_id']     = $locnId;
			$row['id']          = $id;
			$row['pick_order']  = $pickOrder;
			$row['qty']         = $qty;
			
			
			$result[] = $row;

		}
		
		$this->session->pickOrders = array();
		
		$jsonObject->result = $result;
		die(json_encode($jsonObject));
		
		
	}
	
	public function addFromLocationMarkAction()	{
        
		if (isset ($this->session->params[$this->_controller]['addFromLocation']['issns'])) {
			$issns = $this->session->params[$this->_controller]['addFromLocation']['issns'];
		} else {
			$issns = array(); 
		}
		
		$id = $this->getRequest()->getParam('id');
		
		if ($id!='all') {
			$key = array_search ( $id, $issns);
			if ($key===false) {
				$issns[] = $id;
			} else {
				unset($issns[$key]);
			}
		} else {
			if (count($issns)!=0) {
				$issns = array();
			} else {
				$issns = array();
				$rows = $this->minder->getIssns($this->session->params[$this->_controller]['addFromLocation']['clause']);
				foreach ($rows as $row) {
					$issns[] = $row->items['SSN_ID'];			
				}
			}
		}
		
		$this->session->params[$this->_controller]['addFromLocation']['issns'] = $issns;
		
		$jsonObject        = new stdClass();
		$jsonObject->count = count($issns);
		
		die(json_encode($jsonObject));
	}
	
	public function reportAddFromLocationAction() {
        
		if (isset ($this->session->params[$this->_controller]['addFromLocation']['issns'])) {
			$issns = $this->session->params[$this->_controller]['addFromLocation']['issns'];
		} else {
			die(); 
		}
		
		$data = array();
		foreach($issns as $id) {
			 $data[] = $this->minder->getIssn($id);
		}
		
		$this->view->headers = array(
			                            'SSN_ID' => 'ISSN',
			                            'PROD_ID' => 'Product ID',
			                            'ISSN_DESCRIPTION' => 'SSN Description',
			                            'CURRENT_QTY' => 'Qty',
			                            'COMPANY_ID' => 'Company ID',
			                            'CREATE_DATE' => 'Created Date'
		                            );
		$this->view->data = $data;
        $this->_processReportTo(strtoupper($this->getRequest()->getParam('report')));

	}
    
    public function ajaxSaveOrderDataAction(){
        
        $dataToSaveList     =   json_decode($this->getRequest()->getParam('data_to_save'));
        $updateTrueResult   =   array();
        $updateFalseResult  =   array();
        
        foreach($dataToSaveList as $obj){
            
            list($pickOrder, $fieldName)    =   explode('-', $obj->input_name);
            
            $oldPickOrderData   =   $this->minder->getPickOrders(array('PICK_ORDER = ? AND, ' => $pickOrder));
            $oldPickOrderData   =   current($oldPickOrderData['data']);
            
            $fieldValue         =   $obj->input_value;
            $clause             =   array('PICK_ORDER = ?' => $pickOrder);
            
            if($oldPickOrderData[$fieldName] != $fieldValue){
                
                $result = $this->minder->updateOrderField($clause, $fieldName, $fieldValue);
                
                if($result){
                    $updateTrueResult[$pickOrder]     =   'Pick order ' . $pickOrder . ' was successfully updated.';        
                } else {
                    $updateFalseResult[$pickOrder]    =   'Error while update pick order ' . $pickOrder;
                }
            }  
        }
        
        $this->view->updateTrueResult    =   array_values($updateTrueResult);    
        $this->view->updateFalseResult   =   array_values($updateFalseResult);    
    }
    
    public function ajaxSaveOrderLineDataAction(){
        
        $dataToSaveList     =   json_decode($this->getRequest()->getParam('data_to_save'));
        $updateTrueResult   =   array();
        $updateFalseResult  =   array();
        
        
        foreach($dataToSaveList as $obj){
            
            list($pickOrder, $pickOrderLine, $fieldName)    =   explode('-', $obj->input_name);
            
            $oldPickOrderLineData   =   $this->minder->getPickItem($pickOrder, $pickOrderLine);
            
            $fieldValue         =   $obj->input_value;
            $clause             =   array('PICK_ORDER = ? AND '          =>  $pickOrder,
                                          'PICK_ORDER_LINE_NO  = ? AND ' =>  $pickOrderLine);
            
            if($oldPickOrderLineData->items[$fieldName] != $fieldValue){
                
                $result = $this->minder->updatePickOrderItemField($clause, $fieldName, $fieldValue);
                
                if($result){
                    $updateTrueResult[$oldPickOrderLineData->items['PICK_LABEL_NO']]     =   'Pick order line ' . $oldPickOrderLineData->items['PICK_LABEL_NO'] . ' was successfully updated.';        
                } else {
                    $updateFalseResult[$oldPickOrderLineData->items['PICK_LABEL_NO']]    =   'Error while update pick order line ' . $oldPickOrderLineData->items['PICK_LABEL_NO'];
                }
            }  
        }
        
        
        $this->view->updateTrueResult    =   array_values($updateTrueResult);    
        $this->view->updateFalseResult   =   array_values($updateFalseResult);    
    }
    
    public function ajaxGetNewOrderAction(){
        
        $newPickOrder[]             =   $this->minder->newPickOrder();
        
        $this->view->headers        = $this->minder->getSelectField('PICK_ORDER');
        $this->view->editInputs     = $this->minder->getEditInputs('PICK_ORDER');   
        
        $this->view->pickOrders     =   $newPickOrder;
        
    }
    
    public function ajaxSaveNewOrderAction(){
        
        $dataToSaveList                 =   json_decode($this->getRequest()->getParam('data_to_save'));
        
        $newPickOrder                   =   $this->minder->newPickOrder();
        $newPickOrder->pickOrderType    =   'SO';
        
        foreach($dataToSaveList as $obj){
            
            $propertyName       =   transformToObjectProp($obj->input_name);       
            $propertyValue      =   $obj->input_value;
            
            $newPickOrder->$propertyName    =   $propertyValue;          
        }
        
        if (!$this->minder->pickOrderCreate($newPickOrder)) {
            $message    =   'Error occured.';
            $result     =   false;
        } else {
            $message[]  =   'Order ' . $newPickOrder->pickOrder . ' was added.';
            $result     =   true;
        }
        
        $this->view->json   =   array('result'  =>  $result,
                                      'message' =>  $message);
                                      
    }
}
