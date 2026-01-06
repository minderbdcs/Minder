<?php

class AuditProcessing2Controller extends Minder_Controller_Action{

    const SSN_MODEL = 'STOCKTAKE';
    const SSN_NAMESPACE = 'STOCKTAKE-AUDIT';

    const LINES_MODEL = 'STOCKTAKE_LINES';
    const LINES_NAMESPACE = 'STOCKTAKE_LINES-AUDIT';

    const LOCN_MODEL = 'STOCKTAKE_LOCATION';
    const LOCN_NAMESPACE = 'STOCKTAKE_LOCATION-AUDIT';

    public function indexAction() {
        try {
            $this->view->ssnModel = self::SSN_MODEL;
            $this->view->ssnNamespace = self::SSN_NAMESPACE;

            $this->view->linesModel = self::LINES_MODEL;
            $this->view->linesNamespace = self::LINES_NAMESPACE;

            $this->view->locnModel = self::LOCN_MODEL;
            $this->view->locnNamespace = self::LOCN_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper  = $this->_helper->getHelper('SearchKeeper');
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            list($searchFields, , , $giSearchFields) = $screenBuilder->buildSysScreenSearchFields(self::SSN_MODEL);

            $searchFields = array_merge($searchFields, $giSearchFields);
            $this->view->searchFields = $searchFields = $searchKeeper->getSearch($searchFields, self::SSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $ssnModel = $screenBuilder->buildSysScreenModel(self::SSN_MODEL, new Minder_SysScreen_Model_Stocktake());
            if (!empty($searchFields))
                $ssnModel->addConditions($ssnModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $ssnModel, true, self::SSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $linesModel = $screenBuilder->buildSysScreenModel(self::LINES_MODEL, new Minder_SysScreen_Model_StocktakeLines());
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $locnModel = $screenBuilder->buildSysScreenModel(self::LOCN_MODEL, new Minder_SysScreen_Model_StocktakeLocation());
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $locnModel, true, self::LOCN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->_initSubDatasets();

            $this->getRequest()->setParam('sysScreens', array(
                self::SSN_NAMESPACE => array(),
                self::LINES_NAMESPACE => array(),
                self::LOCN_NAMESPACE => array()
            ));
            $this->getDatasetAction();

            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->ssnJsSearchResults = $this->view->jsSearchResult(
                self::SSN_MODEL,
                self::SSN_NAMESPACE,
                array('sysScreenCaption' => 'STOCKTAKE', 'usePagination'    => true)
            );
            $this->view->ssnJsSearchResultsDataset = $this->view->sysScreens[self::SSN_NAMESPACE];

            $this->view->linesJsSearchResults = $this->view->jsSearchResult(
                self::LINES_MODEL,
                self::LINES_NAMESPACE,
                array('sysScreenCaption' => 'ISSN List', 'usePagination'    => true)
            );
            $this->view->linesJsSearchResultsDataset = $this->view->sysScreens[self::LINES_NAMESPACE];

            $this->view->locnJsSearchResults = $this->view->jsSearchResult(
                self::LOCN_MODEL,
                self::LOCN_NAMESPACE,
                array('sysScreenCaption' => 'LOCATIONS', 'usePagination'    => true)
            );
            $this->view->locnJsSearchResultsDataset = $this->view->sysScreens[self::LOCN_NAMESPACE];

            $this->view->actionList = minder_array_merge(array(''=>''), $this->minder->getAudActionList());
            $this->view->warehouseList = minder_array_merge(array('' => ''), $this->minder->getWarehouseList());
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            $this->view->hasErrors = true;
        }
    }

    public function getDatasetAction() {
        $datasets = $this->_getModelMap();
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($sysScreens as $namespace => $sysScreenPagination) {
            if (!isset($datasets[$namespace]))
                continue;

            $pagination = $this->fillPagination($this->restorePagination($namespace), $sysScreenPagination);

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'],$pagination);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($datasets[$namespace], $this->view->dataset, $this->view->selectedRows, $this->view->paginator);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new Minder_JSResponse();
        $result->sysScreens = array();
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try{
            foreach ($this->getRequest()->getParam('sysScreens', array()) as $namespace => $sysScreen) {
                $tmpSysScreen = new stdClass();
                $tmpSysScreen->selectedRows = array();
                $tmpSysScreen->selectedRowsTotal = 0;
                $tmpSysScreen->selectedRowsOnPage = 0;

                $pagination = $this->restorePagination($namespace);

                if (isset($sysScreen['paginator'])) {
                    $pagination['selectedPage']  = (isset($sysScreen['paginator']['selectedPage']))  ? $sysScreen['paginator']['selectedPage']  : $pagination['selectedPage'];
                    $pagination['showBy']        = (isset($sysScreen['paginator']['showBy']))        ? $sysScreen['paginator']['showBy']        : $pagination['showBy'];
                    $pagination['selectionMode'] = (isset($sysScreen['paginator']['selectionMode'])) ? $sysScreen['paginator']['selectionMode'] : $pagination['selectionMode'];
                }

                if (isset($sysScreen['rowId']) && isset($sysScreen['state'])) {
                    $rowSelector->setSelectionMode($pagination['selectionMode'], $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                    $rowSelector->setRowSelection($sysScreen['rowId'], $sysScreen['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                }

                $tmpSysScreen->selectedRowsTotal = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($tmpSysScreen->selectedRowsTotal > 0) {
                    $tmpSysScreen->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
                    $tmpSysScreen->selectedRowsOnPage = count($tmpSysScreen->selectedRows);

                }

                $this->_initSubDatasets($namespace);
                $result->sysScreens[$namespace] = $tmpSysScreen;
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        $namespace = $this->getRequest()->getParam('namespace');
        $modelMap = $this->_getModelMap();
        if (!isset($modelMap[$namespace])) return $this->_forward('get-dataset');

        try{
            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields($modelMap[$namespace]);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (!empty($searchFields)) {
                /**
                 * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
                 */
                $rowSelector = $this->_helper->rowSelector;
                /**
                 * @var Minder_SysScreen_Model $rowsModel
                 */
                $rowsModel    = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

                $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

            $this->_initSubDatasets($namespace);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    protected function _initSubDatasets() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        /**
         * @var Minder_sysScreen_Model_StocktakeLines $issnModel
         */
        $issnModel = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $issnConditions = $issnModel->getConditionObject();
        $issnConditions->deleteConditions(Minder_SysScreen_ModelCondition::DEPENDENT_NAMESPACE);

        if ($rowSelector->hasSelected(self::SSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController)) {
            /**
             * @var Minder_SysScreen_Model_Stocktake $ssnModel
             */
            $ssnModel = $rowSelector->getModel(self::SSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $originalSsnConditions = $ssnModel->getConditions();
            $ssnModel->addConditions($rowSelector->getSelectConditions(self::SSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));

            $issnConditions->addConditions(
                $issnModel->makeOriginalSsnCondition($ssnModel->getOriginalSsnIds(0, Minder_SysScreen_Model::MAX_RECORDS)),
                Minder_SysScreen_ModelCondition::DEPENDENT_NAMESPACE
            );
            $ssnModel->setConditions($originalSsnConditions);
        } else {
            $issnConditions->addConditions(
                array('1 = 2' => array()),
                Minder_SysScreen_ModelCondition::DEPENDENT_NAMESPACE
            );
        }

        $issnModel->setConditionObject($issnConditions);
        $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    public function updatePendingAction() {
        $response = new Minder_JSResponse();
        $action = $this->getRequest()->getParam('pending-action', '');

        if (empty($action)) {
            $response->errors[] = 'No Action selected';
        }

        /**
         * @var Minder_Controller_action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try {
            if (!$rowSelector->hasSelected(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController)) {
                $response->errors[] = 'No rows selected.';
            }

            if ($response->hasErrors()) {
                $this->view->errors = array_merge($this->view->errors, $response->errors);
                return $this->_forward('get-dataset');
            }

            /**
             * @var Minder_SysScreen_Model_StocktakeLines $model
             */
            $model = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $model->addConditions($rowSelector->getSelectConditions(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $response = $model->updateStocktakeAction($action, $response);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        $this->view->errors = array_merge($this->view->errors, $response->errors);
        $this->view->messages = array_merge($this->view->messages, $response->messages);
        return $this->_forward('get-dataset');
    }

    public function applyVarianceAction() {
        $response = new Minder_JSResponse();
        $this->view->errors   = is_array($this->view->errors) ? $this->view->errors : array();
        $this->view->messages = is_array($this->view->messages) ? $this->view->messages : array();

        /**
         * @var Minder_Controller_action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try {
            if (!$rowSelector->hasSelected(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController)) {
                $response->errors[] = 'No rows selected.';
            }

            if ($response->hasErrors()) {
                $this->view->errors = array_merge($this->view->errors, $response->errors);
                return $this->_forward('get-dataset');
            }

            /**
             * @var Minder_SysScreen_Model_StocktakeLines $model
             */
            $model = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $model->addConditions($rowSelector->getSelectConditions(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $response = $model->applyVariance($response);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        $this->view->errors = array_merge($this->view->errors, $response->errors);
        $this->view->messages = array_merge($this->view->messages, $response->messages);
        return $this->_forward('get-dataset');
    }

    public function deleteCountAction() {
        $response = new Minder_JSResponse();
        $this->view->errors   = is_array($this->view->errors) ? $this->view->errors : array();
        $this->view->messages = is_array($this->view->messages) ? $this->view->messages : array();

        /**
         * @var Minder_Controller_action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try {
            if (!$rowSelector->hasSelected(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController)) {
                $response->errors[] = 'No rows selected.';
            }

            if ($response->hasErrors()) {
                $this->view->errors = array_merge($this->view->errors, $response->errors);
                return $this->_forward('get-dataset');
            }

            /**
             * @var Minder_SysScreen_Model_StocktakeLines $model
             */
            $model = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $model->addConditions($rowSelector->getSelectConditions(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $response = $model->deleteStocktakeCount($response);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        $this->view->errors = array_merge($this->view->errors, $response->errors);
        $this->view->messages = array_merge($this->view->messages, $response->messages);
        return $this->_forward('get-dataset');
    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    private function _getModelMap() {
        $datasets = array(
            self::SSN_NAMESPACE     => self::SSN_MODEL,
            self::LINES_NAMESPACE   => self::LINES_MODEL,
            self::LOCN_NAMESPACE    => self::LOCN_MODEL
        );
        return $datasets;
    }

    public function freezeLocationAction() {
        $whId   = $this->getRequest()->getParam('wh_id');
        $locnId = $this->getRequest()->getParam('locn_id');

        $response = $this->minder->freezeLocation($whId, $locnId);
        $result = new Minder_JSResponse();

        if ($response !== false) {
            try {
                $result->messages[]         = $response[0];
                $result->data               = $response[1];
                $result->issnsInLocation    = $this->minder->getIssnCountInLocation($whId, $locnId);
                $result->status             = true;
            } catch (Exception $e) {
                $result->errors[] = $e->getMessage();
            }
        } else {
            if (false !== stripos($this->minder->lastError, 'new location created')) {
                $result->warnings[] = $this->minder->lastError;
            } else {
                $result->errors[] = $this->minder->lastError;
            }
            $result->status  = false;
        }

        $this->_helper->json($result);
    }

    public function releaseLocationDirectAction() {
        $response = new Minder_JSResponse();
        $whId   = $this->getRequest()->getParam('wh_id');
        $locnId = $this->getRequest()->getParam('locn_id');

        if (empty($whId)) {
            $response->errors[] = 'Empty Warehouse.';
        }

        if (empty($locnId)) {
            $response->errors[] = 'Empty Location.';
        }

        if ($response->hasErrors()) {
            $this->getResponse()->setBody($this->_helper->json($response, false));
            return;
        }

        $releaseResult = $this->minder->releaseLocation($whId, $locnId);

        if ($releaseResult != false) {
            $response->messages[] = $whId . $locnId . '. ' . $releaseResult[0];
            $response->success = true;
        } else {
            $response->errors[] = $this->minder->lastError;
            $response->success = false;
        }

        $this->getResponse()->setBody($this->_helper->json($response, false));
        return;
    }

    public function saveCountAction() {
        $whId           = $this->getRequest()->getParam('whId');
        $locnId         = $this->getRequest()->getParam('locnId');
        $ssnId          = $this->getRequest()->getParam('ssnId');
        $count          = $this->getRequest()->getParam('count');
        $recount        = $this->getRequest()->getParam('reCount');
        $recountFlag    = intval($this->getRequest()->getParam('recountFlag'));
        $response = new Minder_JSResponse();

        if (empty($whId)) {
            $response->errors[] = 'Empty Warehouse.';
        }

        if (empty($locnId)) {
            $response->errors[] = 'Empty Location.';
        }

        if (!is_numeric($count)) {
            $response->errors[] = 'Empty COUNT.';
        }

        if ($recountFlag && !is_numeric($recount)) {
            $response->errors[] = 'Empty RECOUNT.';
        }

        if (empty($ssnId)) {
            $response->errors[] = 'Empty ISSN.';
        }

        if ($response->hasErrors()) {
            $this->getResponse()->setBody($this->_helper->json($response, false));
            return;
        }

        if ($recountFlag) {
            $result = $this->minder->updateStocktake($whId, $locnId, $ssnId, $recount);
            if (false === $result) {
                $response->errors[] = $this->minder->lastError;
            } else {
                $response->success = 1;
                $response->counted = $recount;
                $response->messages[] = $result[0];
            }
        } else {
            $result = $this->minder->updateStocktake($whId, $locnId, $ssnId, $count, true);

            if (false === $result) {
                $response->errors[] = $this->minder->lastError;
            } else {
                $response->success = 1;
                if (isset($result[2]) && $result[2] == 'RC') {
                    $response->mustRecount = 1;
                    $response->counted = 0;
                    $response->warnings[] = 'Recount.';
                } else {
                    $response->counted = $count;
                    $response->messages[] = $result[0];
                }
            }
        }
        $this->getResponse()->setBody($this->_helper->json($response, false));
    }

    public function lookupAction() {
        $tdata = array();
        $param = $this->getRequest()->getParam('q');
        $whId   = $this->getRequest()->getParam('wh_id');
        $src = $this->getRequest()->getParam('field');

        $log = Zend_Registry::get('logger');
        switch ($src) {
            case 'cl_st_locn_id':
                if ($whId != '') {
                    try {
                        $response = $this->minder->getLocationListByClause(array('LOCN_ID LIKE ?' => trim(strtoupper($param), '%') . '%', 'WH_ID = ?' => $whId));
                        if ($response !== false) {
                            $tdata    = $response;
                        }
                    } catch (Exception $e) {
                        $log->info($e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage());
                    }
                }
                break;
            case 'st_locn_id':
                try {
                    $response = $this->minder->getLocationListByClause(array('LOCN_ID LIKE ?' => trim(strtoupper($param), '%') . '%'));
                    if ($response !== false) {
                        $tdata    = $response;
                    }

                } catch (Exception $e) {
                    $log->info($e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage());
                }
                break;
            case 'st_prod_id':
                try {
                    $response = $this->minder->getProductList($param);
                    if ($response !== false) {
                        $tdata    = $response;
                    }
                } catch (Exception $e) {
                    $log->info($e->getFile() . ' : ' . $e->getLine() . ' : ' . $e->getMessage());
                }
                break;
            default:
                $tdata = array();
                break;
        }

        $this->view->data = $tdata;
    }

    function releaseLocationAction() {
        $response = new Minder_JSResponse();
        $this->view->errors   = is_array($this->view->errors) ? $this->view->errors : array();
        $this->view->messages = is_array($this->view->messages) ? $this->view->messages : array();

        /**
         * @var Minder_Controller_action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try {
            if (!$rowSelector->hasSelected(self::LOCN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController)) {
                $response->errors[] = 'No location selected.';
            }

            if ($response->hasErrors()) {
                $this->view->errors = array_merge($this->view->errors, $response->errors);
                return $this->_forward('get-dataset');
            }

            /**
             * @var Minder_SysScreen_Model_StocktakeLocation $model
             */
            $model = $rowSelector->getModel(self::LOCN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $model->addConditions($rowSelector->getSelectConditions(self::LOCN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $response = $model->releaseLocations($response);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        $this->view->errors = array_merge($this->view->errors, $response->errors);
        $this->view->messages = array_merge($this->view->messages, $response->messages);
        return $this->_forward('get-dataset');
    }
}