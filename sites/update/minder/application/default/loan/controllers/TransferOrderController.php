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
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class TransferOrderController extends Minder_Controller_Action
{
    protected $_showBy = 5;

    public function init()
    {
        parent::init();
        if ((!$this->minder->isAdmin) && $this->minder->isInventoryOperator) {
            $this->_redirector->setCode(303)
                              ->goto('index', 'index', '', array());
            return;
        }
        
        $this->log = Zend_Registry::get('logger');
        $this->log->info(__FUNCTION__);
        
        if (!isset($this->session->params[$this->_controller]['index']['pick_orders'])) {
            $this->session->params[$this->_controller]['index']['pick_orders'] = array();
        }

        $this->session->returnOrder = $this->_controller;
    }

    /**
     * Diplay the sales order homepage (/transfer-order/index)
     *
     * This page lists the sales orders. It optionally takes input from the user
     * allowing the limit the results by searching for certain sales orders.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->action        = 'index';
        $this->view->pageTitle     = 'SEARCH TRANSFER ORDER:';
        if(empty($this->session->transferOrders)) {
            $this->session->transferOrders = array();
        }
        if(empty($this->session->tMode)) {
            $this->session->tMode  = null;
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
                    $this->_redirect('transfer-order');
                    break;

                case 'allocate orders':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('allocate-orders', 'transfer-order', '');
                    break;

                case 'allocate products':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('allocate-products', 'transfer-order', '');
                    break;

                case 'approve pick':
                    $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
                    foreach ($pickOrders as $pickOrder) {
                        if (!$this->minder->pickOrderApprovePick($pickOrder)) {
                            $this->addWarning('Order ' . $pickOrder . ' was not approved.');
                        }
                    }
                    $this->_redirect('transfer-order');
                    break;

                case 'approve despatch':
                    $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
                    foreach ($pickOrders as $pickOrder) {
                        if (!$this->minder->pickOrderApproveDespatch($pickOrder)) {
                            $this->addWarning('Order ' . $pickOrder . ' was not dispatched.');
                        }
                    }
                    $this->_redirect('transfer-order');
                    break;

                case 'hold':
                    $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
                    foreach ($pickOrders as $pickOrder) {
                        if (!$this->minder->pickOrderHold($pickOrder)) {
                            $this->addWarning('Order ' . $pickOrder . ' was not held.');
                        }
                    }
                    $this->_redirect('transfer-order');
                    break;
/*
                case 'unhold':
                    if (is_array($_POST['pick_orders'])) {
                        foreach ($_POST['pick_orders'] as $pickOrder) {
                            $this->minder->pickOrderUnHold($pickOrder);
                        }
                    }
                    break;
*/
                case 'view availability':
                    $this->session->params['draw']['items'] = array();
                    $this->session->params['draw']['items'] = $this->session->conditions[$this->_controller]['lines'];
                    $this->session->params['draw']['mode']  = 'pickItem';
               
                    $this->_helper->viewRenderer->setNoRender(true);
                    break;
                case 'report: gd':
                    
                    $this->view->data = array();
                    $this->view->data = $this->minder->getPickItemDates($this->session->conditions[$this->_controller]['lines']);   
                    $this->_processReportTo('REPORT: GD');
                    break; 
                
                case 'run_pick_mode':
                
                        $mode = $this->getRequest()->getParam('pick_proc');
                        $this->view->pick_mode = substr($mode, -2);
                        
                        if($mode != null && $mode == 'reset') {
                            unset($this->session->tMode);
                            unset($this->session->pickModeCriteria);
                            unset($this->session->transferOrders);
                            break;
                        } elseif ($mode == null) {
                            return;
                        } else {
                            $this->session->tMode = $mode;
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
                        
                        $order_types = 'TO';
                        $order_modes = array_search($order_modes, $pickModesList);
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
                        
                        $filterdIds = $this->view->minder->pickMode($mode, $order_types, $order_modes, $orders, $order_statuses, $order_prioritys, $ids);
                        
                        
                        $ordersList = '';
                        foreach($filterdIds['data'] as $value ) {
                            $ordersList .= sprintf("'%s'", $value['WK_ORDER']) . ',';
                        }
                        
                        if(!empty($ordersList)) {                        
                            $this->session->pickModeCriteria['PICK_ORDER.PICK_ORDER IN (' . substr($ordersList, 0, -1) . ') AND '] = '';
                        } else {
                            $this->session->pickModeCriteria = array();
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
                    $this->_redirect('transfer-order');
                    break;
                
                case 'add':
                    
                    $this->log->info('add case');
                  
                    $addProduct     =   $this->_request->getParam('add_product');    
                    $addNonProduct  =   $this->_request->getParam('add_non_product');
                 
                    // if clicked on ADD Product button
                    if($addProduct){
                        $this->log->info('addproduct :' . $addProduct);
                    
                        $qty    = $this->_request->getParam('product_qty');
                        $what   = $this->_request->getParam('product_options');
                        $name   = $this->_request->getParam('product_name');
             
                        $this->log->info('qty :' . $qty);
                        $this->log->info('what :' . $what);
                        $this->log->info('name :' . $name);
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
                        
                        $this->log->info('mode ' . $mode );
                        
                        
                         reset($this->session->params[$this->_controller]['index']['pick_orders']);
                        $pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
                        
                        $pickOrderData  =   $this->minder->getPickOrder($pickOrder, 'TO');
                        
                        switch($mode){
                            case 1:
                                
                                $stdClass               =   new stdClass();
                                $stdClass->makeSearch   =   false;
                                $stdClass->qty          =   $qty;
                                $stdClass->from         =   'pick-order';
                                $stdClass->pickOrder    =   $pickOrder;
                                $stdClass->productName  =   $name; 
                                $stdClass->whId         =   $pickOrderData->whId; 
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
                                $stdClass->whId         =   $pickOrderData->whId; 
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
                                
                                $this->log->info('in case 3 '  );
                               
                                $clause  =   array('PROD_ID LIKE ? AND ' => $name);   
                                $product =   current($this->minder->getProductLine1s($clause));
                                
                                if(!empty($product)){
                                    $productCompanyId   =   $product->items['COMPANY_ID'];
                                  
                                    if(!is_null($productCompanyId)){
                                    
                                        if($productCompanyId == $pickOrderData->companyId){
                                       
                                            if($this->minder->defaultControlValues['CONFIRM_WITH_NO_PROD'] == 'T') {
                                            
                                                $this->session->params['pick-order']['index']['pick_items'][0]              =   $product->items;
                                                $this->session->params['pick-order']['index']['pick_items']['required_qty'] =   $qty;
                                                $this->session->params['pick-order']['index']['pick_items']['case']         =   1;
                                                $this->session->params['pick-order']['index']['pick_items']['pick_order']   =   $pickOrder;
                                                $this->_setParam('redirect', 'transfer-order');   
                                         
                                                $this->addIssnItemsAction();    
                                                    
                                            } else {
                                                
                                                $tmpAvailableQty = 0;
                                                
                                                try {
                                                    $product         = $this->minder->checkAvailableProduct($name, $pickOrderData->companyId, $pickOrderData->whId);    
                                                    $tmpAvailableQty = $product['AVAILABLE_QTY'];
                                                } catch (Exception $e) {
                                                    $this->addError('Cannot check available product quantity: ' . $e->getMessage());
                                                    break;
                                                }
                                                
                                                if($tmpAvailableQty >= $qty){
                                                    
                                                    $this->session->params['pick-order']['index']['pick_items'][0]              =   $product->items;
                                                    $this->session->params['pick-order']['index']['pick_items']['required_qty'] =   $qty;
                                                    $this->session->params['pick-order']['index']['pick_items']['case']         =   1;
                                                    $this->session->params['pick-order']['index']['pick_items']['pick_order']   =   $pickOrder;
                                                    $this->_setParam('redirect', 'transfer-order');   
                                             
                                                    $this->addIssnItemsAction();
                                                        
                                                } else {
                                                    $this->addError("Can't Add Product - insufficient Available Quantity. (Available = " . $product['AVAILABLE_QTY'] . "): " . $name);        
                                                }
                                            }
                                        } else {
                                            $this->addError("Product COMPANY_ID do not mutch order COMPANY_ID. Product COMPANY_ID: " . $productCompanyId . ' Order COMPANY_ID: ' . $pickOrderData->companyId);    
                                        }    
                                    } else {
                                        $this->addError("Product COMPANY_ID is null");    
                                    }       
                                } else {
                                    $this->addError("Product Code is not listed in PRODUCT PROFILE: " . $name);    
                                }
                                
                                break;
                                
                            default:
                        }
                   
                    // if clicked on ADD Non-Product button     
                    } elseif($addNonProduct){
                       
                        $this->log->info('addNonproduct :' . $addNonProduct);
                        
                        $qty    = $this->_request->getParam('non_product_qty');
                        $what   = $this->_request->getParam('non_product_options');
                        $name   = $this->_request->getParam('non_product_name');
       
                        $this->log->info('qty :' . $qty);
                        $this->log->info('what :' . $what);
                        $this->log->info('name :' . $name);
                        // example 1 in specs
                        if(empty($name) && !empty($qty) && $what == 'ssn'){
                            $mode = 1;
                        }
                        // example 1 in specs
                        if(empty($name) && !empty($qty) && $what == 'description'){
                            $mode = 1;
                        }
                        // example 2 in specs
                        if(!empty($name) && !empty($qty) && $what == 'ssn'){
                            $mode = 3;    
                        }
                        // example 2 in specs
                        if(!empty($name) && !empty($qty) && $what == 'ssn' && eregi('%', $name)){
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
                        
                        if(!empty($name) && $what == 'issn'){
                            $mode = 4;
                        }
                        
                        $this->log->info('mode ' . $mode );
                        
                
                        reset($this->session->params[$this->_controller]['index']['pick_orders']);
                        $pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
                
                        $pickOrderData  =   $this->minder->getPickOrder($pickOrder, 'TO');
                                
                
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
                                $nonProduct =   current($this->minder->getSsns($clause));
                                
                                if(!empty($nonProduct)){
                                    $this->session->params['pick-order']['index']['pick_items'][0]              =   $nonProduct->items;
                                    $this->session->params['pick-order']['index']['pick_items']['pick_order']   =   $pickOrder;
                                    $this->_setParam('redirect', 'transfer-order');   
                             
                                    $this->addIssnItemsAction();
                                    
                                    $result = $this->session->params['add_issn_item_result'];        
                                } else {
                                    $this->addError("Non-Product Code is not listed in SSN: " . $name);    
                                }
                                
                                
                                
                                break;
                             
                            case 4:
                            
                                $clause         =   array('SSN_ID = ?' => $name);
                                $nonProduct     =   current($this->minder->getIssns($clause));
                                
                                if(!empty($nonProduct)){
                                    
                                    $productCompanyId   =   $nonProduct['COMPANY_ID'];
                                    if(!empty($productCompanyId)){
                                        
                                        if($productCompanyId == $pickOrderData->companyId){
                                            
                                            $this->session->params['pick-order']['index']['pick_items'][0]              =   $nonProduct->items;
                                            $this->session->params['pick-order']['index']['pick_items']['required_qty'] =   $qty;
                                            $this->session->params['pick-order']['index']['pick_items']['case']         =   3;
                                            $this->session->params['pick-order']['index']['pick_items']['pick_order']   =   $pickOrder;
                                            $this->_setParam('redirect', 'transfer-order');   
                                     
                                            $this->addIssnItemsAction();
                                                
                                        } else {
                                            $this->addError('Non Product COMPANY_ID do not mutch order COMPANY_ID. Non Product COMPANY_ID: ' . $productCompanyId . ' Order COMPANY_ID: ' . $pickOrderData->companyId);
                                        }    
                                    } else {
                                        $this->addError("Non Product COMPANY_ID is null");
                                    }
                                        
                                } else {
                                    $this->addError("Non-Product Code is not listed in ISSN: " . $name);    
                                }
                            
                                break; 
                            default:
                        }
                    }
                        
                    break;

            }
        }

        // @TODO: move to linesAction().
        if (($from = $this->getRequest()->getPost('from')) && $from == 'lines-action') {
            $this->_action = 'lines';
            $this->_preProcessNavigation();
            $this->_action = 'index';
        } else {
            $this->_preProcessNavigation();
        }
         $session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;
 
        $this->view->pickStatusDescription = $this->minder->getPickOrderStatusList();
        $this->view->statusList            = minder_array_merge(array('' => ''), $this->minder->getPickOrderStatusList());
        $this->view->shipViaList           = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $this->view->despatchLocationList  = minder_array_merge(array('' => ''), $this->minder->getDespatchLocationList());
        

        $this->view->pickOrder          = isset($_POST['pick_order'])           ? $_POST['pick_order'] : '';
        $this->view->dPersonId          = isset($_POST['person_id'])            ? $_POST['person_id'] : '';
        $this->view->customerPoWo       = isset($_POST['customer_po_wo'])       ? $_POST['customer_po_wo'] : '';
        $this->view->pickFromDueDate    = isset($_POST['pick_from_due_date'])   ? $_POST['pick_from_due_date'] : '';

        $this->view->pickToDueDate      = isset($_POST['pick_to_due_date'])     ? $_POST['pick_to_due_date'] : '';
        $this->view->returnFromDate     = isset($_POST['return_from_date'])     ? $_POST['return_from_date'] : '';
        $this->view->returnToDate       = isset($_POST['return_to_date'])       ? $_POST['return_to_date'] : '';
 
       //conversion
        if($this->minder->isNewDateCalculation() == false){
            foreach($_POST as $key=>$val){ 
                if (DateTime::createFromFormat('Y-m-d H:i:s', $val) !== FALSE  || DateTime::createFromFormat('Y-m-d', $val) !== FALSE) {
                    $datetimet = $val;
                    $tz_tot = 'UTC';
                    $format = 'Y-m-d h:i:s';
                    $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                    $dtt->setTimeZone(new DateTimeZone($tz_tot));        
                    $_POST[$key]=$dtt->format($format);     
                }          
            }
        }

        //conversion
        $this->view->headers = array();
        
        $fieldsData = $this->minder->getUserHeaders('SCN_TORDER');
        $this->view->headers    =   $fieldsData['headers'];
        
        
        $allowed = array('PICK_ORDER'          => 'PICK_ORDER.PICK_ORDER LIKE ? AND ',
                         'PERSON_ID'           => 'PICK_ORDER.PERSON_ID LIKE ?  AND ',
                         'CUSTOMER_PO_WO'      => 'PICK_ORDER.CUSTOMER_PO_WO LIKE ? AND ',
                         'PICK_FROM_DUE_DATE'  => 'PICK_ORDER.PICK_DUE_DATE >= ? AND ',
                         'PICK_TO_DUE_DATE'    => 'PICK_ORDER.PICK_DUE_DATE <= ? AND ',
                         'RETURN_FROM_DATE'    => 'PICK_ORDER.RETURN_DATE >=  ? AND ',
                         'RETURN_TO_DATE'      => 'PICK_ORDER.RETURN_DATE <= ? AND '
                         );
        
        $conditions = $this->_makeConditions($allowed);
        $criteria   = $this->_makeClause($conditions, $allowed);
        $criteria   = array_merge($criteria, array('PICK_ORDER.PICK_ORDER_TYPE = ? AND ' => 'TO'));
        
        if($this->session->tMode != null) {
                $criteria = array_merge($criteria, $this->session->pickModeCriteria);
                $this->view->pick_mode = substr($this->session->tMode, -2);
        }
           
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];  
        

        $this->view->pickOrders = array();
        try {
            $pickOrders = $this->view->minder->getPickOrders($criteria, $pageSelector, $showBy, $fieldsData);
            $this->view->pickOrders     = $pickOrders['data'];
            $this->session->transferOrders  = $pickOrders['all'];
        } catch (Exception $e) {
            $this->session->conditions[$this->_controller][$this->_action] = $this->view->conditions = array();
            $this->addError('Error occured while searching.');
        }

        $pickModesList = array();
        $pickModes     = $this->minder->getPickModes();
        foreach($pickModes['data'] as $pickMode) {
            $pickModesList[$pickMode['PICK_MODE_NO']] = $pickMode['DESCRIPTION'];
        }
        $this->view->listPickModes = array_merge(array(' '=>' '), $pickModesList);
        $this->view->pickModes = $pickModes['data'];
        
        $this->_postProcessNavigation($pickOrders);
        
        if(!empty($this->session->mode)) { 
                $this->session->pickModeName = substr($pickModesList[substr($this->session->mode, -2)], 2, strlen($pickModesList[substr($this->session->mode, -2)]));
        }

        $this->view->conditions    = array_merge($this->_getConditions(), $this->_getConditions('lines'));    
        $this->view->cancelReasons = array_merge(array('' => ''), $this->minder->getOptionsList('CAN_ORDER'));
    }
    
    
    /**
     * Allows editing of an order (/transfer-order/edit)
     *
     * Displays an order for editing or saves the changes submitted
     *
     * @return void
     */
    public function editAction()
    {
        $this->view->action = 'edit';
        $pickOrder = 'ERROR';
        $request = $this->getRequest();
        $params = $request->getParams();
        if (isset($params['pick_order'])) {
            $pickOrder = $params['pick_order'];
        }

        if (!empty($_POST['action'])) {
            switch(strtolower($_POST['action'])) {
                case 'cancel':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                         ->goto('index', 'transfer-order');
                    break;

                case 'save':
                    $pickOrder = $this->view->minder->getPickOrder($params['pick_order'], 'TO');
                    if ($pickOrder->save($_POST)) {
                        // Check if user has rights for change Status field. If does not - default UC (UnConfirmed).
                        if (isset($_POST['pick_status'])) {
                            if ($_POST['pick_status'] != 'UC' && !$this->minder->isAdmin && !$this->minder->isCreditManagerT()) {
                                $_POST['pick_status'] = 'UC';
                            }
                        }
                        $pickOrder->imported = 'N';
                        $pickOrder->importErrors = 0;

                        $pickOrder->sSameAsSoldFrom = !empty($_POST['s_same_as_sold_from']) ? 'T' : 'F';
                        $pickOrder->pSameAsInvoiceTo = !empty($_POST['p_same_as_invoice_to']) ? 'T' : 'F';
                        $pickOrder->supplierList = !empty($_POST['supplier_list']) ? 'T' : 'F';
                        $pickOrder->invWithGoods = !empty($_POST['inv_with_goods']) ? 'T' : 'F';
                        $pickOrder->partialPickAllowed = !empty($_POST['partial_pick_allowed']) ? 'T' : 'F';
                        $pickOrder->overSized = !empty($_POST['over_sized']) ? 'T' : 'F';

                        if (!$this->minder->pickOrderUpdate($pickOrder)) {
                            $this->addError('Error occured.');
                        } else {
                            $this->addMessage('Order ' . $pickOrder->pickOrder . ' was updated.');
                        }
                        $this->_redirector = $this->_helper->getHelper('Redirector');
                        $this->_redirector->setCode(303)
                                          ->goto('index', 'transfer-order');
                    }
                    break;
            }
        } else {
                    $this->view->pageTitle = 'Transfer Order: ' . $pickOrder;
                    $this->view->pickOrder = $this->view->minder->getPickOrder($pickOrder, 'TO');
                    $this->view->allowedStatus = $this->_allowOrderStatus();

        }

        $this->view->loanPeriodList = $this->minder->getLoanPeriodList();
        $this->view->shipViaList    = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $notes = $this->minder->getPickOrderNotesList();
        $this->view->notes = array();
        foreach ($notes as $k => $v) {
            $this->view->notes[$k] = explode('|', $v);
        }
    }

    /**
     * Create a new sales order (/transfer-order/new)
     *
     * Display the form for creating a new sales order. If the user is submitting a previously
     * filled out form then create the order.
     *
     * @return void
     */
    public function newAction()
    {


        $this->view->action = 'new';
        $request = $this->getRequest();
        $params = $request->getParams();

        if (!isset($params['pick_order_type']) || $params['pick_order_type'] != 'TO') {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('index', 'transfer-order');
        }
        $pickOrderType = $params['pick_order_type'];

        if (!empty($_POST['action'])) {

        $session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;
       // $tz_from='Pacific/Auckland';

            $pickOrder = null;
            switch (strtolower($_POST['action'])) {
                case 'cancel':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto('index', 'transfer-order');
                    break;

                case 'save':


               $pick_due_date=$_POST['pick_due_date'];
               $pick_return_date=$_POST['return_date']; 


                if($this->minder->isNewDateCalculation() == false){
                    $datetime = $pick_due_date;
                    $tz_to = 'UTC';
                    $format = 'Y-m-d h:i:s';

                    $dt = new DateTime($datetime, new DateTimeZone($tz_from));
                    //print_r($dt); exit();
                    $dt->setTimeZone(new DateTimeZone($tz_to));
                    $utc=$dt->format($format) ;
                    // echo "local".$pick_due_date;
                    // echo "local to utc: " .$utc."\n";
                    //print_r($dt); exit();
                    $_POST['pick_due_date']=$utc;
                }
                
                
                    $pickOrder = new PickOrder();
                    // Check if user has rights for change Status field. If does not - default UC (UnConfirmed).
                    if (isset($_POST['pick_status'])) {
                        if ($_POST['pick_status'] != 'UC' && !$this->minder->isAdmin && !$this->minder->isCreditManagerT()) {
                            $_POST['pick_status'] = 'UC';
                        }
                    }
                    if ($pickOrder->save($_POST)) {
                        $pickOrder->imported = 'N';
                        $pickOrder->importErrors = 0;

                        $pickOrder->sSameAsSoldFrom = !empty($_POST['s_same_as_sold_from']) ? 'T' : 'F';
                        $pickOrder->pSameAsInvoiceTo = !empty($_POST['p_same_as_invoice_to']) ? 'T' : 'F';
                        $pickOrder->supplierList = !empty($_POST['supplier_list']) ? 'T' : 'F';
                        $pickOrder->invWithGoods = !empty($_POST['inv_with_goods']) ? 'T' : 'F';
                        $pickOrder->partialPickAllowed = !empty($_POST['partial_pick_allowed']) ? 'T' : 'F';
                        $pickOrder->overSized = !empty($_POST['over_sized']) ? 'T' : 'F';
                  
                        if (!$this->minder->pickOrderCreate($pickOrder)) {
                            $this->addError('Error occured.');
                        } else {
                            $this->session->params[$this->_controller]['index']['pick_items']['pick_order'] = $pickOrder->pickOrder;
                            $this->add = 1;
                            $this->addIssnItemsAction();
                            unset($this->add);
                        }
                    }
                    $this->_redirect('transfer-order');
                    break;
            }
            if ($pickOrder == null) {
                $this->view->pickOrder = $this->view->minder->newPickOrder();
            } else {
                $this->view->pickOrder = $pickOrder;
            }
        } else {
            $this->view->pickOrder = $this->view->minder->newPickOrder();
            
            $this->view->pickOrder->paymentMethod = '';
            $this->view->pickOrder->terms = '';
            $this->view->pickOrder->personId = '';
        
        }
        $this->view->pageTitle = 'New Transfer Orders';

        $notes = $this->minder->getPickOrderNotesList();
        $this->view->notes = array();
        $this->view->pickOrder->pickOrderType = $pickOrderType;
        foreach ($notes as $k => $v) {
            $this->view->notes[$k] = explode('|', $v);
        }

        $this->view->withLines = isset($this->session->params[$this->_controller]['index']['pick_items']) && count($this->session->params[$this->_controller]['index']['pick_items']) > 1;
        $this->view->loanPeriodList     = $this->minder->getLoanPeriodList();
        $this->view->shipViaList        = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $this->view->paymentMethodList  = minder_array_merge(array('' => ''), $this->minder->getPaymentMethodList());
        $this->view->paymentTermsList   = minder_array_merge(array('' => ''), $this->minder->getPaymentTermsList());
        
                     
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->renderScript('header.phtml', 'header');
            $this->render();
            $this->renderScript('footer.phtml', 'footer');
        }
    }

    public function linesAction()
    {
        //if (!$this->getRequest()->isXmlHttpRequest() && !$this->_request->getPost('action1')) {
        //    $this->_redirect('transfer-order');
        //}
        $this->view->headers = array(
            'PICK_ORDER'            => 'Order #',
            'PICK_LABEL_NO'         => 'Label #',
            'PICK_ORDER_LINE_NO'    => 'Line #',
            'PROD_ID'               => 'Product ID',
            'SSN_ID'                => 'ISSN',
            'DESCRIPTION'           => 'Description',
            'PICK_ORDER_QTY'        => 'Qty',
            'AGEDAYS'               => 'Days',
            'SALE_PRICE'            => '$ Rate',
            'LINE_TOTAL'            => '$ Total',
            'PICK_LINE_STATUS'      => 'Status',
        );

        if (!empty($_POST['action1'])) {
            switch (strtolower($_POST['action1'])) {
                case 'add non-product':
                    reset($this->session->params[$this->_controller]['index']['pick_orders']);
                    $pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
                    $this->session->from = array('module' => 'default', 'controller' => 'transfer-order', 'action' => 'index', 'pick_order' => $pickOrder, 'product' => 0);
                    $this->_redirect('warehouse/ssn/index/from/transfer-order/without/1');
                    break;

                case 'add product line':
                    reset($this->session->params[$this->_controller]['index']['pick_orders']);
                    $pickOrder = current($this->session->params[$this->_controller]['index']['pick_orders']);
                    $this->session->from = array('module' => 'default', 'controller' => 'transfer-order', 'action' => 'index', 'pick_order' => $pickOrder, 'product' => 1);
                    $this->_redirect('warehouse/products/index/from/transfer-order/without/1');
                    break;

                case 'delete line':
                    if (!isset($this->session->conditions[$this->_controller]['lines'])) {
                        $this->session->conditions[$this->_controller]['lines'] = array();
                    }
                    $pickOrders = $this->session->params[$this->_controller]['index']['pick_orders'];
                    try {
                        foreach ($this->session->conditions[$this->_controller]['lines'] as $id) {
                            if (false === ($pickItem = $this->minder->getPickItemById($id))) {
                                $this->addError('Line ' . $id . ' does not exists.');
                            } else {
                                $this->minder->pickItemDelete($pickItem->pickOrder, $pickItem->pickLabelNo);
                                $this->addMessage('Line ' . $id . ' was deleted from order ' . $pickItem->pickOrder . '.');
                            }
                            if (false !== ($key = array_search($id, $this->session->conditions[$this->_controller]['lines']))) {
                                unset($this->session->conditions[$this->_controller]['lines'][$key]);
                            }
                        }
                    } catch (Exception $e) {
                        $this->addError('Error occured during delete lines.' . $e->getMessage());
                    }
                    $this->_redirect('transfer-order');
                    break;

                case 'report: csv':

                    $ordersList     = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines  = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList  = $this->minder->getPickItemsByPickOrders($ordersList);
                    $data           = array();

                    if (is_array($pickItemsList)) {
                        $data = array_intersect_key($pickItemsList, $selectedLines);
                    }
                    $this->view->data = $data;
                    $this->_processReportTo('REPORT: CSV');
                    break;

                case 'report: xls':

                    $ordersList     = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines  = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList  = $this->minder->getPickItemsByPickOrders($ordersList);
                    $data           = array();

                    if (is_array($pickItemsList)) {
                        $data = array_intersect_key($pickItemsList, $selectedLines);
                    }
                    $this->view->data = $data;
                    $this->_processReportTo('REPORT: XLS');
                    break;

                case 'report: xml':

                    $ordersList     = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines  = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList  = $this->minder->getPickItemsByPickOrders($ordersList);
                    $data           = array();

                    if (is_array($pickItemsList)) {
                        $data = array_intersect_key($pickItemsList, $selectedLines);
                    }
                    $this->view->data = $data;
                    $this->_processReportTo('REPORT: XML');
                    break;

                case 'report: txt':

                    $ordersList     = $this->session->params[$this->_controller]['index']['pick_orders'];
                    $selectedLines  = $this->session->conditions[$this->_controller]['lines'];
                    $pickItemsList  = $this->minder->getPickItemsByPickOrders($ordersList);
                    $data           = array();

                    if (is_array($pickItemsList)) {
                        $data = array_intersect_key($pickItemsList, $selectedLines);
                    }
                    $this->view->data = $data;
                    $this->_processReportTo('REPORT: TXT');
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
                    $this->_redirect('transfer-order');
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

        $allowed = array('PICK_ORDER'          => 'PICK_ORDER.PICK_ORDER LIKE ? AND ',
                         'PERSON_ID'           => 'PICK_ORDER.PERSON_ID LIKE ?  AND ',
                         'CUSTOMER_PO_WO'      => 'PICK_ORDER.CUSTOMER_PO_WO LIKE ? AND ',
                         'PICK_FROM_DUE_DATE'  => 'PICK_ORDER.PICK_DUE_DATE >= ? AND ',
                         'PICK_TO_DUE_DATE'    => 'PICK_ORDER.PICK_DUE_DATE <= ? AND ',
                         'RETURN_FROM_DATE'    => 'PICK_ORDER.RETURN_DATE >=  ? AND ',
                         'RETURN_TO_DATE'      => 'PICK_ORDER.RETURN_DATE <= ? AND '
                         );

        $conditions =  $this->_getConditions('index');
        $clause     =  $this->_makeClause($conditions, $allowed); 

        $clause = array_merge(array('PICK_ORDER_TYPE = ? AND ' => 'TO'), $clause);
        if($this->session->tMode != null) {
            $clause = array_merge($clause, $this->session->pickModeCriteria);
        }
       
        $pageSelector = $this->session->navigation[$this->_controller]['index']['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller]['index']['show_by'];  
        
        $fieldsData   = $this->minder->getUserHeaders('SCN_TORDER');
        
        $lines = $this->view->minder->getPickOrders($clause, $pageSelector, $showBy, $fieldsData);
        $lines = $lines['data'];
        
        $conditions = $this->_getConditions('index');
        $numRecords = count($lines);

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
        $this->view->loanRateData          = $this->minder->getLoanRateData('SSN'); 
        $this->view->pickStatusDescription = $this->minder->getPickOrderStatusList();
        $this->view->pickItems = array_slice($this->view->pickItems,
        $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
        $this->view->maxno);
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
            $this->view->pickItems = $this->minder->getPickItemsByPickOrders($pickOrders);
        }

        $this->view->conditions = $this->_markSelected($this->view->pickItems, $id, null, $method, $this->_action);

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
        $this->view->counters['total_selected'] = round($this->view->counters['total_selected'], 2);
        $this->view->counters['total_displayed'] = round($this->view->counters['total_displayed'], 2);
    }

    public function addIssnItemsAction() {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        
        $this->_helper->viewRenderer->setNoRender(true);
        $case = $this->session->params['pick-order']['index']['pick_items']['case'];
        
        $log->info('case :' . $case);
     
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
                    $this->addError($e->getMessage());
                }
            }
        
        unset($this->session->params['pick-order']['index']['pick_items']);

        if ($redirect = $this->_getParam('redirect')) {
            $this->_redirect($redirect);
        }    
    }
        
    public function searchAction()
    {
        if ($_REQUEST['field'] === 'product_id') {
            $this->view->data = $this->minder->getProductList($_REQUEST['q']);
        }
        if ($_REQUEST['field'] === 'person_id') {
            $oldLimitCompany = null;
            if (isset($_REQUEST['company_id']) && !isset($_REQUEST['nolimit'])) {
                $oldLimitCompany = $this->minder->limitCompany;
                $this->minder->limitCompany = $_REQUEST['company_id'];
            }
            //$this->view->data = $this->minder->getPersonList(array('CU', 'CO', 'IN', 'RP'), $_REQUEST['q']);
            $this->view->data = $this->minder->getPersonList(array('RP'), $_REQUEST['q']);

            if ($oldLimitCompany != null) {
                $this->minder->limitCompany = $oldLimitCompany;
            }
            /*
            foreach ($this->view->data as $key => $value) {
                $value = preg_split('/\s+/si', trim($value));
                $this->view->data[$key] = $value[0];
            }
            */
        }
        if ($_REQUEST['field'] === 's_person_id') {
            $this->view->data = $this->minder->getPersonList(array('CS'), $this->_getParam('q'));
            foreach ($this->view->data as $key => $value) {
                $value = preg_split('/\s+/si', trim($value));
                $this->view->data[$key] = $value[0];
            }
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
        if ($_REQUEST['field'] === 'person_name') {
            $oldLimitCompany = null;
            if (isset($_REQUEST['company_id']) && !isset($_REQUEST['nolimit'])) {
                $oldLimitCompany = $this->minder->limitCompany;
                $this->minder->limitCompany = $_REQUEST['company_id'];
            }
            //$this->view->data = $this->minder->getPersonList(array('CU', 'CO', 'IN', 'RP'), $_REQUEST['q']);
            $result = $this->minder->getPersonList(array('RP'), $_REQUEST['q']);
            $contactName = array_values($result);
            $this->_helper->json(array('contact_name' => $contactName[0]));
        }
        if ($_REQUEST['field'] === 'device_id') {
            $this->view->data;
        }
        if ($_REQUEST['field'] === 'terminal_id') {
            $this->view->data;
        }
        if($_REQUEST['field'] === 'product_id') {
            $this->view->data = $this->minder->getProductList($_REQUEST['q']);
        }
    }

    public function lookupAction()
    {
        $companyId = '';
        $warehouseId = '';
        try {
            if ($tmp = $this->_getParam('pick_order')) {
                $pickOrder = $this->minder->getPickOrder($tmp, 'TO');
                $companyId = $pickOrder->companyId;
                $warehouseId = $pickOrder->whId;
            }
            if ($tmp = $this->_getParam('company_id')) {
                $this->view->data = $this->minder->getCompany($tmp);
            }
            if ($tmp = $this->_getParam('person_id')) {
                $this->view->data = $this->minder->getPerson($tmp);
            }
            if ($tmp = $this->_getParam('s_person_id')) {
                $this->view->data = $this->minder->getPerson($tmp);
            }
            if ($tmp = $this->_getParam('prod_id')) {
                $this->view->data = $this->minder->getProduct($tmp, $companyId, $warehouseId);
            }
            if ($tmp = $this->_getParam('tmp')) {
                $this->view->data = $this->minder->getProductByISSN($tmp);
            }
            if ($tmp = $this->_getParam('field')) {
                $value = $this->_getParam('q');
                $this->view->data = array('field_description' => $this->minder->getSysEquipDescription($value));
            }
        } catch (Exception $e) {
        }
    }

    public function detailAction()
    {

        $this->view->pageTitle = $table = strtoupper($this->getRequest()->getParam('table'));
        if ('' == $table) {
            $this->_redirect('/');
        }

        $controller = $this->_controller;
        $action = $this->_action;

        //
        // Get allowed.
        //
        $allowed = array();
        $temp = $this->minder->getFieldList($table);
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
        $clause = $clause + array('_limit_'  => $this->view->navigation[$table]['show_by'] ,
            '_offset_' => $this->view->navigation[$table]['show_by'] * $this->view->navigation[$table]['pageselector']);

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
        $nav = $this->view->navigation;
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
            /*
            if (++$i >= 6) {
                break;
            }
             */
        }

        $this->view->headers = $headers;
        $this->view->dataset = $dataset;
        $this->view->row = $dataset->current();
    }

    /**
     * Edit a line to an order (/transfer-order/edit-line
     *
     * Display the form allow the user to add a line to an order or add
     * the line if the user is submitting the form
     *
     * @return void
     */
    public function editLineAction()
    {
        if ($this->_request->isPost()) {
            $action = strtolower($this->_request->getPost('action'));
            switch ($action) {
                case 'cancel':
                    $this->_redirect('transfer-order');
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
                        $pickItem->id = $pickItem->pickLabelNo = $tmpPickItem->pickLabelNo;
                        $pickItem->pickOrder = $tmpPickItem->pickOrder;
                        $pickItem->pickOrderLineNo = $tmpPickItem->pickOrderLineNo;
                        $pickItem->pickRetrieveStatus = $tmpPickItem->pickRetrieveStatus;
                        $pickItem->despatchLocationGroup = $tmpPickItem->despatchLocationGroup;
                        $pickItem->wipPrelocnOrdering = $tmpPickItem->wipPrelocnOrdering;
                        $pickItem->wipPostlocnOrdering = $tmpPickItem->wipPostlocnOrdering;
                        $pickItem->pickQtyDifference = $tmpPickItem->pickQtyDifference;
                        $pickItem->pickQtyDifference2 = $tmpPickItem->pickQtyDifference2;
                        $pickItem->lastUpdateDate = $tmpPickItem->lastUpdateDate;
                        $pickItem->pickPickFinish = $tmpPickItem->pickPickFinish;
                        $pickItem->pickLocnSeq = $tmpPickItem->pickLocnSeq;
                        $pickItem->pickLabelDate = $tmpPickItem->pickLabelDate;
                        $pickItem->pickStarted = $tmpPickItem->pickStarted;
                        $pickItem->despatchTs = $tmpPickItem->despatchTs;
                        $pickItem->createDate = $tmpPickItem->createDate;
                        $pickItem->userId = $tmpPickItem->userId;
                        $pickItem->deviceId = $tmpPickItem->deviceId;
                        $pickItem->checkinStart = $tmpPickItem->checkinStart;
                        $pickItem->checkinFinish = $tmpPickItem->checkinFinish;
                        $pickItem->checkinUserId = $tmpPickItem->checkinUserId;

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
                            $this->_redirect('transfer-order');
                        }
                    }
                    break;
            } // switch
        }

        if (!isset($params)) {
            $params = $this->_getAllParams();
        }
        $this->view->pageTitle = 'Edit Transfer Orders Line';
        $this->view->pickOrder = $params['pick_order'];
        $this->view->pickItem = isset($pickItem) ? $pickItem : $this->view->minder->getPickItem($params['pick_order'], $params['line_no']);

        if (empty($this->view->pickItem->taxRate)) {
            $this->view->pickItem->taxRate = $this->minder->getPickItemTaxRate($this->view->pickItem->pickLabelNo);
        }

        if (empty($this->view->pickItem->warrantyTerm)) {
            list($this->view->pickItem->warrantyTerm) = array_values($this->minder->getControlFields('DEFAULT_WARRANTY'));
        }

        $this->view->shipViaList = $this->minder->getShipViaList();
        $this->view->productList = $this->minder->getProductList();
        $this->view->warehouseList = $this->minder->getWarehouseList(true);
        $this->view->despatchLocationList = $this->minder->getDespatchLocationList($this->view->pickItem->whId);
        $this->view->warrantyList = $this->minder->getWarrantyList();
    }

    public function shippingServiceAction()
    {
        if (isset($_REQUEST['ship_via'])) {
            $this->view->data = $this->minder->getShipServiceList($_REQUEST['ship_via']);
        } else {
            $this->view->data = array();
        }
    }

    public function despatchLocationAction()
    {
        if (isset($_REQUEST['wh_id'])) {
            $this->view->data = $this->minder->getDespatchLocationList($_REQUEST['wh_id']);
        } else {
            $this->view->data = array();
        }
    }

    /**
     * Diplay the page allowing the user to allocate products (/transfer-order/allocate-orders)
     *
     * This function handle allocating picks for an order.
     *
     * @return void
     */
    public function allocateOrdersAction()
    {
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
                                     'COMPANY_ID'       => 'Company ID');
        
        $dueDate = (isset($_POST['due_date']) ? $_POST['due_date'] : '');
        
        $this->_preProcessNavigation();
        
        $pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        $allowed    = array('due_date'  =>  'PICK_DUE_DATE = ? AND ');
        $conditions = $this->_setupConditions(null, $allowed);
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge($clause, array('PICK_ORDER.PICK_ORDER_TYPE = ? AND ' => 'TO'));
        
        $ordersList = '';
        if(count($this->session->transferOrders) > 0) {
            foreach($this->session->transferOrders as $filterPickItem) {
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
            $this->view->pick_mode   = $this->session->tMode;
        }
        
        $this->_postProcessNavigation($pickItemToDislpay);
    }

    /**
     * Diplay the page allowing the user to allocate picks to a device (/transfer-order/allocate-picks)
     *
     * This function handle allocating picks.
     *
     * @return void
     */
    public function allocatePicksAction()
    {
        
        if (isset($_POST['action']) && $_POST['action'] == 'Allocate Picks') {
            if (isset($_POST['device_id'])) {
                foreach ($_POST['allocate'] as $allocate) {
                    $a = explode('|', $allocate);
                    $d = explode('|', $_POST['device_id']);
                    if ($a[0] == 'o') {
                        $t = new Transaction_PKALI();
                        $t->deviceId    = $d[0];
                        $t->pickOrder   = $a[1];
                        $t->userId      = $d[1];
                        $this->minder->doTransaction($t);
                    }
                    if ($a[0] == 'p') {
                        $t = new Transaction_PKALG();
                        $t->userId      = $_POST['user_id'];
                        $t->subLocnId   = $d[0];
                        $t->deviceId    = $d[0];
                        $t->pickOrder   = $a[1]; 
                        $this->minder->doTransaction($t, 'Y', 'SSSSSSSSS', '', 'MASTER    ');
                    }
                    /*
                    if ($a[0] == 'p') {
                        $t = new Transaction_PKALF();
                        $t->deviceId = $d[0];
                        $t->pickLabelNo = $a[1];
                        $t->prodId = $a[2];
                        $t->dueDate = $a[3];
                        $this->minder->doTransaction($t);
                    }
                    */
                }
            $this->_forward('index');
            }
        }
    
        $this->view->pageTitle = 'Allocate Picks';
        $this->view->headers = array('Device', 'Description', 'User', 'Total');
        $this->view->devices = $this->minder->getDevicesForAllocating();
        $this->view->allocates = $_POST['allocate'];
        
    }

    /**
     * Diplay the page allowing the user to allocate products (/transfer-order/allocate-products)
     *
     * This function handle allocating picks for an order.
     *
     * @return void
     */
    public function allocateProductsAction()
    {
      
        if (!empty($_POST) && !empty($_POST['allocate'])) {
            $this->_forward('allocate-picks');
        }
        $this->view->pageTitle = 'Allocate Products';
        $this->view->headers = array('PICK_LABEL_NO' => 'Product ID', 
                                     'SHORT_DESC'    => 'Description', 
                                     'PICK_ORDER'    => 'Order No', 
                                     'PICK_ORDER_QTY'=> 'Qty', 
                                     'PICK_PRIORITY' => 'Priority', 
                                     'PICK_DUE_DATE' => 'Due Date', 
                                     'WH_ID'         => 'WH', 
                                     'COMPANY_ID'    => 'Company ID', 
                                     'NAME'          => 'Delivery To');
       
        $dueDate      = (isset($_POST['due_date']) ? $_POST['due_date'] : '');
        $productId    = (isset($_POST['product_id']) ? $_POST['product_id'] : '');
        
        $this->_preProcessNavigation();
        
        $pageNo         = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $resultsPerPage = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        $allowed        = array('due_date'  =>  'PICK_DUE_DATE = ? AND ',
                                'product_id'=>  'PICK_ITEM.PROD_ID = ? AND ');
        
        $conditions = $this->_setupConditions(null, $allowed);
        $clause     = $this->_makeClause($conditions, $allowed);
        $clause     = array_merge($clause, array('PICK_ORDER.PICK_ORDER_TYPE = ? AND ' => 'TO'));
        
        $ordersList = '';
        if(count($this->session->transferOrders) > 0) {
            foreach($this->session->transferOrders as $filterPickItem) {
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
        $this->view->pick_mode   = $this->session->pickModeName;
        
        if(empty($this->session->pickModeName)) {
            $this->view->pick_mode   = 'none';
        } else {
            $this->view->pick_mode   = $this->session->tMode;
        }
        
        $this->_postProcessNavigation($pickItemToDislpay);       
        
    }

    public function addressAction()
    {
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
                            'phone'     => $result['telephone']
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
                            'phone'     => $result['telephone1']
                        ));
                    }
                }
            }
            $this->_helper->json($result);
        } else {
            $json = array();
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
                );
            }
            $this->_helper->json($json);
        }
    }

    public function calcDateAction()
    {
        $dfmt = Zend_Registry::get('config')->date->dateformat;
        $startDate  = $this->_request->first_date;
        $modifier   = '+' . trim($this->_request->modifier, '- ');
        //$periodList = $this->minder->getLoanPeriodList();
        $period     = trim($this->_request->period);
        $modifier *= $period;
        $modifier .= ' days';

        //$modifier  .= ' ' . $periodList[$period];

        $date = new DateTime($startDate);
        $date->modify($modifier);
        $w = $date->format('w');
        if ($w == 0 || $w == 6) {
            $date->modify('next Monday');
        }
        $days = strtotime($date->format($dfmt)) - strtotime($startDate);
        $days = $days/86400;
        //$days = date('z', strtotime($date->format($dfmt)) - strtotime($startDate));
        $days = ceil($days / $period);
        $data = array('date'   => $date->format($dfmt),
                      'days'   => $days);
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($data);
    }
    
    public function addFromLocationAction()
    {
        
        $conditions = $this->_getConditions('index');
        
        $pageselector = $this->getRequest()->getParam('pageselector', 0);
        $show_by = $this->getRequest()->getParam('show_by', 10);
        
        
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
        
        $order = $this->minder->getPickOrder($id, 'TO');
        
        if ($order->despatchLocation==null && $order->despatchLocation=="") {
            $this->view->ds_null = 1;
            return; 
        }       
        
//      var_dump($order); die();

        $despatchLocation = substr($order->despatchLocation,2);
        $this->session->params[$this->_controller]['addFromLocation']['despatch_location'] = $order->despatchLocation; 
        $whId = $order->whId;
        
        $clause= array();
        
        $clause['PICK_ORDER IS NULL']       =   '';

        if ($whId!=null) {
            $clause['WH_ID = ?']        =   $whId;
        }
        
        
        if ($despatchLocation!=null) {
            $clause['LOCN_ID = ?']      =   $despatchLocation;
        }
        
        $issns = $this->minder->getIssns($clause);
        $this->view->pages = ceil(count($issns)/$show_by);
        
//      var_dump($order);
        $issns = array_slice ($issns, $pageselector*$show_by, $show_by);
        $this->view->products = $issns;
        $this->view->pageselector = $pageselector;
        $this->view->show_by = $show_by;
        
        
        $this->view->desc1 = $order->whId.'-'.$despatchLocation; 
        $location = $this->minder->getLocationListByClause(array('LOCN_ID = ?'=>$despatchLocation));
        $this->view->desc2 = $location[$despatchLocation];
        
        
        $this->session->params[$this->_controller]['addFromLocation']['issns'] = array();
        $this->session->params[$this->_controller]['addFromLocation']['wh_id'] = $whId;
        $this->session->params[$this->_controller]['addFromLocation']['pick_order'] = $id;
//      $this->session->params[$this->_controller]['addFromLocation']['despatch_location'] = $despatchLocation; 
        $this->session->params[$this->_controller]['addFromLocation']['clause'] = $clause;
        
//      var_dump($this->session->params[$this->_controller]['addFromLocation']['despatch_location']);
        
//      var_dump($pageselector);var_dump($show_by);var_dump($this->view->pages);
//      var_dump($issns);
    }
    
    
    public function addFromLocationMarkAction()
    {
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
        
        $jsonObject = new stdClass();
        $jsonObject->count = count($issns);
        
        die(json_encode($jsonObject));
    }
    
    public function addFromLocationButtonAction()
    {
        if (isset ($this->session->params[$this->_controller]['addFromLocation']['issns'])) {
            $issns = $this->session->params[$this->_controller]['addFromLocation']['issns'];
        } else {
            return; 
        }

        $jsonObject = new stdClass();
        
        $result = array();
        
        foreach ($issns as $id) {
            
//          var_dump($this->session->params[$this->_controller]['addFromLocation']['despatch_location']);
//          die();
            
            $issn = $this->minder->getIssn($id);
            $qty = $issn['CURRENT_QTY'];
            
            $transaction = new Transaction_PKTRD();

            $whId = $this->session->params[$this->_controller]['addFromLocation']['wh_id'];
            $locnId = $this->session->params[$this->_controller]['addFromLocation']['despatch_location']; 
            $pickOrder = $this->session->params[$this->_controller]['addFromLocation']['pick_order'];
            
            $transaction->whId  = $whId; 
            $transaction->locnId = $locnId; 
            $transaction->objectId = $id;
            if (strlen($pickOrder) <= 10) {
                $transaction->subLocnId = $pickOrder;
            }
            $transaction->reference = "Picked by Transfer to Despatch |" . $pickOrder;
            $transaction->qty = $qty;
            
            $row = array();
            
            if ($locnId!=null) { 
                $response = $this->minder->doTransactionResponse($transaction );
                $row['response'] = $this->minder->lastError;
            } else {
                $row['response'] = 'DESPATCH_LOCATION is Null';
            }
            $row['wh_id'] = $whId;
            $row['locn_id'] = $locnId;
            $row['id'] = $id;
            $row['pick_order'] = $pickOrder;
            $row['qty'] = $qty;
            
            
            $result[] = $row;

        }
        
        $this->session->pickOrders = array();
        
        $jsonObject->result = $result;
        die(json_encode($jsonObject));
        
        
    }   
    
    public function reportAddFromLocationAction()
    {
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
    
    
    protected function _allowOrderStatus() {
        return array('UC', 'OP', 'UP');
    }

    /*protected function _setupShortcuts()
    {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('orders')->buildMinderMenuArray();

        return $this;
    }*/
}
