<?php

class Warehouse_ProductSearchController extends Minder_Controller_Action
{
    const PRODUCT_MODEL = 'PRODUCT_SEARCH';
    const PRODUCT_NAMESPACE = 'PRODUCT_SEARCH_NAMESPACE';
    
    const ISSN_MODEL = 'PRODUCT_SEARCH_ISSN';
    const ISSN_NAMESPACE = 'PRODUCT_SEARCH_ISSN_NAMESPACE';
    
    const PO_MODEL = 'PRODUCT_SEARCH_PO';
    const PO_NAMESPACE = 'PRODUCT_SEARCH_PO_NAMESPACE';
    
    public function indexAction() {
        $this->view->pageTitle = 'SEARCH PRODUCT';

        $this->view->productSsName    = $this->view->searchFormSsName = self::PRODUCT_MODEL;
        $this->view->productNamespace = self::PRODUCT_NAMESPACE;
        
        $this->view->issnSsName       = self::ISSN_MODEL;
        $this->view->issnNamespace    = self::ISSN_NAMESPACE;
        
        $this->view->poSsName         = self::PO_MODEL;
        $this->view->poNamespace      = self::PO_NAMESPACE;
        
        $this->view->errors   = array();
        $this->view->warnings = array();
        $this->view->messages = array();
        
        $request = $this->getRequest();

        
//        $fallback = $request->getParam('fallback');
//        if (!is_array($fallback) || empty($fallback['controller'])) {
//            $this->view->errors[] = 'Direct calls not allowed.';
//            return;
//        }
//        
//        $this->session->productSearch = new stdClass();
//        $this->session->productSearch->fallback = $fallback;
        
        try {
            if(!isset($this->session->productSearch['from'])){
                $this->addError('Direct call not allowed.');
                $this->view->hasErrors = true;
                return;
            }
            
            switch ($this->session->productSearch['from']) {
                case 'pick-order':
                    $this->view->pageTitle .= ': ORDER # = ' . $this->session->productSearch['order_no'];
                    $this->view->cancelAddUrl = $this->view->url(array('controller' => 'pick-order2', 'module' => 'default'), null, true);
                    break;

                case 'transfer-order':
                    $this->view->pageTitle .= ': ORDER # = ' . $this->session->productSearch['order_no'];
                    $this->view->cancelAddUrl = $this->view->url(array('controller' => 'transfer-order', 'module' => 'default'), null, true);
                    break;
                default:
                    $this->addError('Direct call not allowed.');
                    $this->view->hasErrors = true;
                    return;
            }
            
            $pagination           = $this->restorePagination(self::PRODUCT_NAMESPACE);
            $pagination['requiredForOrder'] = $this->session->productSearch['required_qty'];
            $pagination['confirmWithNoProd'] = ($this->minder->defaultControlValues['CONFIRM_WITH_NO_PROD'] == 'T');
            if (!$this->session->defautProductPaginatorSet) {
                $pagination['showBy'] = 15;
                $this->session->defautProductPaginatorSet = true;
            }
            $this->savePagination(self::PRODUCT_NAMESPACE, $pagination);

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::PRODUCT_MODEL);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;
        
            /**
            * @var Minder_SysScreen_Model_ProductSearch
            */
            $productModel = $screenBuilder->buildSysScreenModel(self::PRODUCT_MODEL, new Minder_SysScreen_Model_ProductSearch());
            $productModel->setConditions($productModel->makeConditionsFromSearch($searchFields));
        
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $productModel, true, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $requestedDatasets = array(self::PRODUCT_NAMESPACE => self::PRODUCT_MODEL);

            if ($this->view->isIssnScreenDefined = $screenBuilder->isSysScreenDefined(self::ISSN_MODEL)) {
                $issnModel     = $screenBuilder->buildSysScreenModel(self::ISSN_MODEL);
                $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                $requestedDatasets[self::ISSN_NAMESPACE] = self::ISSN_MODEL;
            }
            
            if ($this->view->isPoScreenDefined = $screenBuilder->isSysScreenDefined(self::PO_MODEL) ){
                $poModel = $screenBuilder->buildSysScreenModel(self::PO_MODEL);
                $rowSelector->setRowSelection('select_complete', 'init', null, null, $poModel, true, self::PO_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                $requestedDatasets[self::PO_NAMESPACE] = self::PO_MODEL;
            }

            $this->initSubDatasets();
            $this->fillProductStatistic(new Minder_ProductStatisticType(true, true));
            $this->getRequest()->setParam('requestedDatasets', $requestedDatasets);
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);
                    
                $this->view->hasErrors = true;
            } else {
                $this->view->productJsDescription = $this->view->jsSearchResult(
                                                        $this->view->productSsName, 
                                                        $this->view->productNamespace, 
                                                        array(
                                                            'sysScreenCaption' => 'SEARCH PRODUCTS',
                                                            'usePagination'    => true,
                                                            'autotabs'         => false
                                                        ));
                $this->view->productJsDataset = $this->view->sysScreens[self::PRODUCT_NAMESPACE];

                if ($this->view->isIssnScreenDefined) {
                    $this->view->issnJsDescription = $this->view->jsSearchResult(
                                                            $this->view->issnSsName, 
                                                            $this->view->issnNamespace, 
                                                            array(
                                                                'sysScreenCaption' => 'ISSN',
                                                                'usePagination'    => true,
                                                                'autotabs'         => false,
                                                                'canSelect'        => false,
                                                                'hasButtons'       => false
                                                            ));
                    $this->view->issnJsDataset = $this->view->sysScreens[self::ISSN_NAMESPACE];
                }

                if ($this->view->isPoScreenDefined) {
                    $this->view->poJsDescription = $this->view->jsSearchResult(
                                                            $this->view->poSsName, 
                                                            $this->view->poNamespace, 
                                                            array(
                                                                'sysScreenCaption' => 'PURCHASE ORDER',
                                                                'usePagination'    => true,
                                                                'autotabs'         => false,
                                                                'canSelect'        => false,
                                                                'hasButtons'       => false
                                                            ));
                    $this->view->poJsDataset = $this->view->sysScreens[self::PO_NAMESPACE];
                }
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }
        
    }
    
    /**
    * @param Minder_ProductStatisticType $productStatisticType
    */
    protected function fillProductStatistic($productStatisticType = null) {
        $pagination           = $this->restorePagination(self::PRODUCT_NAMESPACE);

        if (!is_null($productStatisticType)) {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            /**
            * @var Minder_SysScreen_Model_ProductSearch
            */
            $productModel = $rowSelector->getModel(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            if ($productStatisticType->all) {
                $availableAndOnHandQty = $productModel->getTotalAndOnHandQty();
            
                $pagination['availableQty'] = $availableAndOnHandQty->totalQty;
                $pagination['onHandQty']   = $availableAndOnHandQty->onHandQty;
            }
            
            if ($productStatisticType->selected) {
                if ($rowSelector->getSelectedCount(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController) < 1)
                    $productModel->addConditions(array('1 = 2' => array()));
                else
                    $productModel->addConditions($rowSelector->getSelectConditions(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
                
                $availableAndOnHandQty = $productModel->getTotalAndOnHandQty();
            
                $pagination['selectedAvailableQty'] = $availableAndOnHandQty->totalQty;
                $pagination['selectedOnHandQty']   = $availableAndOnHandQty->onHandQty;
            }
        }
        $this->savePagination(self::PRODUCT_NAMESPACE, $pagination);
    }
    
    public function getDatasetAction() {
        $datasets = $this->getRequest()->getParam('requestedDatasets', array());
        
//        $datasets = array(
//            self::PRODUCT_NAMESPACE   => self::PRODUCT_MODEL
//        );
//        
//        $screenBuilder = new Minder_SysScreen_Builder();
//        if ($screenBuilder->isSysScreenDefined(self::ISSN_MODEL)) {
//            $datasets[self::ISSN_NAMESPACE] = self::ISSN_MODEL;
//        }
//        
//        if ($screenBuilder->isSysScreenDefined(self::PO_MODEL)) {
//            $datasets[self::PO_NAMESPACE] = self::PO_NAMESPACE;
//        }
        
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector   = $this->_helper->getHelper('RowSelector');
        $this->view->sysScreens = array();
        
        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);
        
            if (isset($sysScreens[$namespace]) && isset($sysScreens[$namespace]['paginator'])) {
                $pagination['selectedPage']     = (isset($sysScreens[$namespace]['paginator']['selectedPage']))     ? $sysScreens[$namespace]['paginator']['selectedPage']     : $pagination['selectedPage'];
                $pagination['showBy']           = (isset($sysScreens[$namespace]['paginator']['showBy']))           ? $sysScreens[$namespace]['paginator']['showBy']           : $pagination['showBy'];
                if ($namespace == self::PRODUCT_NAMESPACE)
                    $pagination['requiredForOrder'] = (isset($sysScreens[$namespace]['paginator']['requiredForOrder'])) ? $sysScreens[$namespace]['paginator']['requiredForOrder'] : $pagination['requiredForOrder'];
            }
            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy']);
            $pagination = array_merge($pagination, $this->view->paginator);
            $this->savePagination($namespace, $pagination);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelname, $this->view->dataset, $this->view->selectedRows, $pagination);
            
            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }
    
    protected function initSubDatasets() {
        $screenBuilder = new Minder_SysScreen_Builder();
        
        $issnScreenDefined = $screenBuilder->isSysScreenDefined(self::ISSN_MODEL);
        $poScreenDefined   = $screenBuilder->isSysScreenDefined(self::PO_MODEL);
        
        if (!$issnScreenDefined && !$poScreenDefined) {
            return;
        }
        
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        $selectedProductsAmount = $rowSelector->getSelectedCount(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        
        $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $poModel   = $rowSelector->getModel(self::PO_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedProductsAmount > 0) {
            /**
            * @var Minder_SysScreen_Model_ProductSearch
            */      
            $productModel = $rowSelector->getModel(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $productModel->addConditions($rowSelector->getSelectConditions(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $selectedProducts = $productModel->selectProdId(0, $selectedProductsAmount);
            
            if ($issnScreenDefined) {
                $issnModel->setConditions(array('ISSN.PROD_ID IN (' . substr(str_repeat('?, ', count($selectedProducts)), 0, -2) . ')' => $selectedProducts));
            }
            
            if ($poScreenDefined) {
                $poModel->setConditions(array('PURCHASE_ORDER_LINE.PROD_ID IN (' . substr(str_repeat('?, ', count($selectedProducts)), 0, -2) . ')' => $selectedProducts));
            }
        } else {
            if ($issnScreenDefined) {
                $issnModel->setConditions(array('1 = 2' => array()));
            }
            
            if ($poScreenDefined) {
                $poModel->setConditions(array('1 = 2' => array()));
            }
        }
        
        if ($issnScreenDefined) {
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        }
        
        if ($poScreenDefined) {
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $poModel, true, self::PO_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        }
    }

    public function selectRowAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new stdClass();
        $response->errors       = array();
        $response->warnings     = array();
        $response->messages     = array();
        $response->sysScreens   = array();
        $response->paginator    = array();
        $response->selectedRows = array();
        
        try{
            $sysScreens = $this->getRequest()->getParam('sysScreens', array());
            foreach ($sysScreens as $namespace => $params) {
                $pagination = $this->restorePagination($namespace);
            
                if (isset($sysScreens[$namespace])) {
                    if (isset($sysScreens[$namespace]['paginator'])) {
                        $pagination['selectedPage']  = (isset($sysScreens[$namespace]['paginator']['selectedPage']))  ? $sysScreens[$namespace]['paginator']['selectedPage']  : $pagination['selectedPage'];
                        $pagination['showBy']        = (isset($sysScreens[$namespace]['paginator']['showBy']))        ? $sysScreens[$namespace]['paginator']['showBy']        : $pagination['showBy'];
                        $pagination['selectionMode'] = (isset($sysScreens[$namespace]['paginator']['selectionMode'])) ? $sysScreens[$namespace]['paginator']['selectionMode'] : $pagination['selectionMode'];
                        if ($namespace == self::PRODUCT_NAMESPACE)
                            $pagination['requiredForOrder'] = (isset($sysScreens[$namespace]['paginator']['requiredForOrder'])) ? $sysScreens[$namespace]['paginator']['requiredForOrder'] : $pagination['requiredForOrder'];
                        $this->savePagination($namespace, $pagination);
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
            if ($namespace == self::PRODUCT_NAMESPACE)
                $this->fillProductStatistic(new Minder_ProductStatisticType(false, true));
            $this->getDatasetAction();
            
            $response->sysScreens = $this->view->sysScreens;
            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination, false, true);
            $response->paginator  = array_merge($this->restorePagination($namespace), $this->view->paginator);
            $response->selectedRows = $this->view->selectedRows;
            $this->savePagination($namespace, $response->paginator);
            
            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
            
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        echo json_encode($response);
        return;
    }
    
    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        
        try{
            $pagination                     = $this->restorePagination(self::PRODUCT_NAMESPACE);
            $pagination['requiredForOrder'] = $this->getRequest()->getParam('requiredForOrder', $pagination['requiredForOrder']);
            $this->savePagination(self::PRODUCT_NAMESPACE, $pagination);

            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::PRODUCT_MODEL);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model
            */
            $rowsModel    = $rowSelector->getModel(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->initSubDatasets();
            $this->fillProductStatistic(new Minder_ProductStatisticType(true, true));
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function initAction() {
        // get param from ORDER screen(ADD PRODUCT or ADD Non-Product)
        $productOptions = &$this->session->params['product_options'];
       
        if(isset($productOptions)){
            $this->session->productSearch['from']         = $productOptions->from; 
            $this->session->productSearch['order_no']     = $productOptions->pickOrder; 
            $this->session->productSearch['required_qty'] = $productOptions->qty; 
            $this->session->productSearch['default_price'] = $productOptions->defaultPrice;

            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::PRODUCT_MODEL);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);

            /**
             * @var array $searchFieldDesc
             */
            if($productOptions->thisIs == 'product_code'){
                $searchFieldDesc = $searchKeeper->findSearchFieldByName('PROD_ID', $searchFields);
            } elseif($productOptions->thisIs == 'product_description'){
                $searchFieldDesc = $searchKeeper->findSearchFieldByName('SHORT_DESC', $searchFields);
            }

            if (!is_null($searchFieldDesc)) {
                $this->getRequest()->setParam($searchKeeper->formatSearchFieldId($searchFieldDesc), $productOptions->productName);
            }

            $searchKeeper->makeSearch($searchFields, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            $rowSelector->setRowSelection('select_complete', 'false', null, null, new Minder_SysScreen_Model_ProductSearch(), false, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController); //clear previose selection if was
            
            unset($this->session->params['product_options']);
        }

        return $this->_redirector->gotoSimple('index');
    }
    
    protected function addProducts($qtyParamName, $pickOrderRedirect, $doEmptySession) {
        $pagination                     = $this->restorePagination(self::PRODUCT_NAMESPACE);
        $pagination['requiredForOrder'] = $this->getRequest()->getParam($qtyParamName, $pagination['requiredForOrder']);
        $this->savePagination(self::PRODUCT_NAMESPACE, $pagination);
        
        if ($pagination['requiredForOrder'] < 1)
            throw new Exception('Empty field: Required Qty for Order.');
        
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        $selectedCount = $rowSelector-> getSelectedCount(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedCount < 1)
            throw new Exception('No rows selected.');

        /**
        * @var Minder_SysScreen_Model_ProductSearch $productModel
        */
        $productModel = $rowSelector->getModel(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $productModel->addConditions($rowSelector->getSelectConditions(self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));

        if ($this->session->productSearch['from'] != 'fso') {
            $addRequest = new Minder_AddPickItemRoutine_Request(
                $this->session->productSearch['order_no'],
                $productModel->selectProdId(0, $selectedCount),
                $pagination['requiredForOrder'],
                $this->session->productSearch['default_price']
            );
            $addRequest->addMode = Minder_AddPickItemRoutine_Request::ADD_MODE_FORCE_AVAILABLE;

            $addRoutine = new Minder_AddPickItemRoutine();
            $addRoutineState = $addRoutine->addPickItem($addRequest, new Minder_AddPickItemRoutine_ItemProvider_ProdProfile());

            switch ($addRoutineState->type) {
                case Minder_AddPickItemRoutine_State::STATE_ERROR:
                    $this->addError('Error adding PICK_ITEM: ' . implode('. ', $addRoutineState->errors));
                    $this->_redirector->gotoSimple('index');
                    break;
                case Minder_AddPickItemRoutine_State::STATE_NO_STOCK:
                    $this->addError('Error adding PICK_ITEM: Not enough available stock.');
                    $this->_redirector->gotoSimple('index');
                    break;
                case Minder_AddPickItemRoutine_State::STATE_ITEM_ADDED:
                    $rowSelector->setRowSelection('select_complete', 'false', 0, 0, null, false, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                    $this->addMessage('PICK_ITEMs added to PICK_ORDER #' . $this->session->productSearch['order_no'] . '.');
                    $this->_redirect($pickOrderRedirect);
                    break;
                case Minder_AddPickItemRoutine_State::STATE_ITEM_NOT_FOUND:
                    $this->addError('Error adding PICK_ITEM: ' . implode('. ', $addRoutineState->errors));
                    $this->_redirector->gotoSimple('index');
                    break;
                default:
                    $this->addError('Error. Unexpected Minder_AddPickItemRoutine_State State: "' . $addRoutineState->type . '".');
                    $this->_redirector->gotoSimple('index');
            }

            return;
        } else {
            $data = $productModel->selectProductInfoForAddPickItem(0, $selectedCount);
            $rowSelector->setRowSelection('select_complete', 'false', 0, 0, null, false, self::PRODUCT_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            // ------------------------
            // --- Fast Sales Order ---
            // ------------------------
            $this->session->params[$this->returnOrder]['index']['pick_items'] = array_merge(array('required_qty' => $this->session->productSearch['default_price'], 'case' => $this->session->activeTab), $data);

            if ($doEmptySession)
                unset($this->session->productSearch);
            unset($this->session->productSearch);

            $this->_redirect('pick-order/new/pick_order_type/SO');
        }
    }
    
    public function addAction() {
        try {
            $this->addProducts('required-qty', '/pick-order2', true);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            return $this->_forward('index');
        }
    }
    
    public function addAndContinueAction() {
        try {
            $this->addProducts('required-qty', '/warehouse/product-search', false);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            return $this->_forward('index');
        }
    }
}
