<?php

include_once('functions.php');


class PickOrder2Controller extends Minder_Controller_Action {

    const ADD_PRODUCT_SCREEN_NAME = 'ADD_PRODUCT';
    const ADD_NON_PRODUCT_SCREEN_NAME = 'ADD_NON_PRODUCT';

    protected $_showBy = 5;

    protected $_selectedPickOrders = null;
    protected $_pickOrderSearchClause = null;

    public function init(){
        parent::init();
        
        $this->session->returnOrder = $this->_controller;
        
        $this->view->orderSysScreenName = 'PICKORDER';
        $this->view->linesSysScreenName = 'PICKITEM';
        
        $this->_setupShortcuts();    
    }
    
    public function indexAction(){
        
        $this->view->pageTitle     = 'SEARCH SALES ORDER:';
        
        if(empty($this->session->pickOrders)) {
            $this->session->pickOrders = array();
        }
        if(empty($this->session->mode)) {
            $this->session->mode = null;
        }
        // fill Order # field by new order number
        $newPickOrder   =   null;
        if(isset($this->session->newPickOrder)){

            $newPickOrder   =   $this->minder->getPickOrders(array('PICK_ORDER = ? AND ' => $this->session->newPickOrder, 'PICK_ORDER_TYPE = ? AND ' => 'SO'));           
            $newPickOrder   =   array($this->session->newPickOrder => current($newPickOrder['data']));
            
        }
        
        if (isset($_POST['action'])) {
            switch(strtolower($_POST['action'])) {
                case 'submit search':
     
                    unset($this->session->newPickOrder);
                    unset($newPickOrder);
            
                    $this->_setConditions(array(), 'lines');
                    $this->_clearOrderSelection();
             
                    break;
                case 'confirm':
                    $pickOrders = $this->_getSelectedPickOrders();
                    foreach ($pickOrders as $pickOrder) {
                        if (!$this->minder->pickOrderConfirm($pickOrder)) {
                            $this->addWarning('Order ' . $pickOrder . ' was not confirmed.');
                        }
                    }
                    $this->_redirect('pick-order2');
                    break;

                case 'allocate orders':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('allocate-orders', 'pick-order2', '');
                    break;

                case 'allocate products':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('allocate-products', 'pick-order2', '');
                    break;

                case 'approve pick':
                    $pickOrders = $this->_getSelectedPickOrders();
                    foreach ($pickOrders as $pickOrder) {
                        if (!$this->minder->pickOrderApprovePick($pickOrder)) {
                            $this->addWarning('Order ' . $pickOrder . ' was not approved.');
                        }
                    }
                    $this->_redirect('pick-order2');
                    break;

                case 'approve despatch':
                    if (!$this->minder->canApproveSODespatch()) {
                        $this->addError('User do not have permissions to Despatch Orders.');
                        $this->_redirect('pick-order2');
                        break;
                    }

                    $pickOrders = $this->_getSelectedPickOrders();
                    foreach ($pickOrders as $pickOrder) {
                        if (!$this->minder->pickOrderApproveDespatch($pickOrder)) {
                            $this->addWarning('Order ' . $pickOrder . ' was not dispatched.');
                        }
                    }
                    $this->_redirect('pick-order2');
                    break;

                case 'hold':
                    $pickOrders = $this->_getSelectedPickOrders();
                    foreach ($pickOrders as $pickOrder) {
                        (!$this->minder->pickOrderHold($pickOrder)) ?
                            $this->addWarning('Order ' . $pickOrder . ' was not held.') :
                            $this->addMessage('Order ' . $pickOrder . ' was held.');
                    }
                    $this->_redirect('pick-order2');
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
                        $pickOrders = $this->_getSelectedPickOrders();
                        foreach ($pickOrders as $pickOrder) {
                            if (!$this->minder->pickOrderCancel($pickOrder, $reason)) {
                                $this->addWarning('Order ' . $pickOrder . ' was not canceled.');
                            } else {
                                $this->addMessage('Order ' . $pickOrder . ' was canceled.');
                                $this->_unselectPickOrder($pickOrder);
                            }
                        }
                    }
                    $this->_redirect('pick-order2');
                    break;
                
                case 'add product':
                case 'add non-product':
                    
                    $action     =   $_POST['action'];    
                    
                    // if clicked on ADD Product button
                    if($action == 'ADD PRODUCT'){
                    
                        $qty    = $this->_request->getParam('product_qty');
                        $what   = $this->_request->getParam('product_options');
                        $name   = $this->_request->getParam('product_name');
                        $price  = $this->_request->getParam('product_price');  
             
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
                        
                        $pickOrder      = $this->_getFirstSelectedOrder();
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
                                $stdClass->defaultPrice =   $price; 
                                
                                if($what == 'product_code'){
                                    $stdClass->thisIs       =   'product_code';
                                } else {
                                    $stdClass->thisIs       =   'product_description';    
                                }
                                
                                
                                $this->session->params['product_options']   = $stdClass;
                                $this->session->params['product_price']     = $price;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('init', 'product-search', 'warehouse');
                                
                                
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
                                $stdClass->defaultPrice =   $price; 
                                
                                if($what == 'product_code'){
                                    $stdClass->thisIs       =   'product_code';
                                } else {
                                    $stdClass->thisIs       =   'product_description';    
                                }
                                
                                
                                $this->session->params['product_options']   = $stdClass;
                                $this->session->params['product_price']     = $price;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('init', 'product-search', 'warehouse');
                                
                                
                                break;
                            
                            case 3:
                          
                                if($this->minder->defaultControlValues['CONFIRM_WITH_NO_PROD'] == 'F') {
                                    
                                    $tmpAvailableQty = 0;
                                    
                                    try {
                                        $product         = $this->minder->checkAvailableProduct($name, $pickOrderData->companyId, $pickOrderData->whId);
                                        $tmpAvailableQty = $product['AVAILABLE_QTY'];
                                    } catch (Exception $e) {
                                        $this->addError('Cannot check product available quantity: ' . $e->getMessage());
                                    }
                                
                                    if($tmpAvailableQty > 0){
                                    
                                        if($tmpAvailableQty >= $qty){
                                            
                                            $clause  =   array('PROD_ID = ? AND ' => $name); 
                                            $product =   $this->minder->getProductLine1s($clause);
                                            
                                            $this->session->params['pick-order2']['index']['pick_items']                 =   $product;
                                            $this->session->params['pick-order2']['index']['pick_items']['required_qty'] =   $qty;
                                            $this->session->params['pick-order2']['index']['pick_items']['case']         =   1;
                                            $this->session->params['pick-order2']['index']['pick_items']['pick_order']   =   $pickOrder;
                                            $this->session->params['pick-order2']['index']['pick_items']['price']        =   $price;
                                            $this->_setParam('redirect', 'pick-order2');   
                                     
                                            $this->addIssnItemsAction();
                                            
                                            $result = $this->session->params['add_issn_item_result'];    
                                        } else {
                                            $this->addError("Can't Add Product - insufficient Available Quantity. (Available = " . $product['AVAILABLE_QTY'] . "): " . $name);        
                                        }
                                    } else {
                                        $stdClass               =   new stdClass();
                                        $stdClass->makeSearch   =   true;
                                        $stdClass->qty          =   $qty;
                                        $stdClass->from         =   'pick-order';
                                        $stdClass->pickOrder    =   $pickOrder;
                                        $stdClass->productName  =   $name . '%';
                                        $stdClass->warehouse    =   $pickOrderData->whId;
                                        $stdClass->company      =   $pickOrderData->companyId;
                                        $stdClass->defaultPrice =   $price;

                                        if($what == 'product_code'){
                                            $stdClass->thisIs       =   'product_code';
                                        } else {
                                            $stdClass->thisIs       =   'product_description';
                                        }


                                        $this->session->params['product_options']   = $stdClass;
                                        $this->session->params['product_price']     = $price;

                                        $this->_redirector = $this->_helper->getHelper('Redirector');
                                        $this->_redirector->setCode(303)->goto('init', 'product-search', 'warehouse');
                                    }    
                                } else {
                                    
                                    $clause  =   array('PROD_ID = ? AND ' => $name); 
                                    $product =   $this->minder->getProductLine1s($clause);
                                    
                                    $this->session->params['pick-order2']['index']['pick_items']                 =   $product;
                                    $this->session->params['pick-order2']['index']['pick_items']['required_qty'] =   $qty;
                                    $this->session->params['pick-order2']['index']['pick_items']['case']         =   1;
                                    $this->session->params['pick-order2']['index']['pick_items']['pick_order']   =   $pickOrder;
                                    $this->_setParam('redirect', 'pick-order2');   
                             
                                    $this->addIssnItemsAction();
                                    
                                    $result = $this->session->params['add_issn_item_result'];    
                                }
                                
                                break;
                            default:
                        }
                   
                    // if clicked on ADD Non-Product button     
                    } elseif($action == 'ADD NON-PRODUCT'){
                       
                        $qty    = $this->_request->getParam('non_product_qty');
                        $what   = $this->_request->getParam('non_product_options');
                        $name   = $this->_request->getParam('non_product_name');
                        $price  = $this->_request->getParam('non_product_price');  
             
                        
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
                        
                        $pickOrder      = $this->_getFirstSelectedOrder();
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
                                $stdClass->defaultPrice     =   $price;     
                                
                                if($what == 'description'){
                                    $stdClass->thisIs       =   'description';    
                                } else {
                                    $stdClass->thisIs       =   'issn';    
                                } 
                                
                                $this->session->params['non_product_options']   = $stdClass;
                                $this->session->params['non_product_price']     = $price;
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('search-non-product', 'ssn2', 'warehouse');
                                
                                break;
                            
                            case 2:
                                
                                $stdClass                 =   new stdClass();
                                $stdClass->makeSearch     =   true;
                                $stdClass->qty            =   $qty;
                                $stdClass->from           =   'pick-order2';
                                $stdClass->pickOrder      =   $pickOrder;
                                $stdClass->company        =   $pickOrderData->companyId;
                                $stdClass->whId           =   $pickOrderData->whId;
                                $stdClass->nonProductName =   $name;    
                                $stdClass->defaultPrice   =   $price;    
                                
                                if($what == 'description'){
                                    $stdClass->thisIs       =   'description';    
                                } else {
                                    $stdClass->thisIs       =   'issn';     
                                }
                                
                                 
                                $this->session->params['non_product_options']   = $stdClass;
                                $this->session->params['non_product_price']     = $price;
                                
                                
                                $this->_redirector = $this->_helper->getHelper('Redirector');
                                $this->_redirector->setCode(303)->goto('search-non-product', 'ssn2', 'warehouse');
                                
                                break;
                            
                            case 3:
                            
                                $clause     =   array('ISSN.SSN_ID = ? ' => $name);
                                $nonProduct =   current($this->minder->getIssns($clause, 'edit'));
                                
                                if(!empty($nonProduct)){
                                    
                                    $this->session->params['pick-order2']['index']['pick_items']                 =   array($nonProduct->items);
                                    $this->session->params['pick-order2']['index']['pick_items']['pick_order']   =   $pickOrder;
                                    $this->session->params['pick-order2']['index']['pick_items']['case']         =   3;
                                    $this->session->params['pick-order2']['index']['pick_items']['required_qty'] =   $qty;
                                    $this->session->params['pick-order2']['index']['pick_items']['price']        =   $price;
                                    $this->_setParam('redirect', 'pick-order2');   
                             
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
                $this->_selectPickOrder($pickOrder);
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
        
        if($this->getRequest()->getPost('action1')){
            $this->_forward('lines');    
        }
        
        $this->view->pickStatusDescription = $this->minder->getPickOrderStatusList();
        $this->view->statusList            = minder_array_merge(array('' => ''), $this->minder->getPickOrderStatusList());
        $this->view->shipViaList           = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $this->view->despatchLocationList  = minder_array_merge(array('' => ''), $this->minder->getDespatchLocationList());
        $this->view->companyList           = minder_array_merge(array('' => ''), $this->minder->getCompanyList());

        $this->view->pickLabelNo           = $this->_getParam('pick_label_no');
        
        $this->view->headers = array();
        
        try {

            $screenBuilder = new Minder_SysScreen_Builder();
            list($searchInputs, $allowed) = $this->minder->getSearchInputs2('PICKORDER');

            if(isset($allowed["PICK_DUE_DATE"])){
                $allowed["PICK_DUE_DATE"] = " zerotime(PICK_DUE_DATE) >= ? AND ";
            }

            $this->view->editInputs       = $this->minder->getEditInputs2('PICKORDER','SR','list');
            //echo "<pre>"; die(print_r($this->view->editInputs));

            $ssActions                    = array();
            foreach($screenBuilder->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Action('PICKORDER')) as $ssAction) {
                $ssActions[$ssAction['SSV_NAME']] = isset($ssActions[$ssAction['SSV_NAME']]) ? $ssActions[$ssAction['SSV_NAME']] : array();
                $ssActions[$ssAction['SSV_NAME']][] = $ssAction;
            }
            $this->view->ssActions = $ssActions;
            $this->view->tabList          = $this->minder->getTabList('PICKORDER');
            $this->view->headers          = $this->minder->getHeadersForTabList('PICKORDER', $this->view->tabList);
        } catch(Exception $e) {
            $this->addError($e->getMessage());
        }

        $thisIsSearch = ('submit search' == strtolower($this->getRequest()->getPost('action', 'none'))) ? true : false;

       list($searchInputs, $allowed) = $this->_saveSearchedDLValue($searchInputs, $allowed, NULL, NULL, $thisIsSearch);
        $conditions                   = $this->_makeConditions($allowed);
        

//date update

        $session = new Zend_Session_Namespace();
        $converted_conditons=$conditions;

        if($this->minder->isNewDateCalculation() == false){
            $tz_from=$session->BrowserTimeZone;

            foreach($converted_conditons as $key=>$value){

               if(DateTime::createFromFormat('Y-m-d H:i:s',$value) !== FALSE  || DateTime::createFromFormat('Y-m-d', $value)!== FALSE) {
                    $dt = new DateTime($value, new DateTimeZone($tz_from));
                    $dt->setTimeZone(new DateTimeZone('UTC'));
                    $utc=$dt->format('Y-m-d h:i:s') ;
                    $converted_conditons[$key] = $utc;
                }
            }
        }
                

//date update

        $this->view->searchInputs     = $this->_saveSearchedValue($searchInputs, $conditions);  



        $criteria                     = $this->_makeClause($converted_conditons, $allowed, $searchInputs);

        $criteria                     = array_merge(array('PICK_ORDER_TYPE = ? AND ' => 'SO'), $criteria);
        //echo "<pre>"; die(print_r($criteria));
        if($this->session->mode != null) {
            $criteria              = array_merge($criteria, $this->session->pickModeCriteria);
            $this->view->pick_mode = substr($this->session->mode, -2);
        }
       
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
        
        $this->view->pickOrders   = array();
        $this->view->editInputsEx = array();
        try {
            
            $pickOrders                   = $this->minder->getPickOrders($criteria, $pageSelector, $showBy );
            $screenColors                 = $this->minder->getScreenColors('PICKORDER');
            $this->view->salesOrderTotals = $this->_getSalesOrderTotals($criteria);

            if(!is_null($newPickOrder)){
                array_shift($pickOrders['data']);
                $pickOrders['data'] = $newPickOrder + $pickOrders['data'];
            }
            
            $this->view->pickOrders     = $pickOrders['data'];
            $this->session->pickOrders  = array_slice($pickOrders['data'],0 ,14);
             
            //lets build extended array of edit inputs for each datarow with list filtering etc.
            foreach ($this->view->pickOrders as $key => $tmpPickOrder) {
                foreach ($this->view->editInputs as $tabId => $tab) {
                    foreach ($tab as $fieldName => $fieldDescription) {
                        if ($fieldDescription['SSV_INPUT_METHOD'] == 'DD') {
                            //in pickorder screen we filter all dropdowns by PERSON_ID
                            $tmpPersonId = $tmpPickOrder['PERSON_ID'];
                            
                            $filterCond = array();
                            if (!empty($tmpPersonId)) {
                                $filterCond['PERSON_ID'] = $tmpPersonId;
                            }
                            
                           $fieldDescription = $this->minder->getDropDownExInfo($fieldDescription, $filterCond, false);
                        } 

                        $this->view->editInputsEx[$key][$tabId][$fieldName] = $fieldDescription;
                        
                    }
                }
                
                $this->view->editInputsEx[$key] = $this->minder->highlightEditInputs($this->view->editInputsEx[$key], $tmpPickOrder, $screenColors);
            }
        } catch (Exception $e) {
            $this->session->conditions[$this->_controller][$this->_action] = $this->view->conditions = array();
            $this->addError('Error occured while searching: '.$e->getMessage());
        }
        
        $this->_postProcessNavigation($pickOrders);
        $this->view->conditions = $this->_getSelectedPickOrders();

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
        
        $this->view->cancelReasons = array_merge(array('' => ''), $this->minder->getOptionsList('CAN_ORDER'));
        
        $selectionMode             = $this->getRequest()->getParam('selection_mode', $this->_getPickOrderSelectionMode());
        $this->_setPickOrderSelectionMode($selectionMode);

        $this->view->selectionMode = $this->session->selectionMode['pick_order_selection_mode'] = $selectionMode;
        $this->view->selectedOrdersAmount = $this->_getSelectedPickOrdersAmount();
        try {
            $screenBuilder = new Minder_SysScreen_Builder();
            list($this->view->pickOrderScreenButtons) = $screenBuilder->buildScreenButtons('PICKORDER');
            usort($this->view->pickOrderScreenButtons, create_function('$a, $b', 'return $a[$a["ORDER_BY_FIELD_NAME"]] - $b[$b["ORDER_BY_FIELD_NAME"]];'));
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
    }

    protected function _getSalesOrderTotals($salesOrderSearchCriteria, $getTotal = true, $getSelected = true) {
        $result = array('SALES_ORDER_TOTAL_DUE' => 0, 'SALES_ORDER_SELECTED_DUE' => 0);

        try {
            if ($getTotal) {
                $saleOrderTotals = $this->minder->getPickOrdetTotalsForSalesOrderScreen($salesOrderSearchCriteria);
                $result['SALES_ORDER_TOTAL_DUE'] = $saleOrderTotals['TOTAL_DUE_AMOUNT'];
            }

            if ($getSelected) {
                $selectedOrders = $this->_getSelectedPickOrders();

                if (count($selectedOrders) > 0) {
                    $salesOrderSearchCriteria["PICK_ORDER.PICK_ORDER IN ('" . implode("', '", $selectedOrders) . "') AND "] = '';
                    $saleOrderTotals = $this->minder->getPickOrdetTotalsForSalesOrderScreen($salesOrderSearchCriteria);
                    $result['SALES_ORDER_SELECTED_DUE'] = $saleOrderTotals['TOTAL_DUE_AMOUNT'];
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }

    public function linesAction(){
        $this->getResponse()->setHeader('channel-request-id', $this->getRequest()->getHeader('channel-request-id'));
        $this->_preProcessNavigation();
        if (!empty($_POST['action1'])) {
            switch (strtolower($_POST['action1'])) {
                case 'add non-product':
                    $pickOrder = $this->_getFirstSelectedOrder();
                    $this->session->from = array('module' => 'default', 'controller' => 'pick-order2', 'action' => 'index', 'pick_order' => $pickOrder, 'product' => 0);
                    $this->_redirect('warehouse/ssn/index/from/pick-order2/without/1');
                    break;


                case 'add product line':
                    $pickOrder = $this->_getFirstSelectedOrder();
                    $this->session->from = array('module' => 'default', 'controller' => 'pick-order2', 'action' => 'index', 'pick_order' => $pickOrder, 'product' => 1);
                    $this->_redirect('warehouse/products/index/from/pick-order2/without/1');
                    break;

                case 'delete line':
                    $conditions = $this->_getConditions('calc');

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
                    $this->_redirect('pick-order2');
                    break;

                case 'report: csv':
                case 'report: xls':
                case 'report: xml':
                case 'report: txt':
                
                    $this->view->headers = $this->minder->getHeadersForTabList('PICKITEM', $this->view->tabList);
        

                    $ordersList    = $this->_getSelectedPickOrders();
                    $selectedLines = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList = $this->minder->getPickItemsByPickOrders($ordersList, null, null, null, "PICK_ITEM");
                    $pickItemsList = $pickItemsList['data'];    
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
                    $this->_redirect('pick-order2');
                    break;

                case 'clear_selection':
                    $this->_clearOrderSelection();
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

        list($searchInputs, $dropDownList1, $dropDownList2, $dropDownListNames, $allowed) = $this->minder->getSearchInputs('PICKORDER');
        
        $conditions = $this->_getConditions('index');
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge(array('PICK_ORDER_TYPE = ? AND ' => 'SO'), $clause);
        
        if($this->session->mode != null) {
            $clause = array_merge($clause, $this->session->pickModeCriteria);
        }

        $pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];  
        $lines        = array();

        try{
            $lines      = $this->minder->getPickOrders($clause, $pageSelector, $showBy );
            $lines      = $lines['data'];
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
        
        if(isset($this->session->newPickOrder)){
            $newPickOrder   =   null;
            $newPickOrder   =   $this->minder->getPickOrders(array('PICK_ORDER = ? AND ' => $this->session->newPickOrder, 'PICK_ORDER_TYPE = ? AND ' => 'SO'));           
            $newPickOrder   =   array($this->session->newPickOrder => current($newPickOrder['data']));

            $lines          =   array_merge($lines, $newPickOrder);
        }
        
        $selectionMode = $this->getRequest()->getParam('selection_mode', $this->_getPickOrderSelectionMode());
        $this->_setPickOrderSelectionMode($selectionMode)->_selectPickOrders($id, $method, $lines);

        $selectedOrders = $this->_getSelectedPickOrders();

        $this->session->selectionMode['pick_order_selection_mode'] = $selectionMode;

        $this->_setConditions($selectedOrders, 'lines');

        $pickOrdersCount = $this->_getSelectedPickOrdersAmount();

        //check for orders statuses
        $this->view->ordersStatuses = $this->minder->checkOrdersStatus($selectedOrders);

        /**
         * Calculate and mark stuff for Pick Items (Lines).
         */

        $this->calcLinesAction();
        
        $this->view->selectedPickOrders = $pickOrders = $selectedOrders;
        $this->view->pickItems = array();
        $data = array();

        if (count($pickOrders)) {
            
            $pageSelector = $this->session->navigation[$this->_controller]['lines']['pageselector'];
            $showBy       = $this->session->navigation[$this->_controller]['lines']['show_by'];
            
            try {
                $data                   = $this->minder->getPickItemsByPickOrders($pickOrders, null, $pageSelector, $showBy, 'PICK_ITEM');
                $this->view->pickItems  = $data['data'];
            } catch (Exception $e) {
                $this->addError($e->getMessage());
            }
            
        }
        
        $this->_postProcessNavigation($data);

        if (isset($this->view->counters)) {
            $this->view->counters += array('orders_selected' => $pickOrdersCount);
        } else {
            $this->view->counters = array('orders_selected' => $pickOrdersCount);
        }

        $this->view->reportButtonList       = $this->minder->getReportButtonList();                                      
        $this->view->tabList                = $this->minder->getTabList('PICKITEM');
        $this->view->headers                = $this->minder->getHeadersForTabList('PICKITEM', $this->view->tabList);
        $this->view->editInputs             = $this->minder->getEditInputs2('PICKITEM');
        $screenBuilder = new Minder_SysScreen_Builder();
        $ssActions                    = array();
        foreach($screenBuilder->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Action('PICKITEM')) as $ssAction) {
            $ssActions[$ssAction['SSV_NAME']] = isset($ssActions[$ssAction['SSV_NAME']]) ? $ssActions[$ssAction['SSV_NAME']] : array();
            $ssActions[$ssAction['SSV_NAME']][] = $ssAction;
        }
        $this->view->ssActions = $ssActions;

        list($this->view->showProductPrice, $this->view->showNonProductPrice)   =   $this->minder->getProductNonProductPriceFiled();
        
        $optionRecord                  = Minder_SysScreen_Legacy_OptionManager::getScnRadioButton();
        $tmpDbDefaultSelectionMode     = (empty($optionRecord)) ? 'all' : 'one';
        $selectionMode                 = (isset($this->session->selectionMode['pick_item_selection_mode'])) ? $this->session->selectionMode['pick_item_selection_mode'] : $tmpDbDefaultSelectionMode;
        $selectionMode                 = (in_array($selectionMode, array('all', 'one'))) ? $selectionMode : $tmpDbDefaultSelectionMode;
        $this->view->itemSelectionMode = $this->session->selectionMode['pick_item_selection_mode'] = $selectionMode;

        try {
            $builder = new Minder_SysScreen_Builder();
            $this->view->renderAddProduct    = $builder->isSysScreenDefined(self::ADD_PRODUCT_SCREEN_NAME);
            $this->view->renderAddNonProduct = $builder->isSysScreenDefined(self::ADD_NON_PRODUCT_SCREEN_NAME);
            list($this->view->pickItemScreenButtons) = $builder->buildScreenButtons('PICKITEM');
            usort($this->view->pickItemScreenButtons, create_function('$a, $b', 'return $a[$a["ORDER_BY_FIELD_NAME"]] - $b[$b["ORDER_BY_FIELD_NAME"]];'));
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
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

        $pickOrders = $this->_getSelectedPickOrders();
        $this->view->pickItems = array();
        if (count($pickOrders)) {
            $pageSelector = $this->session->navigation[$this->_controller]['lines']['pageselector'];
            $showBy       = $this->session->navigation[$this->_controller]['lines']['show_by'];
            
            try {
                $this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders, null, $pageSelector, $showBy, 'PICK_ITEM');
                $this->view->pickItems = $this->view->pickItems['data'];
                $tmpTotals             = $this->minder->getPickItemTotalsForSalesOrderScreen($pickOrders);
                $pickOrderTotals       = $this->_getSalesOrderTotals(array(), false, true);
            } catch (Exception $e) {
                $this->addError($e->getMessage());
            }
        }
        
        $optionRecord                  = Minder_SysScreen_Legacy_OptionManager::getScnRadioButton();
        $tmpDbDefaultSelectionMode     = (empty($optionRecord)) ? 'all' : 'one';
        $selectionMode                 = (isset($this->session->selectionMode['pick_item_selection_mode'])) ? $this->session->selectionMode['pick_item_selection_mode'] : $tmpDbDefaultSelectionMode;
        $selectionMode                 = $this->getRequest()->getQuery('selection_mode', $selectionMode);
        $selectionMode                 = (in_array($selectionMode, array('all', 'one'))) ? $selectionMode : $tmpDbDefaultSelectionMode;
        $this->view->itemSelectionMode = $this->session->selectionMode['pick_item_selection_mode'] = $selectionMode;

        $this->view->conditions = array();
        if ($selectionMode == 'one' && $method != 'init') {
            $this->view->conditions = $this->_markSelected($this->view->pickItems, 'select_all', null, 'false', 'calc');
        }
        $this->view->conditions = $this->_markSelected($this->view->pickItems, $id, null, $method, 'calc');

        $this->view->counters = array(
                                        'lines_selected'    => 0,
                                        'products_selected' => 0,
                                        'issns_selected'    => 0,
                                        'total_selected'    => 0,
                                        'products_displayed'=> $tmpTotals['PRODUCTS_DISPLAYED'],
                                        'issns_displayed'   => $tmpTotals['ISSNS_DISPLAYED'],
                                        'total_displayed'   => $tmpTotals['LINES_TOTAL'],
                                        'selected_lines'    => array(),
                                        'sales_order_selected_due' => $pickOrderTotals['SALES_ORDER_SELECTED_DUE']
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
//            if (!empty($pickItem->items['SSN_ID'])) {
//                $this->view->counters['issns_displayed']++;
//            }
//            if (!empty($pickItem->items['PROD_ID'])) {
//                $this->view->counters['products_displayed']++;
//            }

//            $this->view->counters['total_displayed'] +=  $pickItem->items['LINE_TOTAL'];

        }
        $this->view->counters['total_selected']  = round($this->view->counters['total_selected'], 2);
        $this->view->counters['total_displayed'] = round($this->view->counters['total_displayed'], 2);
        $this->view->counters['selected_lines']  = $this->view->conditions;
    }
    
    public function newAction(){
        
        $request = $this->getRequest();
        $params  = $request->getParams();

        if (!isset($params['pick_order_type']) || $params['pick_order_type'] != 'SO') {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)->goto('index', 'pick-order2');
        }
        $pickOrderType = $params['pick_order_type'];

        if (!empty($_POST['action'])) {
            $pickOrder = null;
            switch (strtolower($_POST['action'])) {
                case 'cancel':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('index', 'pick-order2');
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

                        $pickOrder->sSameAsSoldFrom         = !empty($_POST['s_same_as_sold_from']) ? 'T' : 'F';
                        $pickOrder->pSameAsInvoiceTo        = !empty($_POST['p_same_as_invoice_to']) ? 'T' : 'F';
                        $pickOrder->supplierList            = !empty($_POST['supplier_list']) ? 'T' : 'F';
                        $pickOrder->invWithGoods            = !empty($_POST['inv_with_goods']) ? 'T' : 'F';
                        $pickOrder->partialPickAllowed      = !empty($_POST['partial_pick_allowed']) ? 'T' : 'F';
                        $pickOrder->partialDespatchAllowed  = !empty($_POST['partial_despatch_allowed']) ? 'T' : 'F';
                        $pickOrder->overSized               = !empty($_POST['over_sized']) ? 'T' : 'F';
                        
                        if (!$this->minder->pickOrderCreate($pickOrder)) {
                            $this->addError('Error occured.'.$this->minder->lastErrorCode.': '.$this->minder->lastError);
                        } else {
                            $this->session->params['pick-order2']['index']['pick_items']['pick_order'] = $pickOrder->pickOrder;
                            $this->session->newPickOrder    =   $pickOrder->pickOrder;   
                            $this->add = 1;
                            //$this->addIssnItemsAction();
                            unset($this->add);
                        }
                    }
                    $this->_redirect('pick-order2');
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

        $this->view->withLines = isset($this->session->params['pick-order2']['index']['pick_items']) && count($this->session->params['pick-order2']['index']['pick_items']) > 1;

        $sysUserData      = $this->minder->getSysUserData();
        $productOwnerList = $this->minder->getProductOwnerList();
        
        if(!empty($sysUserData['COMPANY_ID'])){
            $productOwnerList   =   minder_array_merge(array($sysUserData['COMPANY_ID'] => $sysUserData['COMPANY_ID']), $productOwnerList);        
        } else {
            $productOwnerList   =   minder_array_merge(array($this->minder->defaultControlValues['COMPANY_ID'] => $this->minder->defaultControlValues['COMPANY_ID'], $productOwnerList));           
        }
        
        $this->view->productOwnerList = $productOwnerList;
        
        try {
            $this->view->pickOrder->shippingMethod = (empty($this->view->pickOrder->shippingMethod)) ? 'FIS' : $this->view->pickOrder->shippingMethod;
            $this->view->shippingMethods = $this->minder->getOptionsList('SHIP_METH');
        } catch (Exception $e) {
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage()));
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->renderScript('header.phtml', 'header');
            $this->render();
            $this->renderScript('footer.phtml', 'footer');
        }
    }
    
    public function editAction(){
        
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
                    $this->_redirector->setCode(303)->goto('index', 'pick-order2');
                    break;

                case 'save':
                    $pickOrder = $this->minder->getPickOrder($params['pick_order']);
                    if ($pickOrder->save($_POST)) {
                        // Check if user has rights for change Status field. If does not - default UC (UnConfirmed).
                        if (isset($_POST['pick_status'])) {
                            if ($_POST['pick_status'] != 'UC' && !$this->minder->isAdmin && !$this->minder->isCreditManagerT()) {
                                $_POST['pick_status'] = 'UC';
                            }
                        }
                        $pickOrder->imported = 'N';
                        $pickOrder->importErrors = 0;

                        $pickOrder->sSameAsSoldFrom         = !empty($_POST['s_same_as_sold_from']) ? 'T' : 'F';
                        $pickOrder->pSameAsInvoiceTo        = !empty($_POST['p_same_as_invoice_to']) ? 'T' : 'F';
                        $pickOrder->supplierList            = !empty($_POST['supplier_list']) ? 'T' : 'F';
                        $pickOrder->invWithGoods            = !empty($_POST['inv_with_goods']) ? 'T' : 'F';
                        $pickOrder->partialPickAllowed      = !empty($_POST['partial_pick_allowed']) ? 'T': 'F';
                        $pickOrder->partialDespatchAllowed  = !empty($_POST['partial_despatch_allowed']) ? 'T': 'F';
                        $pickOrder->overSized               = !empty($_POST['over_sized']) ? 'T' : 'F';


                        if (!$this->minder->pickOrderUpdate($pickOrder)) {
                            $this->addError('Error occured. '.$this->minder->lastErrorCode.': '.$this->minder->lastError);
                        } else {
                            $this->addMessage('Order ' . $pickOrder->pickOrder . ' was updated.');
                        }
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)->goto('index', 'pick-order2');
                    }
                    break;
            }
        } else {
            $this->view->pageTitle               = 'Sales Order: ' . $pickOrder;
            $this->view->pickOrder               = $this->view->minder->getPickOrder($pickOrder, 'SO');
            $this->view->ledgerCodesDescriptions = array('by_code' => array());

            $this->view->displayLedgerCodes = isset($this->minder->defaultControlValues) && ($this->minder->defaultControlValues['LEGACY_LEDGER_ACCOUNTS'] == 'T');
            
            if ($this->view->displayLedgerCodes) {
                $codes  = array();
                
                if (!empty($this->view->pickOrder->legacyLedgerAdminFeeCode)) {
                    $codes[] = $this->view->pickOrder->legacyLedgerAdminFeeCode;
                }
                
                if (!empty($this->view->pickOrder->legacyLedgerFreightCode)) {
                    $codes[] = $this->view->pickOrder->legacyLedgerFreightCode;
                }
                
                if (!empty($this->view->pickOrder->legacyLedgerDepositCode)) {
                    $codes[] = $this->view->pickOrder->legacyLedgerDepositCode;
                }

                if (!empty($this->view->pickOrder->legacyLedgerSaleCode)) {
                    $codes[] = $this->view->pickOrder->legacyLedgerSaleCode;
                }

                if (!empty($this->view->pickOrder->legacyLedgerSaleSsnIdCode)) {
                    $codes[] = $this->view->pickOrder->legacyLedgerSaleSsnIdCode;
                }
                
                $tmpCompanyId                        = $this->view->pickOrder->companyId;
                $this->view->ledgerCodesDescriptions = $this->view->minder->getLedgerCodesDescriptions($codes, $tmpCompanyId);
            }
        }
        
        $notes = $this->minder->getPickOrderNotesList();
        $this->view->notes = array();
        foreach ($notes as $k => $v) {
            $this->view->notes[$k] = explode('|', $v);
        }

        try {
            $this->view->pickOrder->shippingMethod = (empty($this->view->pickOrder->shippingMethod)) ? 'FIS' : $this->view->pickOrder->shippingMethod;
            $this->view->shippingMethods = $this->minder->getOptionsList('SHIP_METH');
        } catch (Exception $e) {
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage()));
        }
    }
    
    public function ajaxSaveOrderDataAction(){
        
        $dataToSaveList     =   json_decode($this->getRequest()->getParam('data_to_save'));
        $updateTrueResult   =   array();
        $updateFalseResult  =   array();
        
        $pickOrderToRecalculate = array();
        
        foreach($dataToSaveList as $obj){
            
            list($pickOrder, $fieldName)    =   explode('-', $obj->input_name);
            
            $objProperty        =   transformToObjectProp($fieldName);
            
            $oldPickOrderData   =   $this->minder->getPickOrder($pickOrder, 'SO');
            
            $fieldValue         =   $obj->input_value;
            $clause             =   array('PICK_ORDER = ?' => $pickOrder);
            
            if($oldPickOrderData->$objProperty != $fieldValue){
                if ($this->minder->canEditSalesOrder() || ((strtolower($fieldName) == 'PICK_STATUS') && ($this->minder->canAdjustSalesOrder()))) {
                    $result = $this->minder->updateOrderField($clause, $fieldName, $fieldValue);
                    $pickOrderToRecalculate[$pickOrder] = $pickOrder;
                    
                    if($result){
                        $updateTrueResult[$pickOrder]     =   'Pick order ' . $pickOrder . ' was successfully updated.';        
                    } else {
                        $updateFalseResult[$pickOrder]    =   'Error while update pick order ' . $pickOrder . '. Reason: ' . $this->minder->lastError;
                    }
                } else {
                    $updateFalseResult[$pickOrder]    =   'Error while update pick order ' . $pickOrder . '. Reason: no permissions.';
                }
            }  
        }
        
        foreach ($pickOrderToRecalculate as $pickOrder) {
            $this->minder->pickOrderRecalculate($pickOrder);
        }
        
        $this->view->updateTrueResult    =   array_values($updateTrueResult);    
        $this->view->updateFalseResult   =   array_values($updateFalseResult);    
    }
    
    public function ajaxSaveOrderLineDataAction(){
        
        $dataToSaveList     =   json_decode($this->getRequest()->getParam('data_to_save'));
        $updateTrueResult   =   array();
        $updateFalseResult  =   array();
        
        $ordersToUpdateStatus = array();
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
                    $ordersToUpdateStatus[$pickOrder]['PICK_ORDER'] = $pickOrder;
                    $ordersToUpdateStatus[$pickOrder]['PICK_LINES'][$pickOrderLine] = $pickOrderLine;
                } else {
                    $updateFalseResult[$oldPickOrderLineData->items['PICK_LABEL_NO']]    =   'Error while update pick order line ' . $oldPickOrderLineData->items['PICK_LABEL_NO'];
                }
            }  
        }
        
        foreach ($ordersToUpdateStatus as $pickOrder) {
            $this->minder->pickOrderRecalculate($pickOrder['PICK_ORDER']);
        }
        
        
        $this->view->updateTrueResult    =   array_values($updateTrueResult);    
        $this->view->updateFalseResult   =   array_values($updateFalseResult);    
    }
    
    public function ajaxGetNewOrderAction(){
        
        $extendedAction = $this->getRequest()->getParam('extended_action', 'new');
        
        $conditions = $this->_getConditions();

        $changedName            = $this->getRequest()->getParam('changed_name');
        $changedValue           = $this->getRequest()->getParam('changed_value', '');
                
        if ($changedValue === '') {
            unset($conditions[$changedName]);
        } else {
            $conditions[$changedName] = $changedValue;
        }
        
        $selectedTab                =   $this->getRequest()->getParam('selected_tab');
        $newPickOrder               =   $this->minder->newPickOrder();
        
        $this->view->tabList        =   $this->minder->getTabList('PICKORDER');
        $this->view->headers        =   $this->minder->getHeadersForTabList('PICKORDER', $this->view->tabList);
        
        $this->view->editInputs     =   $this->minder->getEditInputs2('PICKORDER', 'SR', 'new');
        $screenBuilder = new Minder_SysScreen_Builder();
        $ssActions                    = array();
        foreach($screenBuilder->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Action('PICKORDER')) as $ssAction) {
            $ssActions[$ssAction['SSV_NAME']] = isset($ssActions[$ssAction['SSV_NAME']]) ? $ssActions[$ssAction['SSV_NAME']] : array();
            $ssActions[$ssAction['SSV_NAME']][] = $ssAction;
        }
        $this->view->ssActions = $ssActions;

        //lets find default values from fields description
        $fieldsDefaults = array();
        foreach ($this->view->editInputs as $tabId => $tab) {
            foreach ($tab as $fieldName => $fieldDescription) {
                if (!isset($fieldsDefaults[$fieldName])) {
                    //will use firs suitable default value
                    $fieldsDefaults[$fieldName] = $fieldDescription['DEFAULT_VALUE'];
                } else {
                    if ($fieldsDefaults[$fieldName] == '' || $fieldsDefaults[$fieldName] == null) {
                        //but if previouse value is empty will use next
                        $fieldsDefaults[$fieldName] = $fieldDescription['DEFAULT_VALUE'];
                    }
                }
            }
        }
        
        switch ($extendedAction) {
            case 'new':
                if (isset($fieldsDefaults['PERSON_ID']) && $fieldsDefaults['PERSON_ID'] != '' && $fieldsDefaults['PERSON_ID'] !== null) {
                    //for new records use default PERSON_ID for filtering
                    $conditions['PERSON_ID'] = $fieldsDefaults['PERSON_ID'];
                } else {
                    $conditions['PERSON_ID'] = '';
                }
                break;
            case 'update_filter':
                //in pickOrder screen we filter all dropdowns by person id
                if (isset($conditions['PERSON_ID']) && $conditions['PERSON_ID'] != '' && $conditions['PERSON_ID'] !== null) {
                    //when user already selected some PERSON_ID use it
                } elseif (isset($fieldsDefaults['PERSON_ID']) && $fieldsDefaults['PERSON_ID'] != '' && $fieldsDefaults['PERSON_ID'] !== null) {
                    //if user selected nothing use default PERSON_ID
                    $conditions['PERSON_ID'] = $fieldsDefaults['PERSON_ID'];
                } else {
                    $conditions['PERSON_ID'] = '';
                }
                break;
            default:
                $this->addError("Error. Unrecognized extended action: $extendedAction.");
                $this->_setConditions($conditions);
                $this->view->pickOrders     =   array($newPickOrder);
                $this->render('ajax-get-new-pick-order');
                return;
        }
        
        //now lets build extended edit inputs
        $populatedDefaults        = array();
        foreach ($this->view->editInputs as $tabKey => &$tab){
            foreach ($tab as $fieldName => &$fieldDescription) {
                if ($fieldDescription['SSV_INPUT_METHOD'] == 'DD') {
                    $fieldDescription  = $this->minder->getDropDownExInfo($fieldDescription, $conditions, true);
                    if (is_array($fieldDescription['MAPPED_DEFAULTS']))
                        $populatedDefaults = array_merge($populatedDefaults, $fieldDescription['MAPPED_DEFAULTS']);
                }
            }
        }
        
        //overrides default values from fields description
        //with values wich comes from populating with mapping
        foreach ($this->view->editInputs as $tabId => &$tab) {
            foreach ($tab as $fieldName => &$fieldDescription) {
                if ($fieldDescription['SSV_INPUT_METHOD'] != 'RO') {
                    if (isset($populatedDefaults[$fieldName])) {
                        $fieldDescription['DEFAULT_VALUE'] = $populatedDefaults[$fieldName];
                    }
                }

                if (isset($conditions[$fieldName])) {
                    //restore filter value
                    $fieldDescription['DEFAULT_VALUE'] = $conditions[$fieldName];
                }
            }
        }

        $enteredValues = $this->getRequest()->getParams();
        foreach ($this->view->editInputs as $tab) {
            foreach ($tab as $fieldDescription) {
                if (!empty($fieldDescription['DEFAULT_VALUE']))
                    $enteredValues[$fieldDescription['SSV_NAME']] = $fieldDescription['DEFAULT_VALUE'];
            }
        }

        foreach ($this->view->editInputs as &$tab) {
            $tab = $this->minder->fillEditInputsDefaults($tab, $enteredValues);
            foreach ($tab as $fieldName => &$fieldDescription) {
                if (isset($conditions[$fieldName])) {
                    //restore filter value
                    $fieldDescription['DEFAULT_VALUE'] = $conditions[$fieldName];
                }
            }
        }

        $this->_setConditions($conditions);
        $this->view->pickOrders     =   array($newPickOrder);
        
        $this->render('ajax-get-new-pick-order');
        
    }

    public function clearOrderSelectionAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_clearOrderSelection();

        echo "{'status': 'ok'}";
    }

    protected function _clearOrderSelection() {
        $this->_setConditions(array(), 'lines');
        /**
         * @var Minder_Controller_Action_Helper_LegacyRowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('LegacyRowSelector');
        $rowSelector->clearSelection($this->_getPickOrderSelectionNamespace());
    }
    
    public function ajaxSaveNewOrderAction(){
        
        $dataToSaveList                 =   json_decode($this->getRequest()->getParam('data_to_save'));
        
        $newPickOrder                   =   $this->minder->newPickOrder();
        $newPickOrder->pickOrderType    =   'SO';

        $this->_clearOrderSelection();

        foreach($dataToSaveList as $obj){
            
            $propertyName       =   transformToObjectProp($obj->input_name);       
            $propertyValue      =   $obj->input_value;
            
            $newPickOrder->$propertyName    =   $propertyValue;    
        }

        
        if (!$this->minder->pickOrderCreate($newPickOrder)) {
            $message[]  =   'Error occured: ' . $this->minder->lastError;
            $result     =   false;
        } else {
            $message[]  =   'Order ' . $newPickOrder->pickOrder . ' was added.';
            $result     =   true;
        }
        
        $this->session->newPickOrder    =   $newPickOrder->pickOrder;   
        
        $this->view->json   =   array('result'  =>  $result,
                                      'message' =>  $message);
                                      
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
                                        'SSN_ID'            => 'ISSN',
                                        'PROD_ID'           => 'Product ID',
                                        'ISSN_DESCRIPTION'  => 'SSN Description',
                                        'CURRENT_QTY'       => 'Qty',
                                        'COMPANY_ID'        => 'Company ID',
                                        'CREATE_DATE'       => 'Created Date'
                                    );
        $this->view->data = $data;
        $this->_processReportTo(strtoupper($this->getRequest()->getParam('report')));

    }
    
    public function addFromLocationMarkAction()    {
        
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
            
            $transaction->whId         = $whId; 
            $transaction->locnId    = $locnId; 
            $transaction->objectId  = $id;
            $transaction->subLocnId = $pickOrder;
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
    
    public function addFromLocationAction()    {
        
        $conditions   = $this->_getSelectedPickOrders();
        
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
                                        'SSN_ID'            => 'ISSN',
                                        'PROD_ID'           => 'Product ID',
                                        'ISSN_DESCRIPTION'  => 'SSN Description',
                                        'CURRENT_QTY'       => 'Qty',
                                        'COMPANY_ID'        => 'Company ID',
                                        'CREATE_DATE'       => 'Created Date',
                                        ''                  =>'Processing Result'
                                    );
        
        $order = $this->minder->getPickOrder($id, 'SO');
        
        if ($order->despatchLocation==null && $order->despatchLocation=="") {
            $this->view->ds_null = 1;
            return;    
        }

        $despatchLocation = substr($order->despatchLocation,2);
        $this->session->params[$this->_controller]['addFromLocation']['despatch_location'] = $order->despatchLocation; 
        $whId             = $order->whId;
        
        $clause= array();
        
        $clause['PICK_ORDER IS NULL']        =    '';

        if ($whId!=null) {
            $clause['WH_ID = ?']        =    $whId;
        }
        
        
        if ($despatchLocation!=null) {
            $clause['LOCN_ID = ?']        =    $despatchLocation;
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
    
    /**
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _getRowSelector() {
        return $this->_helper->getHelper('LegacyRowSelector');
    }

    protected function _getPickOrderSelectionNamespace() {
        return 'PICK_ORDER';
    }

    protected function _getSelectedPickOrdersAmount() {
        return count($this->_getSelectedPickOrders());
    }

    protected function _getPickOrderSelectionMode() {
        return $this->_getRowSelector()->getSelectionMode($this->_getPickOrderSelectionNamespace());
    }

    protected function _setPickOrderSelectionMode($selectionMode) {
        $this->_getRowSelector()->setSelectionMode($selectionMode, $this->_getPickOrderSelectionNamespace());
        return $this;
    }

    protected function _buildPickOrderSearchClause() {
        list($searchInputs, $dropDownList1, $dropDownList2, $dropDownListNames, $allowed) = $this->minder->getSearchInputs('PICKORDER');

        $conditions = $this->_getConditions('index');
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge(array('PICK_ORDER_TYPE = ? AND ' => 'SO'), $clause);

        if($this->session->mode != null) {
            $clause = array_merge($clause, $this->session->pickModeCriteria);
        }

        return $clause;
    }

    /**
     * @return array
     */
    protected function _getPickOrderSearchClause() {
        if (is_null($this->_pickOrderSearchClause))
            $this->_pickOrderSearchClause = $this->_buildPickOrderSearchClause();

        return $this->_pickOrderSearchClause;
    }

    protected function _fetchSelectedPickOrders() {
        $selectedPickOrders = $this->_getRowSelector()->getSelectedRowIds($this->_getPickOrderSelectionNamespace());

        if (count($selectedPickOrders) < 1)
            return array();

        $clause = array_merge($this->_getPickOrderSearchClause(), array("PICK_ORDER IN ('" . implode("', '", $selectedPickOrders) . "') AND " => ''));

        return $this->minder->getPickOrderIds($clause);
    }

    protected function _getSelectedPickOrders()
    {
        if (is_null($this->_selectedPickOrders))
            $this->_selectedPickOrders = $this->_fetchSelectedPickOrders();

        return $this->_selectedPickOrders;
    }

    protected function _selectPickOrders($pickOrders, $method, $pickOrderLines) {
        $this->_selectedPickOrders = null;
        $this->_getRowSelector()->selectRows($pickOrders, $method, $pickOrderLines, $this->_getPickOrderSelectionNamespace());
        return $this;
    }

    protected function _selectPickOrder($pickOrder) {
        return $this->_selectPickOrders($pickOrder, 'true', array(array($pickOrder => array())));
    }

    protected function _unselectPickOrder($pickOrder) {
        return $this->_selectPickOrders($pickOrder, 'false', array());
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
                        $this->view->response['data']['url'] =  $this->view->url(array('controller' => 'pick-order2', 'action' => ''), null, true);
                    }
                }
            }
            $soap->unlockSoapTransaction();
        }

        $this->render('get-order');
        return;

    }
    
    public function addressAction() {
        
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
                            'contact'    => substr($result['firstName'].' '.$result['lastName'], 0, 50)
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
                            'contact'    => substr($result['firstName'].' '.$result['lastName'], 0, 50)
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
                    'contact'    => substr($person['firstName'].' '.$person['lastName'], 0, 50)
                );
            }
            $this->_helper->json($json);
        }
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
       
        $dueDate      = (isset($_POST['due_date'])   ? $_POST['due_date'] :     '');
        $productId    = (isset($_POST['product_id']) ? $_POST['product_id'] :   '');
        
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
    
    public function shippingServiceAction()    {
        
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
                    $this->_redirect('pick-order2');
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
                                $this->addError('Error occured during save line. '.$e->getMessage());
                                $result = false;
                            }
                        } else {
                            $this->addError('Error occured during save line. '.$this->minder->lastError);
                            $result = false;
                        }
                        if ($result) {
                            $this->_redirect('pick-order2');
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
   
    /**
     * @deprecated remove when possible
     * @return void
     */
    public function addIssnItemsAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $case = $this->session->params['pick-order2']['index']['pick_items']['case'];

        unset($this->session->notAddedQty);

        if($case == 1){
            if (isset($this->session->params['pick-order2']['index']['pick_items'])) {
                try {
                    $result = $this->minder->addPickItem($this->session->params['pick-order2']['index']['pick_items']);
                    if (isset($this->session->params['pick-order2']['index']['pick_items']['pick_order'])) {
                        if ($this->add) {
                            $this->addMessage('Order '
                            . $this->session->params['pick-order2']['index']['pick_items']['pick_order']
                            . ' was added'
                            . (isset($this->session->params['pick-order2']['index']['pick_items']) && count($this->session->params['pick-order2']['index']['pick_items']) > 1 ? ' (with lines).' : '.'));
                        } else {
                            $this->addMessage('Lines were added in order '
                            . $this->session->params['pick-order2']['index']['pick_items']['pick_order'] . '.');
                        }
                    }
                } catch (Exception $e) {
                    $this->addError('Error occured during add item: ' . $this->minder->lastError);
                }
            }
        } else {
            if (isset($this->session->params['pick-order2']['index']['pick_items'])) {
                try {
                    $result = $this->minder->addPickItem($this->session->params['pick-order2']['index']['pick_items']);
                    if (isset($this->session->params['pick-order2']['index']['pick_items']['pick_order'])) {
                        if ($this->add) {
                            $this->addMessage('Order '
                            . $this->session->params['pick-order2']['index']['pick_items']['pick_order']
                            . ' was added'
                            . (isset($this->session->params['pick-order2']['index']['pick_items']) && count($this->session->params['pick-order2']['index']['pick_items']) > 1 ? ' (with lines).' : '.'));
                        } else {
                            $this->addMessage('Lines were added in order '
                            . $this->session->params['pick-order2']['index']['pick_items']['pick_order'] . '.');
                        }
                    }
                } catch (Exception $e) {
                    $this->addError('Error occured during add item: ' . $this->minder->lastError);
                }
            }
        }

        if (isset($this->session->params['pick-order2']['index']['pick_items']['not_added_qty']))
            $this->session->notAddedQty = $this->session->params['pick-order2']['index']['pick_items']['not_added_qty'];

        unset($this->session->params['pick-order2']['index']['pick_items']);

        if ($redirect = $this->_getParam('redirect')) {
            $this->_redirect($redirect);
        }
    }

    /**
     * @deprecated use printInvoiceReportAction()
     * @throws Exception
     * @return
     */
    public function printInvoiceAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();
        $response->savedOrders = array();

        try {
            $selectedOrders = $this->_getSelectedPickOrders();

            if (count($selectedOrders) < 1) {
                $response->warnings[] = 'No Orders selected. Select one.';
                echo json_encode($response);
                return;
            }

            /**
             * @var Minder_Printer_Abstract $printer
             */
            $printer       = $this->minder->getPrinter();

            foreach ($selectedOrders as $pickOrderId) {
                try {
                    /**
                     * @var PickOrder $pickOrderObject
                     */
                    if (false === ($pickOrderObject = $this->minder->getPickOrder($pickOrderId)))
                        throw new Exception('Sales Order #' . $pickOrderId . ' not found.');

                    $invoiceReport            = Minder_Report_Factory::makeInvoiceReportForCompany($pickOrderObject->companyId, 'TI');
                    $invoiceReport->pickOrder = $pickOrderId;
                    $pdfImage = $invoiceReport->getPdfImage();
                    $printer->printPdfImage($pdfImage);
                    $response->messages[] = 'Sales Order #' . $pickOrderId . ': Invoice print request sent.';
                } catch (Exception $e) {
                    $response->errors[] = 'Error printing Invoice for Sales Order #' . $pickOrderId . ': ' . $e->getMessage();
                    continue;
                }

                try {
                    /**
                     * @var Company $companyObject
                     */
                    $companyObject = $this->minder->getCompany($pickOrderObject->companyId);
                    if (is_null($companyObject))
                        throw new Exception('Company #' . $pickOrderObject->companyId . ' not found.');

                    $tmpDateStr = (empty($pickOrderObject->approvedDespDate)) ? $pickOrderObject->createDate : $pickOrderObject->approvedDespDate;

                    if (false === ($tmpTimestamp = strtotime($tmpDateStr)))
                        throw new Exception('Bad Date provided "' . $tmpDateStr . '"');

                    $dateArray = getdate($tmpTimestamp);
                    $tmpMonth  = (strlen($dateArray['mon']) < 2) ? '0' . $dateArray['mon'] : $dateArray['mon'];
                    $uniqNo    = $companyObject->saveInvoiceImage($pickOrderId, $dateArray['year'], $tmpMonth, $pdfImage);

                    $response->savedOrders[] = array(
                        'pickOrder' => $pickOrderId,
                        'uniqNo' => $uniqNo,
                        'year' => $dateArray['year'],
                        'month' => $tmpMonth
                    );

                } catch (Exception $e) {
                    $response->warnings[] = 'Cannot save Invoice PDF for Sales Order #' . $pickOrderId . ': ' . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }



        echo json_encode($response);
    }

    public function printInvoiceReportAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();
        $response->savedOrders = array();

        $reportType  = $this->getRequest()->getParam('reportType');
        $paramsMap = $this->getRequest()->getParam('paramsMap', array());
        $displayReports = $this->getRequest()->getParam('displayReports', false);

        if (empty($reportType)) {
            $response->errors[] = 'No Report Type.';
            echo json_encode($response);
            return;
        }

        try {
            $selectedOrders = $this->_getSelectedPickOrders();

            if (count($selectedOrders) < 1) {
                $response->warnings[] = 'No Orders selected. Select one.';
                echo json_encode($response);
                return;
            }

            /**
             * @var Minder_Printer_Abstract $printer
             */
            $printer       = $this->minder->getPrinter();

            foreach ($selectedOrders as $pickOrderId) {
                try {
                    /**
                     * @var PickOrder $pickOrderObject
                     */
                    if (false === ($pickOrderObject = $this->minder->getPickOrder($pickOrderId)))
                        throw new Exception('Sales Order #' . $pickOrderId . ' not found.');

                    $invoiceReport            = Minder_Report_Factory::makeInvoiceReportForCompany($pickOrderObject->companyId, $reportType);
                    $paramsMap                = $invoiceReport->fillStaticParams($paramsMap);

                    $sql = 'SELECT ' . implode(', ', array_keys($paramsMap)) . ' FROM PICK_ORDER WHERE PICK_ORDER = ? ' . substr($this->minder->getWarehouseAndCompanyLimit(0, false, 'PICK_ORDER.', 'PICK_ORDER.'), 0, -5);
                    if (false === ($result = $this->minder->fetchAllAssocExt($sql, $pickOrderId)))
                        throw new Exception($this->minder->lastError);

                    if (count($result) < 1)
                        throw new Exception('Sales Order #' . $pickOrderId . ' not found.');

                    $orderData = array_shift($result);

                    foreach ($paramsMap as $orderField => $reportField) {
                        $orderField = strtoupper($orderField);
                        $invoiceReport->setQueryFieldValue($reportField, (isset($orderData[$orderField]) ? $orderData[$orderField] : ''));
                    }

                    $pdfImage = $invoiceReport->getPdfImage();
                    $printer->printPdfImage($pdfImage);
                    $response->messages[] = 'Sales Order #' . $pickOrderId . ': Invoice print request sent.';
                } catch (Exception $e) {
                    $response->errors[] = 'Error printing Invoice for Sales Order #' . $pickOrderId . ': ' . $e->getMessage();
                    continue;
                }

                try {
                    /**
                     * @var Company $companyObject
                     */
                    $companyObject = $this->minder->getCompany($pickOrderObject->companyId);
                    if (is_null($companyObject))
                        throw new Exception('Company #' . $pickOrderObject->companyId . ' not found.');

                    $tmpDateStr = (empty($pickOrderObject->approvedDespDate)) ? $pickOrderObject->createDate : $pickOrderObject->approvedDespDate;

                    if (false === ($tmpTimestamp = strtotime($tmpDateStr)))
                        throw new Exception('Bad Date provided "' . $tmpDateStr . '"');

                    $dateArray = getdate($tmpTimestamp);
                    $tmpMonth  = (strlen($dateArray['mon']) < 2) ? '0' . $dateArray['mon'] : $dateArray['mon'];
                    $uniqNo    = $companyObject->saveInvoiceImage($pickOrderId, $dateArray['year'], $tmpMonth, $pdfImage);

                    if ($displayReports) {
                        $response->savedOrders[] = array(
                            'pickOrder' => $pickOrderId,
                            'uniqNo' => $uniqNo,
                            'year' => $dateArray['year'],
                            'month' => $tmpMonth
                        );
                    }

                } catch (Exception $e) {
                    $response->warnings[] = 'Cannot save Invoice PDF for Sales Order #' . $pickOrderId . ': ' . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        echo json_encode($response);
    }

    public function showInvoiceAction() {
        $pickOrder = $this->getRequest()->getParam('pickOrder');
        $uniqNo    = $this->getRequest()->getParam('uniqNo');
        $year      = $this->getRequest()->getParam('year');
        $month     = $this->getRequest()->getParam('month');

        /**
         * @var PickOrder $pickOrderObject 
         */
        if (false === ($pickOrderObject = $this->minder->getPickOrder($pickOrder))) {
            $this->addError('Pick Order #' . $pickOrder . ' not found.');
            return $this->_redirector->gotoSimple('index');
        }

        $companyList = $this->minder->getCompanyListLimited();
        if (!isset($companyList[$pickOrderObject->companyId])) {
            $this->addError('Access denied.');
            return $this->_redirector->gotoSimple('index');
        }

        /**
         * @var Company $companyObject
         */
        $companyObject = $this->minder->getCompany($pickOrderObject->companyId);
        if (is_null($companyObject)) {
            $this->addError('Company #' . $pickOrderObject->companyId . ' not found.');
            return $this->_redirector->gotoSimple('index');
        }

        try {
            $pdfImage = $companyObject->loadInvoiceImage($pickOrder, $uniqNo, $year, $month);
        } catch (Exception $e) {
            $this->addError('Cannot get Invoice PDF: ' . $e->getMessage());
            return $this->_redirector->gotoSimple('index');
        }

        $this->_helper->viewRenderer->setNoRender(true);
        $this->getResponse()->setHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $companyObject->formatInvoiceFileName($pickOrder, '') . '"');
        echo $pdfImage;
    }

    protected function _getFirstSelectedOrder() {
        $selectedOrders = $this->_getSelectedPickOrders();

        if (count($selectedOrders) > 0)
            return current($selectedOrders);

        return null;
    }

    protected function _isSearchRequest($productName, $nameType) {
        if ($nameType == 'product_description') return true;
        if (strstr($productName, '%') !== false ) return true;

        return false;
    }

    protected function _prepareEnvForItemSearch($qty, $pickOrder, $name, $thisIs, $price, $providerName) {
        $productSearchOptions               =   new stdClass();
        $productSearchOptions->makeSearch   =   false;
        $productSearchOptions->qty          =   $qty;
        $productSearchOptions->from         =   'pick-order';
        $productSearchOptions->pickOrder    =   $pickOrder;
        $productSearchOptions->defaultPrice =   $price;
        $productSearchOptions->thisIs       =   $thisIs;

        if ($providerName == Minder_AddPickItemRoutine_ItemProvider_ProdProfile::PROVIDER_NAME) {
            $productSearchOptions->productName          = $name;
            $this->session->params['product_options']   = $productSearchOptions;
            $this->session->params['product_price']     = $price;
        }

        if ($providerName == Minder_AddPickItemRoutine_ItemProvider_Issn::PROVIDER_NAME) {
            $productSearchOptions->nonProductName           = $name;
            $this->session->params['non_product_options']   = $productSearchOptions;
            $this->session->params['non_product_price']     = $price;

        }

    }

    protected function _getPickItemProviderObject($providerName) {
        switch ($providerName) {
            case Minder_AddPickItemRoutine_ItemProvider_Issn::PROVIDER_NAME:
                return new Minder_AddPickItemRoutine_ItemProvider_Issn();

            case Minder_AddPickItemRoutine_ItemProvider_ProdProfile::PROVIDER_NAME:
                return new Minder_AddPickItemRoutine_ItemProvider_ProdProfile();

            default:
                throw new Minder_Exception('Bad PICK_ITEM provider specified "' . $providerName . '".');
        }
    }

    protected function _getSearchLocationForProvider($providerName) {
        switch ($providerName) {
            case Minder_AddPickItemRoutine_ItemProvider_Issn::PROVIDER_NAME:
                return $this->view->url(array('action' => 'search-non-product', 'controller' => 'ssn2', 'module' => 'warehouse'));

            case Minder_AddPickItemRoutine_ItemProvider_ProdProfile::PROVIDER_NAME:
                return $this->view->url(array('action' => 'init', 'controller' => 'product-search', 'module' => 'warehouse'));

            default:
                throw new Minder_Exception('Bad PICK_ITEM provider specified "' . $providerName . '".');
        }
    }

    public function addProductAction() {
        $this->_helper->getHelper('ViewRenderer')->setNoRender(true);
        $result = new Minder_JSResponse();
        $result->confirmNoStock = false;
        $result->stockLeft      = 0;

        try {
            $selectedOrder = $this->_getFirstSelectedOrder();

            if (is_null($selectedOrder)) {
                $result->warnings[] = 'No Sales Order selected. Please select one.';
                echo json_encode($result);
                return;
            }

            $requiredQty  = intval($this->getRequest()->getParam('requiredQty'));

            if ($requiredQty < 1) {
                $result->warnings[] = 'Specify Required Qty.';
                echo json_encode($result);
                return;
            }

            $defaultPrice = floatval($this->getRequest()->getParam('defaultPrice'));

            $name     = $this->getRequest()->getParam('productName');
            $name     = empty($name) ? '%' : $name;

            $nameType = $this->getRequest()->getParam('nameType');
            $nameType = in_array($nameType, array('product_code', 'product_description', 'issn')) ? $nameType : 'product_description';

            $result->providerName = $this->getRequest()->getParam('providerName');

            if ($this->_isSearchRequest($name, $nameType)) {
                $this->_prepareEnvForItemSearch($requiredQty, $selectedOrder, $name, $nameType, $defaultPrice, $result->providerName);
                $result->location = $this->_getSearchLocationForProvider($result->providerName);

                echo json_encode($result);
                return;
            }

            $addRoutine = new Minder_AddPickItemRoutine();
            $addItemRequest = new Minder_AddPickItemRoutine_Request($selectedOrder, $name, $requiredQty, $defaultPrice);
            $addRoutineState = $addRoutine->addPickItem(
                $addItemRequest,
                $this->_getPickItemProviderObject($result->providerName)
            );

            switch ($addRoutineState->type) {
                case Minder_AddPickItemRoutine_State::STATE_ERROR:
                    $result->errors = array_merge($result->errors, $addRoutineState->errors);
                    break;
                case Minder_AddPickItemRoutine_State::STATE_NO_STOCK:
                    $result->confirmNoStock        = true;
                    $result->stockLeft             = $addRoutineState->stockAmount;
                    $result->requiredAmount        = $addItemRequest->requiredForOrder;
                    $result->prodId                = current($addItemRequest->itemIdList);
                    $this->session->addItemRequest = $addItemRequest;
                    break;
                case Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED:
                    $this->addMessage('PICK_ITEMs added to PICK_ORDER #' . $selectedOrder . '.');
                    $result->messages[] = 'PICK_ITEMs added to PICK_ORDER #' . $selectedOrder . '.';
                    $result->location = $this->view->url(array('action' => 'index'));
                    break;
                default:
                    $this->_prepareEnvForItemSearch($requiredQty, $selectedOrder, $name . '%', $nameType, $defaultPrice, $result->providerName);
                    $result->location = $this->_getSearchLocationForProvider($result->providerName);
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function confirmAddWithNoStockAction() {
        $this->_helper->getHelper('ViewRenderer')->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            if (!isset($this->session->addItemRequest)) {
                $result->errors[] = 'Bad request.';
                echo json_encode($result);
                return;
            }
            /**
             * @var Minder_AddPickItemRoutine_Request $addItemRequest
             */
            $addItemRequest          = $this->session->addItemRequest;
            $addItemRequest->addMode = $this->getRequest()->getParam('addMode', Minder_AddPickItemRoutine_Request::ADD_MODE_DEFAULT);

            unset($this->session->addItemRequest);

            $addRoutine      = new Minder_AddPickItemRoutine();
            $addRoutineState = $addRoutine->addPickItem(
                $addItemRequest,
                $this->_getPickItemProviderObject($this->getRequest()->getParam('providerName'))
            );

            switch ($addRoutineState->type) {
                case Minder_AddPickItemRoutine_State::STATE_ERROR:
                    $result->errors = array_merge($result->errors, $addRoutineState->errors);
                    break;
                case Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED:
                    $this->addMessage('PICK_ITEMs added to PICK_ORDER #' . $addItemRequest->orderNo . '.');
                    $result->messages[] = 'PICK_ITEMs added to PICK_ORDER #' . $addItemRequest->orderNo . '.';
                    $result->location = $this->view->url(array('action' => 'index'));
                    break;
                default:
                    throw new Exception('Unexpected Minder_AddPickItemRoutine_State "' . $addRoutineState->type . '"');
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function refreshTotalsAction() {
        $selectedOrders = $this->_getSelectedPickOrders();

        if (empty($selectedOrders)) {
            $this->addWarning('No orders selected.');
            $this->_redirector->gotoSimple('index');
            return;
        }

        $refreshed = 0;
        foreach ($selectedOrders as $orderNo) {
            if (false === $this->minder->pickOrderRecalculate($orderNo)) {
                $this->addError($this->minder->lastError);
            } else {
                $refreshed++;
            }
        }

        if ($refreshed > 0) {
            $this->addMessage($refreshed . ' Order(s) updated.');
        } else {
            $this->addWarning('No Order updated.');
        }

        $this->_redirector->gotoSimple('index');
        return;
    }

    /*public function _setupShortcuts(){
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('orders')->buildMinderMenuArray();

        return $this;
    }*/
}
  
?>
