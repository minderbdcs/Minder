<?php
class Warehouse_Ssn2Controller extends Minder_Controller_Action 
{
    
    public static $ssnScreen        = 'SSN';
    public static $nonProductScreen = 'PICK_ORDER_SSN';
    
    public static $ssnRowSelAction     = 'select-rows';
    public static $ssnRowSelController = 'service';
    public static $ssnRowSelModule     = 'default';
    
    public static $npsRowSelAction     = 'select-rows-simple';
    public static $npsRowSelController = 'ssn2';
    public static $npsRowSelModule     = 'warehouse';
    
    
    public function init() {
        parent::init();
        
        $this->view->ssnSysScreenName = self::$ssnScreen;
        $this->view->pageTitle        = 'SSN';
        
        $this->view->issnSysScreenName = self::$nonProductScreen;

        $this->view->reportModule     = 'default';
        $this->view->reportController = 'service';
        $this->view->reportAction     = 'report';
        
    }
    
    public function indexAction() {
        try {
            $searchKeeper = $this->_helper->searchKeeper;
            $request = $this->getRequest();
            
            $screenBuilder      = new Minder_SysScreen_Builder();
            list($searchFields, $searchActions, $searchTabs) = $screenBuilder->buildSysScreenSearchFields(self::$ssnScreen);
            
            $action = $request->getParam('SEARCH_FORM_ACTION', 'none');
            
            switch (strtolower($action)) {
                case 'search':
                    $searchFields = $searchKeeper->makeSearch($searchFields, self::$ssnScreen);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields, self::$ssnScreen);
            }
            

            $session = new Zend_Session_Namespace();
            $tz_from=$session->BrowserTimeZone;


$array_new=$searchFields;

foreach ($array_new as $key => $value) { 
            foreach($value as $key1=>$val1){
             
                if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') { unset($array_new[$key]); }
            
                
            }    

}

            
            
            foreach($array_new as $key=>$val){

                                foreach($val as $key1=>$val1){

                                                    if($key1=='SEARCH_VALUE' && $val1!=''){


                                                            if (DateTime::createFromFormat('Y-m-d H:i:s', $val1) !== FALSE  || DateTime::createFromFormat('Y-m-d', $val1) !== FALSE) {


                                                                                    $datetimet = $val1;
                                                                                    $tz_tot = 'UTC';
                                                                                    $format = 'Y-m-d h:i:s';

                                                                                    $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                                                                                    $dtt->setTimeZone(new DateTimeZone($tz_tot));
                                                                                  
                                                                                    $searchFields[$key][$key1]=$dtt->format($format);

                                                                }                 

                                                    }


                                }

            }



            $ssnModel = $screenBuilder->buildSysScreenModel(self::$ssnScreen, new Minder_SysScreen_Model_Ssn());
            $ssnModel->setConditions($ssnModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector  = $this->_helper->rowSelector;
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $ssnModel, true, self::$ssnScreen, self::$ssnRowSelAction, self::$ssnRowSelController);
            
            $this->_preProcessNavigation();
            $totalSsns = count($ssnModel);
            $this->_postProcessNavigation(array('total' => $totalSsns));

            $pageSelector = $this->view->navigation['pageselector'];
            $showBy       = $this->view->navigation['show_by'];
            

            $this->view->searchFields     = $searchFields;
            $this->view->searchActions    = $searchActions;
            $this->view->searchTabs       = $searchTabs;
            
            $this->view->ssns              = $ssnModel->getItems($pageSelector * $showBy, $showBy);
            $this->view->totalSsns         = $totalSsns;
            $this->view->selectedSsns      = $rowSelector->getSelected($pageSelector, $showBy, true, self::$ssnScreen, self::$ssnRowSelAction, self::$ssnRowSelController);
            $this->view->selectedSsnsCount = $rowSelector->getSelectedCount(self::$ssnScreen, self::$ssnRowSelAction, self::$ssnRowSelController);
            
            list(
                $this->view->fields, 
                $this->view->tabs, 
                $this->view->colors, 
                $this->view->actions
            )                             = $screenBuilder->buildSysScreenSearchResult(self::$ssnScreen);
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->ssns));
            $this->view->rowSelectionNamespace  = self::$ssnScreen;
            $this->view->rowSelectionAction     = self::$ssnRowSelAction;
            $this->view->rowSelectionController = self::$ssnRowSelController;
            $this->view->rowSelectionModule     = self::$ssnRowSelModule;
            
            $this->view->selectMode             = $rowSelector->getSelectionMode('', self::$ssnScreen, self::$ssnRowSelAction, self::$ssnRowSelController);
            
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function searchNonProductAction() {
        try {
            $this->view->makeSearch = true;
            
            // get param from ORDER screen(ADD PRODUCT ot ADD Non-Product)
            if(isset($this->session->params['non_product_options'])){
                $this->session->from['pick_order']      =   $this->session->params['non_product_options']->pickOrder;
                $this->session->from['wh_id']           =   $this->session->params['non_product_options']->whId;
                $this->session->from['default_price']   =   $this->session->params['non_product_options']->defaultPrice;
                $this->session->from['qty']             =   $this->session->params['non_product_options']->qty;
                $this->session->from['from']            =   $this->session->params['non_product_options']->from;
                
                $this->view->makeSearch = $this->session->params['non_product_options']->makeSearch;
                 
                if($this->session->params['non_product_options']->thisIs == 'description'){
                    $this->getRequest()->setParam('SEARCH-SSN-SSN_DESCRIPTION', $this->session->params['non_product_options']->nonProductName);    
                    $this->getRequest()->setParam('SEARCH-SSN-SSN_ID', '');    
                } elseif($this->session->params['non_product_options']->thisIs == 'issn') {
                    $this->getRequest()->setParam('SEARCH-SSN-SSN_ID', $this->session->params['non_product_options']->nonProductName);    
                    $this->getRequest()->setParam('SEARCH-SSN-SSN_DESCRIPTION', '');    
                }
               
               $this->getRequest()->setParam('SEARCH_FORM_ACTION', 'search');
               
               unset($this->session->params['non_product_options']);
            } else {
                if (isset($this->session->notAddedQty))
                    $this->session->from['qty'] = $this->session->notAddedQty;
            }
            if (isset($this->session->notAddedQty))
                unset($this->session->notAddedQty);

            $pickOrderNo = $this->session->from['pick_order'];

            $this->view->pageTitle = 'Search SSN: ORDER # = ' . $pickOrderNo;
            $searchKeeper = $this->_helper->searchKeeper;
            $request = $this->getRequest();
            
            $this->view->requiredQty = $this->session->from['qty'];
            
            $screenBuilder      = new Minder_SysScreen_Builder();
            list($searchFields, $searchActions, $searchTabs) = $screenBuilder->buildSysScreenSearchFields(self::$nonProductScreen);
            
            $action = $request->getParam('SEARCH_FORM_ACTION', 'none');
            
            switch (strtolower($action)) {
                case 'search':
                    $searchFields = $searchKeeper->makeSearch($searchFields, self::$nonProductScreen);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields, self::$nonProductScreen);
            }
            
            $this->view->searchFields     = $searchFields;
            $this->view->searchActions    = $searchActions;
            $this->view->searchTabs       = $searchTabs;

            /**
             * @var Minder_SysScreen_Model_PickOrderSsn $model
             */
            $model = $screenBuilder->buildSysScreenModel(self::$nonProductScreen, new Minder_SysScreen_Model_PickOrderSsn());
            $model->initServiceFields();
            $model->setConditions($model->makeConditionsFromSearch($searchFields));
            
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector  = $this->_helper->rowSelector;
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $model, true, self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
            
            $addedIssns           = $this->minder->getPickOrderIssns($pickOrderNo);
            $this->view->notSelectableRows = array();
            if (!empty($addedIssns)) {
                $addedIssnsConditions = array("SSN.SSN_ID IN (" . substr(str_repeat('?, ', count($addedIssns)), 0, -2) . ")" => $addedIssns);
                $model->addConditions($addedIssnsConditions);
                
                $rowsTotal = count($model);
                $this->view->notSelectableRows = $model->getItems(0, $rowsTotal, true);
                $rowSelector->setRowSelection('select_all', 'false', 0, $rowsTotal, $model, false, self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
                $model->removeConditions($addedIssnsConditions);
            }
            
            if ($this->view->makeSearch) {
                
                $this->_preProcessNavigation();
                $totalSsns = count($model);
                $this->_postProcessNavigation(array('total' => $totalSsns));
    
                $pageSelector = $this->view->navigation['pageselector'];
                $showBy       = $this->view->navigation['show_by'];
                
                $this->view->ssns              = $model->getItems($pageSelector * $showBy, $showBy);
                $this->view->totalSsns         = $totalSsns;
                $this->view->selectedSsns      = $rowSelector->filterSelectedRowsLegacy($this->view->ssns, self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
                $this->view->selectedSsnsCount = $rowSelector->getSelectedCount(self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
                
                $this->view->summarySelected   = $this->view->summaryTotal = $model->selectSummary();
                $this->view->summaryTotal['NOT_SELECTABLE_ROWS_AMOUNT'] = count($this->view->notSelectableRows);
                foreach ($this->view->summarySelected as &$summary) $summary = 0;
                
                if ($this->view->selectedSsnsCount > 0) {
                    $model->addConditions($rowSelector->getSelectConditions(self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController));
                    $this->view->summarySelected = $model->selectSummary();
                }
                
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->summarySelected, $this->view->summaryTotal));
                
                list(
                    $this->view->fields, 
                    $this->view->tabs, 
                    $this->view->colors, 
                    $this->view->actions
                )                             = $screenBuilder->buildSysScreenSearchResult(self::$nonProductScreen);
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->ssns));
                $this->view->rowSelectionNamespace  = self::$nonProductScreen;
                $this->view->rowSelectionAction     = self::$npsRowSelAction;
                $this->view->rowSelectionController = self::$npsRowSelController;
                $this->view->rowSelectionModule     = self::$npsRowSelModule;
                
                $this->view->selectMode             = $rowSelector->getSelectionMode('', self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
            
            }
            
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
    }
    
    public function cancelAddAction() {
        $rowSelector  = $this->_helper->rowSelector;
        $rowSelector->setRowSelection('select_complete', 'false', null, null, null, false, self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
        unset($this->session->from);
        $this->_redirect($this->session->returnOrder . '/index');
    }
    
    public function addNonProductAction() {
        try {
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector  = $this->_helper->rowSelector;
            if ($rowSelector->getSelectedCount(self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController) < 1) {
                $this->addError('No items selected. Please select one.');
                $this->_forward('search-non-product');
                return;
            }

            /**
             * @var Minder_SysScreen_Model_PickOrderSsn $model
             */
            $model = $rowSelector->getModel(self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
            $model->addConditions($rowSelector->getSelectConditions(self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController));
            if ($this->session->from['from'] != 'fso') {

                $addRequest = new Minder_AddPickItemRoutine_Request(
                    $this->session->from['pick_order'],
                    $model->selectSsnId(0, count($model)),
                    $this->session->from['qty'],
                    $this->session->from['default_price'],
                    Minder_AddPickItemRoutine_Request::ADD_MODE_FORCE_AVAILABLE
                );
                $addRoutine = new Minder_AddPickItemRoutine();
                $addRoutineState = $addRoutine->addPickItem($addRequest, new Minder_AddPickItemRoutine_ItemProvider_Issn());

                switch ($addRoutineState->type) {
                    case Minder_AddPickItemRoutine_State::STATE_ERROR:
                        $this->addError('Error adding PICK_ITEM: ' . implode('. ', $addRoutineState->errors));
                        $this->_redirector->gotoSimple('search-non-product');
                        break;
                    case Minder_AddPickItemRoutine_State::STATE_NO_STOCK:
                        $this->addError('Error adding PICK_ITEM: Not enough available stock.');
                        $this->_redirector->gotoSimple('search-non-product');
                        break;
                    case Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED:
                        $rowSelector->setRowSelection('select_complete', 'false', 0, 0, null, false, self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
                        $this->addMessage('PICK_ITEMs added to PICK_ORDER #' . $this->session->from['order_no'] . '.');
                        $this->addWarning($addRoutineState->errors);

                        if ($this->getRequest()->getParam('add', 'add') == 'add_and_continue')
                            $this->_redirect('warehouse/ssn2/search-non-product/');
                        else
                            $this->_redirect($this->session->returnOrder);

                        break;
                    case Minder_AddPickItemRoutine_State::STATE_ITEM_NOT_FOUND:
                        $this->addError('Error adding PICK_ITEM: ' . implode('. ', $addRoutineState->errors));
                        $this->_redirector->gotoSimple('search-non-product');
                        break;
                    default:
                        $this->addError('Error. Unexpected Minder_AddPickItemRoutine_State State: "' . $addRoutineState->type . '".');
                        $this->_redirector->gotoSimple('search-non-product');
                }

            } else {
                $rowSelector->setRowSelection('select_complete', 'false', null, null, null, false, self::$nonProductScreen, self::$npsRowSelAction, self::$npsRowSelController);
                // ------------------------
                // --- Fast Sales Order ---
                // ------------------------
                if (!isset($this->session->params[$this->session->returnOrder]['index']['pick_items'])) {
                    $this->session->params[$this->session->returnOrder]['index']['pick_items'] = array();
                }
                $this->session->params[$this->session->returnOrder]['index']['pick_items'] = array_merge($this->session->params[$this->session->returnOrder]['index']['pick_items'], $model->getIssnDataForPickItem());

                if ($this->getRequest()->getParam('add', 'add') == '') {
                    $this->_redirect('warehouse/products/index'
                        . (isset($this->session->from['from']) ? '/from/' . $this->session->from['from'] : ''));

                } else {
                    if($this->$this->session->returnOrder == 'transfer-order') {
                        $this->_redirect( $this->session->returnOrder . '/new/pick_order_type/TO');
                    } else {
                        $this->_redirect( $this->session->returnOrder . '/new/pick_order_type/SO');
                    }
                }
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            $this->_redirector->gotoSimple('search-non-product');
        }
    }
    
    public function selectRowsAction() {
        $request = $this->getRequest();
        
        $response = new stdClass();
        $response->errors             = array();
        $response->warnings           = array();
        $response->messages           = array();
        $response->selected           = 0;
        $response->selectedRows       = array();
        $response->rowId              = null;
        $response->selectionNamespace = 'default';
        
        $response->totalSummary       = array();
        $response->selectedSummary    = array();
        
        $showBy       = $request->getParam('show_by');
        $pageselector = $request->getParam('pageselector');
        $rowId        = $request->getParam('row_id');
        $state        = $request->getParam('state', 'init');
        
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionAction     = $request->getParam('selection_action');
        $selectionController = $request->getParam('selection_controller');
        $selectionMode       = $request->getParam('selection_mode');
        
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector  = $this->_helper->getHelper('RowSelector');
        
        try {
            $rowSelector->setSelectionMode($selectionMode, $selectionNamespace, $selectionAction, $selectionController);
            $rowSelector->setRowSelection($rowId, $state, $pageselector, $showBy, null, false, $selectionNamespace, $selectionAction, $selectionController);
            $rowsModel = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
            

            $pickOrderNo = $this->session->from['pick_order'];
            $addedIssns  = $this->minder->getPickOrderIssns($pickOrderNo);
            $this->view->notSelectableRows = array();
            if (!empty($addedIssns)) {
                $addedIssnsConditions = array("SSN.SSN_ID IN (" . substr(str_repeat('?, ', count($addedIssns)), 0, -2) . ")" => $addedIssns);
                $rowsModel->addConditions($addedIssnsConditions);
                $rowsTotal = count($rowsModel);
                $rowSelector->setSelectionMode('all', $selectionNamespace, $selectionAction, $selectionController);
                $rowSelector->setRowSelection('select_all', 'false', 0, $rowsTotal, $rowsModel, false, $selectionNamespace, $selectionAction, $selectionController);
                $rowSelector->setSelectionMode($selectionMode, $selectionNamespace, $selectionAction, $selectionController);
                $rowsModel->removeConditions($addedIssnsConditions);
            }
            
            $response->selected           = $rowSelector->getSelectedCount($selectionNamespace, $selectionAction, $selectionController);
            $response->selectedRows       = $rowSelector->getSelected($pageselector, $showBy, true, $selectionNamespace, $selectionAction, $selectionController);
            $response->rowId              = $rowId;
            $response->selectionNamespace = $selectionNamespace;

            $response->selectedSummary = $response->totalSummary = $rowsModel->selectSummary();
            foreach ($response->selectedSummary as &$summary) $summary = 0;
            
            if ($response->selected > 0) {
                $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
                $response->selectedSummary = $rowsModel->selectSummary();
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

    public function selectRowsSimpleAction() {
        $request = $this->getRequest();

        $response = new stdClass();
        $response->errors             = array();
        $response->warnings           = array();
        $response->messages           = array();

        $showBy       = $request->getParam('show_by');
        $pageselector = $request->getParam('pageselector');
        $rowId        = $request->getParam('row_id');
        $state        = $request->getParam('state', 'init');

        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionAction     = $request->getParam('selection_action');
        $selectionController = $request->getParam('selection_controller');
        $selectionMode       = $request->getParam('selection_mode');

        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector  = $this->_helper->getHelper('RowSelector');

        try {
            $rowSelector->setSelectionMode($selectionMode, $selectionNamespace, $selectionAction, $selectionController);
            $rowSelector->setRowSelection($rowId, $state, $pageselector, $showBy, null, false, $selectionNamespace, $selectionAction, $selectionController);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

    /*protected function _setupShortcuts() {
        $minderNavigation = Minder_Navigation::getNavigationInstance('warehouse');

        if ($this->getRequest()->getParam('action') == 'search-non-product') {
            $ssnPage = $minderNavigation->findOneBy('name', 'ssn');
            if (!is_null($ssnPage)) $ssnPage->setVisible(false);

            $ssnSearchPage = $minderNavigation->findOneBy('name', 'ssn_search');
            if (!is_null($ssnSearchPage)) $ssnSearchPage->setVisible(true);
        }

        $this->view->shortcuts = $minderNavigation->buildMinderMenuArray('warehouse');

        return $this;
    }*/
}
