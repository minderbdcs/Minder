<?php
  
class Orders_PurchaseOrderController extends Minder_Controller_Action {
    
    const ORDERS_MODEL_NAME        = 'PURCHASEORDER';
    const ORDERS_DATASET_NAMESPACE = 'ORDERS-PURCHASEORDER';
    
    const LINES_MODEL_NAME        = 'PURCHASEORDERLINE';
    const LINES_DATASET_NAMESPACE = 'ORDERS-PURCHASEORDERLINE';
    
    const SUBLINES_MODEL_NAME        = 'PURCHASESUBLINE';
    const SUBLINES_DATASET_NAMESPACE = 'ORDERS-PURCHASESUBLINE';
    
    public function init() {
        parent::init();
        $this->view->pageTitle = 'Purchase Order';
    }

    public function indexAction() {
        try {
            $this->view->orderSsName     = $this->view->searchFormSsName = self::ORDERS_MODEL_NAME;
            $this->view->ordersNamespace = self::ORDERS_DATASET_NAMESPACE;
            
            $this->view->linesSsName     = self::LINES_MODEL_NAME;
            $this->view->linesNamespace  = self::LINES_DATASET_NAMESPACE;
            
            $this->view->sublinesSsName     = self::SUBLINES_MODEL_NAME;
            $this->view->sublinesNamespace  = self::SUBLINES_DATASET_NAMESPACE;
            $this->view->statusList         = array_merge(array(''=>''), $this->minder->getOptionsList('PO_STATUS'));

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ORDERS_MODEL_NAME);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */
            $ordersModel   = $screenBuilder->buildSysScreenModel(self::ORDERS_MODEL_NAME, new Minder_SysScreen_Model_PurchaseOrder());
            $ordersModel->setConditions($ordersModel->makeConditionsFromSearch($searchFields));
        
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $ordersModel, true, self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $linesModel    = $screenBuilder->buildSysScreenModel(self::LINES_MODEL_NAME, new Minder_SysScreen_Model_PurchaseOrderLine());
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $this->view->renderSublines = $screenBuilder->isSysScreenDefined(self::SUBLINES_MODEL_NAME);
            $sublinesModel = $screenBuilder->buildSysScreenModel(self::SUBLINES_MODEL_NAME, new Minder_SysScreen_Model_PurchaseOrderSubline());
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $sublinesModel, true, self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->initSubDatasets();
            
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);
                    
                $this->view->hasErrors = true;
            }
        
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }
        
        return;
        
    }    

    public function getDatasetAction() {
        $datasets = array(
            self::ORDERS_DATASET_NAMESPACE   => self::ORDERS_MODEL_NAME,
            self::LINES_DATASET_NAMESPACE    => self::LINES_MODEL_NAME,
            self::SUBLINES_DATASET_NAMESPACE => self::SUBLINES_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector   = $this->_helper->getHelper('RowSelector');
        $this->view->sysScreens = array();
        
        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);

            if (isset($sysScreens[$namespace]) && isset($sysScreens[$namespace]['paginator'])) {
                $pagination = $this->fillPagination($pagination, $sysScreens[$namespace]);
            }

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = array(
                'paginator'    => $this->view->paginator,
                'rows'         => $this->view->dataset,
                'selectedRows' => $this->view->selectedRows,
                'ssName'       => $modelname
            );
        
            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }
    
    public function selectRowAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        
        try{
            $sysScreens = $this->getRequest()->getParam('sysScreens', array());
            foreach ($sysScreens as $namespace => $params) {
                $pagination = $this->restorePagination($namespace);
            
                if (isset($sysScreens[$namespace])) {
                    if (isset($sysScreens[$namespace]['paginator'])) {
                        $pagination['selectedPage']  = (isset($sysScreens[$namespace]['paginator']['selectedPage']))  ? $sysScreens[$namespace]['paginator']['selectedPage']  : $pagination['selectedPage'];
                        $pagination['showBy']        = (isset($sysScreens[$namespace]['paginator']['showBy']))        ? $sysScreens[$namespace]['paginator']['showBy']        : $pagination['showBy'];
                        $pagination['selectionMode'] = (isset($sysScreens[$namespace]['paginator']['selectionMode'])) ? $sysScreens[$namespace]['paginator']['selectionMode'] : $pagination['selectionMode'];
                    }
    
                    if (isset($sysScreens[$namespace]['rowId']) && isset($sysScreens[$namespace]['state'])) {
                        /**
                        * @var Minder_Controller_Action_Helper_RowSelector
                        */
                        $rowSelector = $this->_helper->getHelper('RowSelector');
                    
                        $rowSelector->setSelectionMode($pagination['selectionMode'], $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                        $rowSelector->setRowSelection($sysScreens[$namespace]['rowId'], $sysScreens[$namespace]['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                    }
                }
            }
            
            $this->initSubDatasets();
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        
        try{
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ORDERS_MODEL_NAME);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);



        foreach($array_new as $key=>$val){

                    foreach($val as $key1=>$val1){

                         if($key1=='SEARCH_VALUE'&& $val1!=''){

 if(DateTime::createFromFormat('Y-m-d H:i:s', $val1)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val1)!==FALSE) {


                            $datetimet=$val1;
                            $tz_tot='UTC';
                            $format='Y-m-d h:i:s';

                            $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                            $dtt->setTimeZone(new DateTimeZone($tz_tot));

                           $searchFields[$key][$key1]=$dtt->format($format);

                             }

                         }


                    }

            }





            
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model
            */
            $rowsModel    = $rowSelector->getModel(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->initSubDatasets();
            
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    protected function initSubDatasets() {
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        $selectedOrdersAmount = $rowSelector->getSelectedCount(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        /**
        * @var Minder_SysScreen_Model_PurchaseOrderLine
        */
        $linesModel    = $rowSelector->getModel(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        /**
        * @var Minder_SysScreen_Model_PurchaseOrderSubline
        */
        $sublinesModel = $rowSelector->getModel(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
        if ($selectedOrdersAmount > 0) {
            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */      
            $ordersModel = $rowSelector->getModel(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $ordersModel->addConditions($rowSelector->getSelectConditions(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $selectedOrders = $ordersModel->selectPurchaseOrder(0, $selectedOrdersAmount);
            
            $tmpFilterString = 'PURCHASE_ORDER_LINE.PURCHASE_ORDER IN (' . substr(str_repeat('?, ', count($selectedOrders)), 0, -2) . ')';
            $linesModel->setConditions(array($tmpFilterString => $selectedOrders));
        } else {
            $linesModel->setConditions(array('1 = 2' => array()));
        }
        $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        $selectedLinesAmount = $rowSelector->getSelectedCount(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $selectedLinesAmount));
        if ($selectedLinesAmount > 0) {
            $linesModel = $rowSelector->getModel(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $linesModel->addConditions($rowSelector->getSelectConditions(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));

            $selectedOrdersAndLines = $linesModel->selectPurchaseOrderAndLine(0, $selectedLinesAmount);

            $sublinesModel->setConditions($sublinesModel->makeConditionsFromPurchaseOrderAndLine($selectedOrdersAndLines));
            
        } else {
            $sublinesModel->setConditions(array('1 = 2' => array()));
        }
        $rowSelector->setRowSelection('select_complete', 'init', null, null, $sublinesModel, true, self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    public function setOrderStatusAction() {
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors : array();
        
        try{
            $status = $this->getRequest()->getParam('status');
            if (empty($status)) {
                $this->view->warnings[] = 'Select status.';
                return $this->_forward('get-dataset');
            }
            
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $selectedOrdersAmount = $rowSelector->getSelectedCount(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        
            if ($selectedOrdersAmount < 1) {
                $this->view->warnings[] = 'No orders selected, please select one.';
                return $this->_forward('get-dataset');
            }
            
            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */
            $ordersModel = $rowSelector->getModel(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $ordersModel->addConditions($rowSelector->getSelectConditions(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            foreach ($ordersModel->selectPurchaseOrder(0, $selectedOrdersAmount) as $purchaseOrder) {
                if (false !== $this->minder->updatePurchaseOrderStatus($purchaseOrder, $status)) {
                    $this->view->messages[] = 'Order #' . $purchaseOrder . ' status updated.';
                } else {
                    $this->view->errors[] = 'Error updating Order #' . $purchaseOrder . ' status. ' . $this->minder->lastError;
                }
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
            
        return $this->_forward('get-dataset');
    }


//------------ LINES ACTIONS -----------------------------    
    public function setLineStatusAction() {
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors : array();
        
        try{
            $status = $this->getRequest()->getParam('status');
            if (empty($status)) {
                $this->view->warnings[] = 'Select status.';
                return $this->_forward('get-dataset');
            }
            
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $selectedLinesAmount = $rowSelector->getSelectedCount(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        
            if ($selectedLinesAmount < 1) {
                $this->view->warnings[] = 'No lines selected, please select one.';
                return $this->_forward('get-dataset');
            }
            
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderLine
            */
            $linesModel = $rowSelector->getModel(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $linesModel->addConditions($rowSelector->getSelectConditions(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            list($tmpErrors, $tmpMessages) = $linesModel->setPoLineStatus($status);
            
            $this->view->messages = array_merge($this->view->messages, $tmpMessages);
            $this->view->errors   = array_merge($this->view->errors, $tmpErrors);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
            
        return $this->_forward('get-dataset');
    }
    
    public function importLinesAction() {
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors   : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();
        
        $fileElement = new Zend_Form_Element_File('import_lines_file');
        if (!$fileElement->receive()) {
            $this->view->errors[] = 'Error uploading import file. ' . implode(' ', $fileElement->getMessages());
            return $this->_forward('get-dataset');
        }
        
        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $selectedOrdersAmount = $rowSelector->getSelectedCount(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            if ($selectedOrdersAmount < 1) {
                $this->view->warnings[] = 'No orders selected. Select one.';
                return $this->_forward('get-dataset');
            }
            
            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */
            $orderModel = $rowSelector->getModel(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $orderModel->addConditions($rowSelector->getSelectConditions(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $selectedOrder = current($orderModel->selectPurchaseOrder(0, 1));
            
            $lineImporter = new Minder_ImportPurchaseOrderLines($selectedOrder);
            $lineImporter->doImport($fileElement->getFileName());
            
            $this->view->warnings = array_merge($this->view->warnings, $lineImporter->getWarnings());
            $this->view->messages = array_merge($this->view->messages, $lineImporter->getMessages());
        } catch (Exception $e) {
            $this->view->errors[] = 'Error importing lines. ' . $e->getMessage();
        }
        
        return $this->_forward('get-dataset');
    }

    public function lineEditFormAction(){
        if (!$this->minder->isEditable && !$this->minder->isSysAdmin() && !$this->minder->isStatusAdjustPoLine())
            $this->_redirector->goto('index');
            
        $rowId = $this->getRequest()->getParam('line', 'new');
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        $purchaseOrderLine = array();
        if ($rowId == 'new') {
            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */
            $dataModel = $rowSelector->getModel(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $dataModel->addConditions($rowSelector->getSelectConditions(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            
            $purchaseOrderNo = current($dataModel->selectPurchaseOrder(0, 1));
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $purchaseOrderNo));
            $purchaseOrderLineNo    =   $this->minder->getNewPurchaseLineNo($purchaseOrderNo);
        } else {
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderLine
            */
            $dataModel = $rowSelector->getModel(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $dataModel->addConditions($dataModel->makeConditionsFromId($rowId));
            
            $selectedLine = current($dataModel->selectPurchaseOrderAndLine(0, 1));
            $purchaseOrderNo = $selectedLine['PURCHASE_ORDER'];
            $purchaseOrderLineNo    =   $selectedLine['PO_LINE'];
            $purchaseOrderLine  =   current($this->minder->getPurchaseOrderLineById($purchaseOrderNo, $purchaseOrderLineNo));
   
        }
        
        $purchaseOrderData      =   $this->minder->getPurchaseOrderById($purchaseOrderNo);
        $purchaseOrderCompanyId =   $purchaseOrderData['COMPANY_ID'];
        
        $this->view->currencyList       =   minder_array_merge(array('AUSTRALIAN DOLLAR' => 'AUSTRALIAN DOLLAR'), $this->minder->getOptionsList('CURRENCY'));        
        $this->view->poLineStatusList   =   minder_array_merge(array('' => ''), $this->minder->getOptionsList('PO_STATUS'));        
        
        $this->view->purchaseOrderNo    =   $purchaseOrderNo;
        $this->view->purchaseOrderLineNo=   $purchaseOrderLineNo;
        
        $this->view->purchaseOrderLine  =   $purchaseOrderLine;
        $this->view->gstRate            =   empty($_POST['gst_rate'])           ? $this->minder->defaultControlValues['DEFAULT_GST_RATE'] : $_POST['gst_rate']; 
        $this->view->poLineStatus       =   empty($_POST['po_line_status'])     ? 'OP'                                                    : $_POST['po_line_status'];    
        $this->view->earliestDate       =   empty($_POST['earliest_date'])      ? date('Y-m-d')                                           : $_POST['po_line_status'];    
        $this->view->poLineDueDate      =   empty($_POST['po_line_due_date'])   ? date('Y-m-d')                                           : $_POST['po_line_status'];    
        $this->view->uomOrderList       =   minder_array_merge(array('' => ''), $this->minder->getUoms());
        $this->view->productList        =   minder_array_merge(array('' => ''), $this->minder->getFilteredByCompnayProdList($purchaseOrderCompanyId));
    
    }
    
    public function saveLineAction() {
        $request = $this->getRequest();
        $rowId = $request->getParam('line', 'new');
        $orderNo = $request->getParam('purchase_order');
        $orderLineNo = $request->getParam('po_line');
        if ($rowId == 'new') {
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
        } else {
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
                                      'last_update_by'          =>  'LAST_UPDATE_BY = ?, '
                                      );
        }
                
        $validate       =   true;
        $validators     =   $this->_getValidators('purchase-line');
        $conditions     =  $this->_setupConditions(null, $allowed);
                
        foreach($conditions as $key => $value){
            $validator  =   isset($validators[$key]) ? $validators[$key] : null;
                    
            if(!is_null($validator) && !$validator->isValid($value)){
                $validate   =   false;
                $this->addError('Field: ' . strtoupper($key) . ' ' . current($validator->getMessages()));    
            }
        }
        if($validate){
                $data       = $this->_makeClause($conditions, $allowed);
                $message    = '';
                
                try{
                    if ($rowId == 'new') {
                        $result     = $this->minder->addPurchaseOrderLine($data);
                        if($result){
                            $this->addMessage('New order line was successfully added.');
                        } else {
                            $this->addError('Error while add order line.');
                            return $this->_forward('line-edit-form');
                        }
                    } else {
                        $result     = $this->minder->updatePurchaseOrderLineByOrderId($data, $orderNo, $orderLineNo);
          
                        if($result){
                            $this->addMessage('Line was successfully updated.');
                        } else {
                            $this->addError('Error while update order line.');
                            return $this->_forward('line-edit-form');
                        }
                    }

                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)->goto('index', 'purchase-order', 'orders');
                } catch(Exception $ex){
                    $message    =   $this->minder->lastError;    
                }
        }
        return $this->_forward('line-edit-form');
    }
    
    public function deleteLinesAction() {
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors   : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();

        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $linesSelectedCount = $rowSelector->getSelectedCount(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            if ($linesSelectedCount < 1) {
                $this->view->errors[] = 'No PO Line selected. Please, select one.';
                return $this->_forward('get-dataset');
            }
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderLine
            */
            $dataModel = $rowSelector->getModel(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $dataModel->addConditions($rowSelector->getSelectConditions(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            
            $selectedLines = $dataModel->selectPurchaseOrderAndLine(0, $linesSelectedCount);
        } catch (Exception $e) {
            $this->view->errors[]   = 'Error while delete PO Lines. ' . $e->getMessage();
            return $this->_forward('get-dataset');
        }
        
        foreach($selectedLines as $line){
            try {
                $orderNo     = $line['PURCHASE_ORDER'];
                $orderLineNo = $line['PO_LINE'];
                
                $purchaseDetailLines = $this->minder->purchaseLineDetailsList(array($orderNo . '_' . $orderLineNo), 0, 1);
            
                if($purchaseDetailLines['total'] == 0){
                    $result = $this->minder->deletePurchaseOrderLine($orderNo, $orderLineNo);
                
                    if(!$result){
                        $this->view->errors[]   = 'Error while delete PO Line #' . $orderLineNo . ' from PURCHASE ORDER #' . $orderNo . '  . ' . $e->getMessage();
                    } else {
                        $this->view->messages[] = 'PO Line #' . $orderLineNo . ' was deleted from PURCHASE ORDER #' . $orderNo . '. ';
                    }    
                } else {
                    $this->view->errors[]   = 'Error while delete PO Line #' . $orderLineNo . ' from PURCHASE ORDER #' . $orderNo . '  . Detail Line exists for this Purchase Order Line.';
                }
        
            } catch (Exception $e) {
                $this->view->errors[]   = 'Error while delete PO Line #' . $orderLineNo . ' from PURCHASE ORDER #' . $orderNo . '  . ' . $e->getMessage();
            }
        }
        return $this->_forward('get-dataset');
    }
//------------ LINES ACTIONS -----------------------------    

//------------ ORDERS ACTIONS ----------------------------    
    protected function getOrderRecord($orderNo) {
        if ($orderNo == 'new') {
            return new PurchaseOrder();
        } else {
            return $this->minder->getPurchaseOrderById($orderNo);
        }
    }
    
    protected function fillOrderFields($orderNo) {
        /**
        * @var PurchaseOrder
        */
        $order      = $this->getOrderRecord($orderNo);
        $isNewOrder = ($orderNo == 'new');
        $lineTotalSum = 0;
        if (!$isNewOrder) {
            /**
            * @var Address
            */
            $supplied_address = current($this->minder->getAddresses('MT', $order->items['PERSON_ID']));
            if (!$supplied_address) {
                $supplied_address = new Address();
            }
            /**
            * @var Person
            */
            $supplied_person  = $this->minder->getPerson($order->items['PERSON_ID']);
            if (is_null($supplied_person))
                $supplied_person = new Person();
            $lineTotalSum = $this->minder->getTotalSumPOlines($orderNo);
            $lineTotalSum = (empty($lineTotalSum)) ? 0 : $lineTotalSum;
        }

        $this->view->id                                 = $orderNo;
         
        //------------------ COMMON FIELDS -------------------------
        $this->view->order_status                       = ($isNewOrder) ? 'UC' : $order->items['PO_STATUS'];
        $this->view->create_date                        = $order->items['PO_DATE'];
        $this->view->revision_no                        = $order->items['PO_REVISION_NO'];
        $this->view->printed_date                       = $order->items['PO_PRINTED'];
        $this->view->po_currency                        = ($isNewOrder) ? 'AUSTRALIAN DOLLAR' : $order->items['PO_CURRENCY'];
        $this->view->due_date                           = $order->items['PO_DUE_DATE'];
        $this->view->cost_centre                        = $order->items['COST_CENTER'];
        $this->view->earliest_date                      = $order->items['EARLIEST_DATE'];
        $this->view->order_entered_by                   = ($isNewOrder) ? $this->minder->userId : $order->items['USER_ID'];
        $this->view->update_date                        = $order->items['LAST_UPDATE_DATE'];
        //------------------ COMMON FIELDS -------------------------
        
        
        //------------------ SUPPLIED BY FIELDS---------------------
        $this->view->supplied_by_existing               = $order->items['PERSON_ID'];
        $this->view->supplied_by_new                    = '';
        $this->view->person_type                        = ($isNewOrder) ? 'CS' : $supplied_person->personType;
        
        $this->view->supplied_first_name                = ($isNewOrder) ? '' : $supplied_address->firstName;
        $this->view->supplied_last_name                 = ($isNewOrder) ? '' : $supplied_address->lastName;
        $this->view->supplied_address1                  = ($isNewOrder) ? '' : $supplied_address->line1;
        $this->view->supplied_address2                  = ($isNewOrder) ? '' : $supplied_address->line2;
        $this->view->address_type                       = ($isNewOrder) ? 'MT' : $supplied_address->type;
        $this->view->supplied_city                      = ($isNewOrder) ? '' : $supplied_address->city;
        $this->view->supplied_state                     = ($isNewOrder) ? '' : $supplied_address->state;
        $this->view->supplied_postcode                  = ($isNewOrder) ? '' : $supplied_address->postcode;
        $this->view->supplied_country                   = ($isNewOrder) ? '' : $supplied_address->country;
        $this->view->supplied_telephone                 = ($isNewOrder) ? '' : $supplied_address->phone;
        $this->view->supplied_contact                   = $order->items['SUPPLIER_CONTACT'];
        //------------------ SUPPLIED BY FIELDS---------------------
        
        //------------------ PURCHASED BY FIELDS--------------------
        $this->view->purchased_send_invoice_to          = ($isNewOrder) ? $this->minder->defaultControlValues['COMPANY_ID'] : $order->items['COMPANY_ID'];
        $this->view->delivery_required_by               = $order->items['PO_RECEIVER'];
        $this->view->purchase_raised_by                 = $order->items['PO_CREATED_BY_NAME'];
        $this->view->delivery_warehouse                 = $order->items['PO_RECEIVE_WH_ID'];
        $this->view->purchase_requisition_no            = $order->items['REQUISITION_NO'];
        $this->view->delivery_to_dock                   = $order->items['PO_RECEIVE_LOCN_ID'];
        $this->view->shipping_address_as_invoice        = false;
        $this->view->send_invoice_to_limited            = '';
        $this->view->invoice_first_name                 = '';
        $this->view->shipping_first_name                = $order->items['PO_SHIP_TO_ADDRESSEE'];
        $this->view->invoice_last_name                  = '';
        $this->view->shipping_last_name                 = $order->items['PO_SHIP_TO_ATTENSION'];
        $this->view->invoice_address1                   = '';
        $this->view->shipping_address1                  = $order->items['PO_SHIP_TO_ADDRESS1'];
        $this->view->invoice_address2                   = '';
        $this->view->shipping_address2                  = $order->items['PO_SHIP_TO_ADDRESS2'];
        $this->view->invoice_city                       = '';
        $this->view->shipping_city                      = $order->items['PO_SHIP_TO_SUBURB'];
        $this->view->invoice_state                      = '';
        $this->view->shipping_state                     = $order->items['PO_SHIP_TO_STATE'];
        $this->view->invoice_postcode                   = '';
        $this->view->shipping_postcode                  = $order->items['PO_SHIP_TO_POSTCODE'];
        $this->view->invoice_country                    = '';
        $this->view->shipping_country                   = $order->items['PO_SHIP_TO_COUNTRY'];
        $this->view->invoice_telephone                  = '';
        $this->view->shipping_telephone                 = $order->items['PO_SHIP_TO_PHONE'];
        //------------------ PURCHASED BY FIELDS--------------------
         
        //------------------ SHIPPING INFORMATION ------------------
        $this->view->ship_via                           = $order->items['PO_SHIP_VIA'];
        $this->view->ship_payment                       = ($isNewOrder) ? 'FOB' : $order->items['PO_SHIPPING_METHOD'];
        $this->view->ship_service                       = $order->items['PO_SHIP_VIA_SERVICE'];
        $this->view->ship_account_no                    = $order->items['PO_SHIPPING_ACCOUNT'];
        //------------------ SHIPPING INFORMATION ------------------
         
        //------------------ IN-TRANSIT DETAILS --------------------
        $this->view->good_receipt_no                    = $order->items['PO_GRN'];
        $this->view->vessel_name                        = $order->items['PO_VESSEL_NAME'];
        $this->view->shipping_container_no              = $order->items['PO_CONTAINER_NO'];
        $this->view->voyage_no                          = $order->items['PO_VOYAGE_NO'];
        //------------------ IN-TRANSIT DETAILS --------------------
         
        //------------------ LEGACY DETAILS ------------------------
        $this->view->legacy_inwards                     = $order->items['PO_LEGACY_CONSIGNMENT'];
        $this->view->legacy_ssn_prefix                  = $order->items['PO_LEGACY_OWNER_ID'];
        $this->view->legacy_warehouse                   = $order->items['PO_LEGACY_RECEIVE_WH_ID'];
        $this->view->legacy_receipt_id                  = $order->items['PO_LEGACY_INTERNAL_ID'];
        $this->view->legacy_status                      = $order->items['PO_LEGACY_STATUS'];
        $this->view->legacy_receipt_date                = $order->items['PO_LEGACY_RECVD_DATE'];
        $this->view->legacy_text                        = $order->items['PO_LEGACY_MEMO'];
        //------------------ LEGACY DETAILS ------------------------
         
        //------------------ PURCHASE COSTS ------------------------
        $this->view->freight                            = $order->items['PO_FREIGHT_COST'];
        $this->view->shipping_container                 = $order->items['PO_CONTAINER_FEES'];
        $this->view->customs_fees                       = $order->items['PO_CUSTOM_FEES'];
        $this->view->container_unloading                = $order->items['PO_UNLOADING_FEES'];
        $this->view->storage_fees                       = $order->items['PO_STORAGE_FEES'];
        $this->view->adminstrative_charges              = $order->items['PO_ADMIN_FEES'];
        $this->view->insurance                          = $order->items['PO_INSURANCE'];
        $this->view->other_charges                      = $order->items['PO_OTHER_FEES'];
        $this->view->tax                                = $order->items['PO_TAX_RATE'];
        $this->view->default_tax_rate                   = $this->minder->defaultControlValues['DEFAULT_GST_RATE'];
        $this->view->tax_cost                           = $order->items['PO_TAX_AMOUNT'];
        $this->view->deposit_paid                       = $order->items['PO_AMOUNT_PAID'];
        $this->view->amount_due                         = $order->items['PO_AMOUNT_DUE'];
        $this->view->line_total_sum                     = $lineTotalSum;
        //------------------ PURCHASE COSTS ------------------------
         
        $this->view->internal_instructions              = $order->items['COMMENTS'];
        $this->view->external_instructions              = $order->items['PO_LINE_EXTERNAL_NOTES'];
    }
    
    protected function calculateTaxCostsAndAmountDue() {
        $freight                            = floatval($this->view->freight);
        $shipping_container                 = floatval($this->view->shipping_container);
        $customs_fees                       = floatval($this->view->customs_fees);
        $container_unloading                = floatval($this->view->container_unloading);
        $storage_fees                       = floatval($this->view->storage_fees);
        $adminstrative_charges              = floatval($this->view->adminstrative_charges);
        $insurance                          = floatval($this->view->insurance);
        $other_charges                      = floatval($this->view->other_charges);
        $line_total_sum                     = floatval($this->view->line_total_sum);
        
        $taxableAmount = $freight 
                            + $shipping_container 
                            + $customs_fees 
                            + $container_unloading 
                            + $storage_fees 
                            + $adminstrative_charges 
                            + $insurance 
                            + $other_charges 
                            + $line_total_sum;

        $tax                                = floatval($this->view->tax);
        $deposit_paid                       = floatval($this->view->deposit_paid);
        
        $this->view->tax_cost                           = $taxableAmount * $tax / 100;
        $this->view->amount_due                         = $taxableAmount + $this->view->tax_cost - $deposit_paid;
    }
    
    protected function overwriteOrderFieldsWithPost() {
        $request = $this->getRequest();
        
        //------------------ COMMON FIELDS -------------------------
        $this->view->order_status                       = $request->getParam('order_status');
        $this->view->create_date                        = $request->getParam('create_date');
        $this->view->revision_no                        = $request->getParam('revision_no');
        $this->view->printed_date                       = $request->getParam('printed_date');
        $this->view->po_currency                        = $request->getParam('po_currency');
        $this->view->due_date                           = $request->getParam('due_date');
        $this->view->cost_centre                        = $request->getParam('cost_centre');
        $this->view->earliest_date                      = $request->getParam('earliest_date');
        $this->view->order_entered_by                   = $request->getParam('order_entered_by');
        $this->view->update_date                        = $request->getParam('update_date');
        //------------------ COMMON FIELDS -------------------------
        
        //------------------ SUPPLIED BY FIELDS---------------------
        $this->view->supplied_by_existing               = $request->getParam('supplied_by_existing');
        $this->view->supplied_by_new                    = strtoupper($request->getParam('supplied_by_new'));
        $this->view->person_type                        = $request->getParam('person_type', 'CS');
        
        $this->view->supplied_first_name                = $request->getParam('supplied_first_name');
        $this->view->supplied_last_name                 = $request->getParam('supplied_last_name');
        $this->view->supplied_address1                  = $request->getParam('supplied_address1');
        $this->view->supplied_address2                  = $request->getParam('supplied_address2');
        $this->view->address_type                       = $request->getParam('address_type', 'MT');
        $this->view->supplied_city                      = $request->getParam('supplied_city');
        $this->view->supplied_state                     = $request->getParam('supplied_state');
        $this->view->supplied_postcode                  = $request->getParam('supplied_postcode');
        $this->view->supplied_country                   = $request->getParam('supplied_country');
        $this->view->supplied_telephone                 = $request->getParam('supplied_telephone');
        $this->view->supplied_contact                   = $request->getParam('supplied_contact');
        //------------------ SUPPLIED BY FIELDS---------------------
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->person_type, $this->view->address_type));
        //------------------ PURCHASED BY FIELDS--------------------
        $this->view->purchased_send_invoice_to          = $request->getParam('purchased_send_invoice_to');
        $this->view->delivery_required_by               = $request->getParam('delivery_required_by');
        $this->view->purchase_raised_by                 = $request->getParam('purchase_raised_by');
        $this->view->delivery_warehouse                 = $request->getParam('delivery_warehouse');
        $this->view->purchase_requisition_no            = $request->getParam('purchase_requisition_no');
        $this->view->delivery_to_dock                   = $request->getParam('delivery_to_dock');
        $this->view->shipping_address_as_invoice        = $request->getParam('shipping_address_as_invoice', false);
        $this->view->send_invoice_to_limited            = $request->getParam('send_invoice_to_limited');
        $this->view->invoice_first_name                 = $request->getParam('invoice_first_name');
        $this->view->shipping_first_name                = $request->getParam('shipping_first_name');
        $this->view->invoice_last_name                  = $request->getParam('invoice_last_name');
        $this->view->shipping_last_name                 = $request->getParam('shipping_last_name');
        $this->view->invoice_address1                   = $request->getParam('invoice_address1');
        $this->view->shipping_address1                  = $request->getParam('shipping_address1');
        $this->view->invoice_address2                   = $request->getParam('invoice_address2');
        $this->view->shipping_address2                  = $request->getParam('shipping_address2');
        $this->view->invoice_city                       = $request->getParam('invoice_city');
        $this->view->shipping_city                      = $request->getParam('shipping_city');
        $this->view->invoice_state                      = $request->getParam('invoice_state');
        $this->view->shipping_state                     = $request->getParam('shipping_state');
        $this->view->invoice_postcode                   = $request->getParam('invoice_postcode');
        $this->view->shipping_postcode                  = $request->getParam('shipping_postcode');
        $this->view->invoice_country                    = $request->getParam('invoice_country');
        $this->view->shipping_country                   = $request->getParam('shipping_country');
        $this->view->invoice_telephone                  = $request->getParam('invoice_telephone');
        $this->view->shipping_telephone                 = $request->getParam('shipping_telephone');
        
        if ($this->view->shipping_address_as_invoice) {
            $this->view->shipping_first_name            = $this->view->invoice_first_name;
            $this->view->shipping_last_name             = $this->view->invoice_last_name;
            $this->view->shipping_address1              = $this->view->invoice_address1;
            $this->view->shipping_address2              = $this->view->invoice_address2;
            $this->view->shipping_city                  = $this->view->invoice_city;
            $this->view->shipping_state                 = $this->view->invoice_state;
            $this->view->shipping_postcode              = $this->view->invoice_postcode;
            $this->view->shipping_country               = $this->view->invoice_country;
            $this->view->shipping_telephone             = $this->view->invoice_telephone;
        }
        
        //------------------ PURCHASED BY FIELDS--------------------
         
        //------------------ SHIPPING INFORMATION ------------------
        $this->view->ship_via                           = $request->getParam('ship_via');
        $this->view->ship_payment                       = $request->getParam('ship_payment');
        $this->view->ship_service                       = $request->getParam('ship_service');
        $this->view->ship_account_no                    = $request->getParam('ship_account_no');
        //------------------ SHIPPING INFORMATION ------------------
         
        //------------------ IN-TRANSIT DETAILS --------------------
        $this->view->vessel_name                        = $request->getParam('vessel_name');
        $this->view->shipping_container_no              = $request->getParam('shipping_container_no');
        $this->view->voyage_no                          = $request->getParam('voyage_no');
        //------------------ IN-TRANSIT DETAILS --------------------
         
        //------------------ PURCHASE COSTS ------------------------
        $this->view->freight                            = $request->getParam('freight', 0);
        $this->view->shipping_container                 = $request->getParam('shipping_container', 0);
        $this->view->customs_fees                       = $request->getParam('customs_fees', 0);
        $this->view->container_unloading                = $request->getParam('container_unloading', 0);
        $this->view->storage_fees                       = $request->getParam('storage_fees', 0);
        $this->view->adminstrative_charges              = $request->getParam('adminstrative_charges', 0);
        $this->view->insurance                          = $request->getParam('insurance', 0);
        $this->view->other_charges                      = $request->getParam('other_charges', 0);

        $this->view->tax                                = $request->getParam('tax', '');
        $supplierCountry                                = strtoupper($this->view->supplied_country);
        if ($supplierCountry == 'AU' || $supplierCountry == 'AUSTRALIA') {
            $this->view->tax                            = empty($this->view->tax) ? $this->minder->defaultControlValues['DEFAULT_GST_RATE'] : $this->view->tax;
        }

        $this->view->tax_cost                           = $request->getParam('tax_cost', 0);
        $this->view->deposit_paid                       = $request->getParam('deposit_paid', 0);
        $this->view->amount_due                         = $request->getParam('amount_due', 0);
        $this->calculateTaxCostsAndAmountDue();
        //------------------ PURCHASE COSTS ------------------------
         
        $this->view->internal_instructions              = $request->getParam('internal_instructions');
        $this->view->external_instructions              = $request->getParam('external_instructions');
    }
    
    protected function fillDropdownOrderFieldsOptions() {
        $this->view->po_status_list                     = $this->minder->getOptionsList('PO_STATUS');
        $this->view->po_currency_list                   = $this->minder->getOptionsList('CURRENCY');
        $this->view->cost_centre_list                   = array_merge(array('' => ''), $this->minder->getCostCentreList());

        $supplied_by_existing_list = $this->minder->getPersonNamesList(array('CO','CS','IN'));
        $supplied_by_existing_list_parsed = array();
        foreach($supplied_by_existing_list as $key=>$val) {
            $supplied_by_existing_list_parsed["$key"] = $val;
        }
        $supplied_by_existing_list = $supplied_by_existing_list_parsed;
        $this->view->supplied_by_existing_list          =  minder_array_merge(array('' => ''), $supplied_by_existing_list);
        
        $this->view->company_list                       = array_merge(array('' => ''), $this->minder->getCompanyListLimited());
        $this->view->warehouse_list                     = array_merge(array('' => ''), $this->minder->getWarehouseListLimited());
        $this->view->delivery_to_dock_list              = $this->minder->getLocationListByClause(array('STORE_AREA = ?' =>  'RC', 'WH_ID = ?' => $this->view->delivery_warehouse));
        
        $sendInvoiceToList   = array();
        $shippingAddressList = array();
        if (!empty($this->view->purchased_send_invoice_to)) {
            $sendInvoiceToList   = $this->getAddressListByType('MT', $this->view->purchased_send_invoice_to);
            $shippingAddressList = $this->getAddressListByType('DT', $this->view->purchased_send_invoice_to);
        }
        
        $this->view->send_invoice_to_limited_list       = array('' => '') + $sendInvoiceToList;
        $this->view->shipping_address_list              = array('' => '') + $shippingAddressList;
        
        $this->view->ship_via_list                      = minder_array_merge(array('' => ''), $this->minder->getShipViaList());
        $this->view->ship_payment_list                  = $this->minder->getOptionsList('SHIP_METH');
        
        $shipServiceList = array('' => '');
        if (!empty($this->view->ship_via)) {
            if (false !== ($carrierService = $this->minder->getCarrierServiceTypes($this->view->ship_via))) 
                foreach ($carrierService as $row) {
                    $shipServiceList[$row['SERVICE_TYPE']] = $row['DESCRIPTION'];
                }
        }
        
        $this->view->ship_service_list                  = $shipServiceList;
    }

    public function orderEditFormAction() {
        if (!$this->minder->isEditable && !$this->minder->isAdmin && !$this->minder->isStatusAdjustPurchaseOrderT())
            $this->_redirector->goto('index');
        
        $id = trim($this->getRequest()->getParam('order'));
        if ($id == 'new') {
            $this->fillOrderFields($id);
        } else {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */
            $orderModel = $rowSelector->getModel(self::ORDERS_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $orderModel->addConditions($orderModel->makeConditionsFromId($id));
            if (count($orderModel) < 1) {
                $this->addError('Order #' . $id . ' not found');
                $id = 'new';
                $this->fillOrderFields($id);
            } else {
                $id = current($orderModel->selectPurchaseOrder(0, 1));
                $this->fillOrderFields($id);
            }
        }
        
        if ($this->getRequest()->isPost()) {
            $this->overwriteOrderFieldsWithPost();
        }
        
        $this->fillDropdownOrderFieldsOptions();
    }
    
    protected function isOrderValid() {
        $valid    = true;
        
        $validator255 = new Zend_Validate_StringLength(0, 255);
        $validator20  = new Zend_Validate_StringLength(0, 20);
        $validator10  = new Zend_Validate_StringLength(0, 10);
        $validator2   = new Zend_Validate_StringLength(0, 2);
        
        if (!$validator10->isValid($this->view->purchase_requisition_no)) {
            $this->addError('REQUISITION_NO value is too long.');
            $valid = false;
        }
            
        if (! $validator10->isValid($this->view->revision_no)) {
            $this->addError('PO_REVISION_NO value is too long.');
            $valid = false;
        }
            
        if (! $validator255->isValid($this->view->internal_instructions)) {
            $this->addError('COMMENTS value is too long.');
            $valid = false;
        }
        
        if (empty($this->view->supplied_by_existing) && empty($this->view->supplied_by_new)) {
            $this->addError('Either select "Supplied By ID(Existing)" or fill in "Supplied By ID(New)".');
            $valid = false;
        }
        
        if (! $validator10->isValid($this->view->supplied_by_existing)) {
            $this->addError('"Supplied By ID(New)" is too long.');
            $valid = false;
        }
        
        if (!empty($this->view->supplied_by_new) && empty($this->view->person_type)) {
            $this->addError('Specify "Person Type" for new Supplier.');
            $valid = false;
        }
        
        if (!empty($this->view->supplied_by_new) && empty($this->view->address_type)) {
            $this->addError('Specify "Address Type" for new Supplier.');
            $valid = false;
        }
        
        if (empty($this->view->purchased_send_invoice_to)) {
            $this->addError('Select "Invoice To ID".');
            $valid = false;
        }
        
        if (empty($this->view->ship_payment)) {
            $this->addError('"Ship Payment" could not be null.');
            $valid = false;
        }
        
        return $valid;
    }
    
    /**
    * Fill order record with passed data
    * 
    * @return PurchaseOrder
    */
    protected function fillOrderRecord() {
        $order = $this->getOrderRecord($this->view->id);
        
        //------------------ COMMON FIELDS -------------------------
        $order->items['PO_STATUS']                  = $this->view->order_status;
        $order->items['PO_DATE']                    = $this->view->create_date;
        $order->items['PO_REVISION_NO']             = $this->view->revision_no;
        $order->items['PO_PRINTED']                 = $this->view->printed_date;
        $order->items['PO_CURRENCY']                = $this->view->po_currency;
        $order->items['PO_DUE_DATE']                = $this->view->due_date;
        $order->items['COST_CENTER']                = $this->view->cost_centre;
        $order->items['EARLIEST_DATE']              = $this->view->earliest_date;
        $order->items['USER_ID']                    = $this->view->order_entered_by;
        $order->items['LAST_UPDATE_DATE']           = date('Y-m-d H:i:s');
        //------------------ COMMON FIELDS -------------------------
        
        //------------------ SUPPLIED BY FIELDS---------------------
        $order->items['PERSON_ID']                  = $this->view->supplied_by_existing;
        $order->items['SUPPLIER_CONTACT']           = $this->view->supplied_contact;
        //------------------ SUPPLIED BY FIELDS---------------------
        
        //------------------ PURCHASED BY FIELDS--------------------
        $order->items['COMPANY_ID']                 = $this->view->purchased_send_invoice_to;
        $order->items['PO_RECEIVER']                = $this->view->delivery_required_by;
        $order->items['PO_CREATED_BY_NAME']         = $this->view->purchase_raised_by;
        $order->items['PO_RECEIVE_WH_ID']           = $this->view->delivery_warehouse;
        $order->items['REQUISITION_NO']             = $this->view->purchase_requisition_no;
        $order->items['PO_RECEIVE_LOCN_ID']         = $this->view->delivery_to_dock;
        $order->items['PO_SHIP_TO_ATTENSION']       = $this->view->shipping_first_name;
        $order->items['PO_SHIP_TO_ADDRESSEE']       = $this->view->shipping_last_name;
        $order->items['PO_SHIP_TO_ADDRESS1']        = $this->view->shipping_address1;
        $order->items['PO_SHIP_TO_ADDRESS2']        = $this->view->shipping_address2;
        $order->items['PO_SHIP_TO_SUBURB']          = $this->view->shipping_city;
        $order->items['PO_SHIP_TO_STATE']           = $this->view->shipping_state;
        $order->items['PO_SHIP_TO_POSTCODE']        = $this->view->shipping_postcode;
        $order->items['PO_SHIP_TO_COUNTRY']         = $this->view->shipping_country;
        $order->items['PO_SHIP_TO_PHONE']           = $this->view->shipping_telephone;
        //------------------ PURCHASED BY FIELDS--------------------
         
        //------------------ SHIPPING INFORMATION ------------------
        $order->items['PO_SHIP_VIA']                = $this->view->ship_via;
        $order->items['PO_SHIPPING_METHOD']         = $this->view->ship_payment;
        $order->items['PO_SHIP_VIA_SERVICE']        = $this->view->ship_service;
        $order->items['PO_SHIPPING_ACCOUNT']        = $this->view->ship_account_no;
        //------------------ SHIPPING INFORMATION ------------------
         
        //------------------ IN-TRANSIT DETAILS --------------------
        $order->items['PO_VESSEL_NAME']             = $this->view->vessel_name;
        $order->items['PO_CONTAINER_NO']            = $this->view->shipping_container_no;
        $order->items['PO_VOYAGE_NO']               = $this->view->voyage_no;
        //------------------ IN-TRANSIT DETAILS --------------------
         
        //------------------ LEGACY DETAILS ------------------------
        $order->items['PO_LEGACY_CONSIGNMENT']      = $this->view->legacy_inwards;
        $order->items['PO_LEGACY_OWNER_ID']         = $this->view->legacy_ssn_prefix;
        $order->items['PO_LEGACY_RECEIVE_WH_ID']    = $this->view->legacy_warehouse;
        $order->items['PO_LEGACY_INTERNAL_ID']      = $this->view->legacy_receipt_id;
        $order->items['PO_LEGACY_STATUS']           = $this->view->legacy_status;
        $order->items['PO_LEGACY_RECVD_DATE']       = $this->view->legacy_receipt_date;
        $order->items['PO_LEGACY_MEMO']             = $this->view->legacy_text;
        //------------------ LEGACY DETAILS ------------------------
         
        //------------------ PURCHASE COSTS ------------------------
        $order->items['PO_FREIGHT_COST']            = $this->view->freight;
        $order->items['PO_CONTAINER_FEES']          = $this->view->shipping_container;
        $order->items['PO_CUSTOM_FEES']             = $this->view->customs_fees;
        $order->items['PO_UNLOADING_FEES']          = $this->view->container_unloading;
        $order->items['PO_STORAGE_FEES']            = $this->view->storage_fees;
        $order->items['PO_ADMIN_FEES']              = $this->view->adminstrative_charges;
        $order->items['PO_INSURANCE']               = $this->view->insurance;
        $order->items['PO_OTHER_FEES']              = $this->view->other_charges;
        $order->items['PO_TAX_RATE']                = $this->view->tax;
        $order->items['PO_TAX_AMOUNT']              = $this->view->tax_cost;
        $order->items['PO_AMOUNT_PAID']             = $this->view->deposit_paid;
        $order->items['PO_AMOUNT_DUE']              = $this->view->amount_due;
        //------------------ PURCHASE COSTS ------------------------
         
        $order->items['COMMENTS']                  = $this->view->internal_instructions;
        $order->items['PO_LINE_EXTERNAL_NOTES']    = $this->view->external_instructions;
        
        return $order;
    }
    
    protected function createNewSupplier () {
        $newContactLine = new ContactLine(array(
            'PERSON_ID'   => $this->view->supplied_by_new,
            'PERSON_TYPE' => $this->view->person_type,
            'FIRST_NAME'  => $this->view->supplied_first_name,
            'LAST_NAME'   => $this->view->supplied_last_name,
            'CREATE_DATE' => '',
            'CREATE_BY'   => ''
        ));
        
        $this->minder->personAdd($newContactLine);
        
        $this->view->supplied_by_existing = $this->view->supplied_by_new;
        
        $this->createSupplierAddress($this->view->address_type);        
    }
    
    protected function isSupplierMailToAddressExists() {
        $addresses = $this->minder->getAddresses('MT', $this->view->supplied_by_existing);
        return count($addresses) > 0;
    }
    
    protected function createSupplierAddress($addressType = 'MT') {
        $newPersonAddress = new AddressLine(array(
            'PERSON_ID' => $this->view->supplied_by_existing,
            'ADDR_TYPE' => $addressType,
            'COMPANY_ID' => '',
            'ADDR_LINE1' => $this->view->supplied_address1,
            'ADDR_LINE2' => $this->view->supplied_address2,
            'ADDR_CITY' => $this->view->supplied_city,
            'ADDR_STATE' => $this->view->supplied_state,
            'ADDR_POST_CODE' => $this->view->supplied_postcode,
            'ADDR_COUNTRY' => $this->view->supplied_country,
            'ADDR_PHONE_NO' => $this->view->supplied_telephone,
            'ADDR_FIRST_NAME' => $this->view->supplied_first_name,
            'ADDR_LAST_NAME' => $this->view->supplied_last_name
        ));
        
        if (false === $this->minder->personAddressAdd($newPersonAddress)) 
            throw new Minder_Exception('Error Creating Supplier Address. ' . $this->minder->lastError);
    }
    
    protected function updateSupplierMailToAddress() {
        /**
        * @var AddressLine
        */
        $addressLine = current($this->minder->getAddressLines(array('ADDR_TYPE' => 'MT', 'PERSON_ID' => $this->view->supplied_by_existing)));
        
        $addressLine->items['PERSON_ID']        = $this->view->supplied_by_existing;
        $addressLine->items['ADDR_TYPE']        = 'MT';
        $addressLine->items['COMPANY_ID']       = '';
        $addressLine->items['ADDR_LINE1']       = $this->view->supplied_address1;
        $addressLine->items['ADDR_LINE2']       = $this->view->supplied_address2;
        $addressLine->items['ADDR_CITY']        = $this->view->supplied_city;
        $addressLine->items['ADDR_STATE']       = $this->view->supplied_state;
        $addressLine->items['ADDR_POST_CODE']   = $this->view->supplied_postcode;
        $addressLine->items['ADDR_COUNTRY']     = $this->view->supplied_country;
        $addressLine->items['ADDR_PHONE_NO']    = $this->view->supplied_telephone;
        $addressLine->items['ADDR_FIRST_NAME']  = $this->view->supplied_first_name;
        $addressLine->items['ADDR_LAST_NAME']   = $this->view->supplied_last_name;
        
        if (false === $this->minder->personAddressUpdate($addressLine)) 
            throw new Minder_Exception('Error Updating Supplier Mail To Address. ' . $this->minder->lastError);
    }
    
    protected function createOrderGrn() {
        $grnBuilder = new Minder_GrnBuilder();

        $grnBuilder->purchaseOrder  = $this->view->id;
        $grnBuilder->carrierId      = $this->view->delivery_warehouse . 'INTRANST';
        $grnBuilder->containerNo    = $this->view->shipping_container_no;
        $grnBuilder->pslOrderQty    = 0;
        $grnBuilder->ownerId        = 'N';
        $grnBuilder->supplierId     = $this->view->supplied_by_existing;
        $grnBuilder->palletOwnerId  = 'U';
        $grnBuilder->crateQty       = 1;
        $grnBuilder->deliveryTypeId = 'IP';
        $grnBuilder->labelQty       = 1;
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $grnBuilder));
        $grnBuilder->nonEmptyContainerNoRequired = true;
        
        $grnBuilder->doBuild();
        
        $this->orderGrn   = $grnBuilder->grn;
        
        $this->addMessage('GRNDI: ' . $grnBuilder->grndiMessage);
        $this->addMessage('GRNDL: ' . $grnBuilder->grndlMessage);

        $this->minder->updateGrn($grnBuilder->grn, 'VESSEL_NAME', $this->view->vessel_name);
        $this->minder->updateGrn($grnBuilder->grn, 'VOYAGE_NO', $this->view->voyage_no);
    }
    
    public function saveOrderAction() {
        $id = trim($this->getRequest()->getParam('order'));
        if ($id == 'new') {
            $this->fillOrderFields($id, true);
        } else {
            try {
                $this->fillOrderFields($id, false);
            } catch (Exception $e) {
                $this->addError($e->getMessage());
                return $this->_forward('order-edit-form');
            }
        }
        
        try {
            $this->overwriteOrderFieldsWithPost();
            
            if (!$this->isOrderValid())
                return $this->_forward('order-edit-form');
                
            if (empty($this->view->supplied_by_existing)) {
                $this->createNewSupplier();
            } else {
                if ($this->isSupplierMailToAddressExists())
                    $this->updateSupplierMailToAddress();
                else 
                    $this->createSupplierAddress('MT');
            }
            
            $orderRecord = $this->fillOrderRecord();
            
            if ($id == 'new') {
                $orderRecord['PURCHASE_ORDER'] = null;
            } else {
                $orderRecord['PURCHASE_ORDER'] = $id;
            }
            
            $orderRecord['itemList'] = array();
            if (false === $this->minder->addPurchaseOrder($orderRecord, false)) {
                throw new Exception('Error creating Purchase Order. ' . $this->minder->lastError);
            }
            
            $this->view->id = $orderRecord['PURCHASE_ORDER'];
            
            if ($id == 'new') {
                $this->addMessage('New Purchase Order created.');
            } else {
                $this->addMessage('Purchase Order #"' . $this->view->id . '" updated.');
            }

        } catch (Exception $e) {
            $this->addError($e->getMessage());
            return $this->_forward('order-edit-form');
        }
        
        try {
            if ($id == 'new') {
                if (!empty($this->view->shipping_container_no) && !empty($this->view->vessel_name)) 
                    $this->createOrderGrn();
            }
        } catch (Exception $e) {
            $this->addError('Error creating GRN for Purchase Order. ' . $e->getMessage());
        }
        
        /**
        * @var Zend_Controller_Action_Helper_Redirector
        */
        $redirector = $this->_helper->Redirector;
        $redirector->gotoSimple('index');
    }

    public function getAddressByTypeAjaxAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $type = trim($this->getRequest()->getParam('id'));
        $jsonObject = new stdClass();
        
        if($type!='') {
            $address    = $this->minder->getAddresses('MT', $type);
            $address    = current($address);
            $jsonObject->address = $address;
        }
            
        echo json_encode($jsonObject);
    }

    public function getDeliveryToDockAjaxAction() {
        $whId = trim($this->getRequest()->getParam('id'));
         
        $jsonObject = new stdClass();
         
        $ids = array(); $names = array();
         
        if ($whId!='') {
            $locations = $this->minder->getLocationListByClause(array(
                'WH_ID = ?'         => $whId,
                'STORE_AREA = ?'    => 'RC'
                ));
       }
         
        $jsonObject->ids = array_keys($locations); //$ids;
        $jsonObject->names = array_values($locations); //$names;
         
        die(json_encode($jsonObject));
    }

    public function getServiceByViaAjaxAction() {
        $carrier_id = trim($this->getRequest()->getParam('id'));
         
        $jsonObject = new stdClass();
         
        $ids = array(); $names = array();
        $services = $this->minder->getShipServiceList($carrier_id);
         
         
        $jsonObject->ids = array_keys($services); //$ids;
        $jsonObject->names = array_values($services); //$names;
         
        die(json_encode($jsonObject));
    }

    public function getAddressByIdAjaxAction() {
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

    protected function getAddressListByType($type, $companyId) {
        $result    = array();
        $addresses = $this->minder->getAddresses($type, $companyId);
        
        foreach ($addresses as $adrObj) {
            $result[$adrObj->recordId] = implode(', ', array($adrObj->line1, $adrObj->line2, $adrObj->city, $adrObj->state, $adrObj->postcode, $adrObj->country));
        }
        
        return $result;
    }
    
    public function getAddressListByTypeAjaxAction() {   
        $company_id = trim($this->getRequest()->getParam('id'));
        $type       = trim($this->getRequest()->getParam('type'));
        
        $jsonObject = new stdClass();
         
        $ids        = array(); 
        $names      = array(); 
        $record_ids = array();
        
        foreach ($this->getAddressListByType($type, $company_id) as $addrId => $addrLine) {
            $ids[]          = $addrId;
            $record_ids[]   = $addrId;
            $names[]        = $addrLine;
        }
         
        $jsonObject->ids        = $ids;
        $jsonObject->names      = $names;
        $jsonObject->record_ids = $record_ids;
         
        die(json_encode($jsonObject));
    }
//------------ ORDERS ACTIONS ----------------------------    

//------------ LINES DETAILS ACTIONS ---------------------    
    public function getLineDetailFormAction(){
        
        $this->view->errors = array();
        
        $recordId = $this->getRequest()->getParam('record-id', 'new');
        
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        
        $this->view->recordId = $recordId;
        $this->view->lineDetails    =   array();
        
        if ($recordId == 'new') {
            if ($rowSelector->getSelectedCount(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController) < 1) {
                $this->view->errors[] = 'No Lines selected. Please select one.';
                return;
            }
            
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderLine
            */
            $dataModel = $rowSelector->getModel(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $dataModel->addConditions($rowSelector->getSelectConditions(self::LINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        } else {
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderSubline
            */
            $dataModel = $rowSelector->getModel(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $dataModel->addConditions($dataModel->makeConditionsFromId($recordId));
            
            if (count($dataModel) < 1) {
                $this->view->errors[] = 'Cannot find Line Detail with RECORD_ID = "' . $recordId . '".';
                return;
            }
            $this->view->recordId = current($dataModel->selectRecordId(0, 1));
            $this->view->lineDetails  = current($dataModel->selectAll(0, 1));
        }

        $selectedLine                = current($dataModel->selectPurchaseOrderAndLine(0, 1));
        $this->view->purchaseOrderNo = $selectedLine['PURCHASE_ORDER'];
        $this->view->purchaseLineNo  = $selectedLine['PO_LINE'];
        $this->view->pslStatusList   = minder_array_merge(array('' => ''), $this->minder->getOptionsList('POL_STATUS'));
    }
    
    public function saveLineDetailsAction() {
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors   : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();
        
        $request                = $this->getRequest();
        $recordId               = $request->getParam('record_id');
        $purchaseOrderNo        = $request->getParam('purchase_order');
        $purchaseOrderLineNo    = $request->getParam('po_line');

        try {
            $purchaseLineData       =   current($this->minder->getPurchaseOrderLineById($purchaseOrderNo, $purchaseOrderLineNo));
            $purchaseOrderLineQty   =   !empty($purchaseLineData['ORIGINAL_QTY']) ? $purchaseLineData['ORIGINAL_QTY'] : 0;
            $detailLinesTotalQty    =   $this->minder->getDetailQtySum($purchaseOrderNo, $purchaseOrderLineNo);
            $newDetailLineQty       =   $request->getParam('psl_order_qty');
            $newDetailLineQty       =   !empty($newDetailLineQty)                 ? $newDetailLineQty                 : 0;
    
            if(($detailLinesTotalQty + $newDetailLineQty) > $purchaseOrderLineQty){
                $this->view->errors[] = 'Total Qty of PO_SUB_LINE\'s must be less or equal to PURCHASE_ORDER_LINE.ORIGINAL_QTY';
                return $this->_forward('get-dataset');
            }  
    
            if ($recordId == 'new') {
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
            } else {
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
            }
                    
            $conditions = $this->_setupConditions(null, $allowed);
            $data       = $this->_makeClause($conditions, $allowed);
    
            if ($recordId == 'new') {
                $data       = array_merge($data, array('USER_ID, ' => $this->minder->userId));  
                $result     = $this->minder->addPurcahseDetailLine($data);
                $message    = $this->minder->lastError;
                if($result){
                    $this->view->messages[] = 'New detail line was successfully added.';
                } else {
                    $this->view->errors[]   = 'Error while add detail line: ' . $message;
                }    
            } else {
                $result     = $this->minder->updatePurchaseDetailLine($recordId, $data);
                $message    = $this->minder->lastError;
                if($result){
                    $this->view->messages[] = 'Detail line was successfully updated.';
                } else {
                    $this->view->errors[]   = 'Error while update detail line: ' . $message;
                }
            }
        } catch (Exception $e) {
            if ($recordId == 'new') {
                $this->view->errors[]   = 'Error while add detail line: ' . $e->getMessage();
            } else {
                $this->view->errors[]   = 'Error while update detail line: ' . $e->getMessage();
            }
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function deleteLineDetailsAction() {
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors   : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();
        
        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $detailsSelectedCount = $rowSelector->getSelectedCount(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            if ($detailsSelectedCount < 1) {
                $this->view->errors[] = 'No Line Details selected. Please, select one.';
                return $this->_forward('get-dataset');
            }
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderSubline
            */
            $dataModel = $rowSelector->getModel(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $dataModel->addConditions($rowSelector->getSelectConditions(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            
            $selectedRecordIds = $dataModel->selectRecordId(0, $detailsSelectedCount);
        } catch (Exception $e) {
            $this->view->errors[]   = 'Error while delete detail lines. ' . $e->getMessage();
            return $this->_forward('get-dataset');
        }
        
        foreach($selectedRecordIds as $recordId){
            try {
                $result = $this->minder->deletePurchaseDetailLine($recordId);
                if(!$result){
                    $this->view->errors[]   = 'Error while delete detail line: ' . $recordId . '. ' . $this->minder->lastError;
                } else {
                    $this->view->messages[] = 'Order detail line: ' . $recordId . ' was successfully deleted.';
                }
            } catch (Exception $e) {
                $this->view->errors[]   = 'Error while delete detail line: ' . $recordId . '. ' . $e->getMessage();
            }
        }
        return $this->_forward('get-dataset');
    }

    public function createIssnsAction() {
        $this->view->errors   = (is_array($this->view->errors))   ? $this->view->errors   : array();
        $this->view->warnings = (is_array($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->messages = (is_array($this->view->messages)) ? $this->view->messages : array();
        
        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $selectedSublinesAmount = $rowSelector->getSelectedCount(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            if ($selectedSublinesAmount < 1) {
                $this->view->warnings[] = 'No line details selected. Select one.';
                return $this->_forward('get-dataset');
            }
            
            /**
            * @var Minder_SysScreen_Model_PurchaseOrderSubline
            */
            $sublineModel = $rowSelector->getModel(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $sublineModel->addConditions($rowSelector->getSelectConditions(self::SUBLINES_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            
            $issnBuilder = new Minder_CreateSsnsFromPurchaseSublines();
            $issnBuilder->doCreate($sublineModel->selectRecordId(0, $selectedSublinesAmount));
            
            $this->view->warnings = array_merge($this->view->warnings, $issnBuilder->getWarnings());
            $this->view->messages = array_merge($this->view->messages, $issnBuilder->getMessages());
        } catch (Exception $e) {
            $this->view->errors[] = 'Error creating ISSNs. ' . $e->getMessage();
        }
        
        return $this->_forward('get-dataset');
    }
//------------ LINES DETAILS ACTIONS ---------------------    
    
    
    
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
    
    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

}
