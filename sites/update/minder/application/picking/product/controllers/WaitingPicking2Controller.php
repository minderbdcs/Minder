<?php

class Picking_WaitingPicking2Controller extends Minder_Controller_Action_Picking {

    const ORDER_MODEL_NAME = 'WAITPICKING';
    const ORDER_NAMESPACE  = 'PICKING2-WAITPICKING';

    const LINES_MODEL_NAME = 'WAITPICKINGLINES';
    const LINES_NAMESPACE  = 'PICKING2-WAITPICKINGLINES';

    const LIMITS_MODEL_NAME = 'ALLOCATELIMITS';
    const LIMITS_INSTANCE_ID = 'wait_picking_allocating_limits';

    const UNDISPATCHABLE_ITEMS = 'UNDISPATCHABLE_ITEMS';

    const DEVICE_STATISTICS_MODEL = 'WAIT_PICKING_DEVICE_STAT';
    const DEVICE_STATISTICS_NAMESPACE = 'WP-WAIT_PICKING_DEVICE_STAT';

    public function init()
    {
        parent::init();
        $this->view->pageTitle = 'Waiting Picking';
    }

    /**
     * @param Minder_SysScreen_Builder $screenBuilder
     * @param Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
     * @return array
     */
    protected function _getLinesSearchFields($screenBuilder, $searchKeeper)
    {
        list($linesSearchFields, , , $linesGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::LINES_MODEL_NAME);

        $linesSearchFields = array_merge($linesSearchFields, $linesGISearchFields);
        $linesSearchFields = $searchKeeper->getSearch($linesSearchFields, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        return $linesSearchFields;
    }

    /**
     * @param Minder_SysScreen_Builder $screenBuilder
     * @param Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
     * @return array
     */
    protected function _getOrderSearchFields($screenBuilder, $searchKeeper)
    {
        list($orderSearchFields, , , $orderGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ORDER_MODEL_NAME);

        $orderSearchFields = array_merge($orderSearchFields, $orderGISearchFields);
        $orderSearchFields = $searchKeeper->getSearch($orderSearchFields, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        return $orderSearchFields;
    }


    /**
     * @param Minder_SysScreen_Builder $screenBuilder
     * @return array
     */
    protected function _getScreenOrder($screenBuilder) {
        function sortScreens($a, $b) {
            return $a['order'] - $b['order'];
        }

        $screenOrder = array();

        if (null !== ($tmpOrder = $screenBuilder->getSysScreenOrder(self::ORDER_MODEL_NAME))) {
            $screenOrder[] = array('name' => self::ORDER_MODEL_NAME, 'order' => $tmpOrder);
        }

        if (null !== ($tmpOrder = $screenBuilder->getSysScreenOrder(self::LINES_MODEL_NAME))) {
            $screenOrder[] = array('name' => self::LINES_MODEL_NAME, 'order' => $tmpOrder);
        }

        if (null !== ($tmpOrder = $screenBuilder->getSysScreenOrder(self::LIMITS_MODEL_NAME))) {
            $screenOrder[] = array('name' => self::LIMITS_MODEL_NAME, 'order' => $tmpOrder);
        }

        usort($screenOrder, 'sortScreens');

        return $screenOrder;
    }

    public function indexAction() {
        try {
            $this->view->orderSsName = $this->view->orderSearchForm = self::ORDER_MODEL_NAME;
            $this->view->orderNamespace = self::ORDER_NAMESPACE;

            $this->view->linesSsName = $this->view->linesSearchForm = self::LINES_MODEL_NAME;
            $this->view->linesNamespace = self::LINES_NAMESPACE;

            $this->view->limitsSsName = self::LIMITS_MODEL_NAME;
            $this->view->limitsInstanceId = self::LIMITS_INSTANCE_ID;

            $rootSysScreens = array();
            $this->view->masterSlaveChain = $this->_buildMasterSlaveChain(array_values($this->_getModelMap()), $rootSysScreens);

            $screenBuilder                 = new Minder_SysScreen_Builder();
            $this->view->screenOrder       = $this->_getScreenOrder($screenBuilder);

            $this->view->orderSearchFields = $orderSearchFields = $this->_getOrderSearchFields($screenBuilder, $this->_helper->searchKeeper);
            $this->view->linesSearchFields = $linesSearchFields = $this->_getLinesSearchFields($screenBuilder, $this->_helper->searchKeeper);

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_WaitingPicking $orderModel
             */
            $orderModel = $screenBuilder->buildSysScreenModel(self::ORDER_MODEL_NAME, new Minder_SysScreen_Model_WaitingPicking());
            $orderModel->setProdAllocateLimit($this->_allocateLimit()->getProductLimit(self::LIMITS_INSTANCE_ID));
            if (!empty($orderSearchFields))
                $orderModel->addConditions($orderModel->makeConditionsFromSearch($orderSearchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $orderModel, true, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            /**
             * @var Minder_SysScreen_Model_WaitingPickingLine $linesModel
             */
            $linesModel = $screenBuilder->buildSysScreenModel(self::LINES_MODEL_NAME, new Minder_SysScreen_Model_WaitingPickingLine());
            if (!empty($linesSearchFields))
                $linesModel->addConditions($linesModel->makeConditionsFromSearch($linesSearchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->initSubDatasets();

            $this->getRequest()->setParam('sysScreens', array(self::ORDER_NAMESPACE => array(), self::LINES_NAMESPACE => array()));
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->orderJsSearchResults = $this->view->jsSearchResult(
                                                    self::ORDER_MODEL_NAME,
                                                    self::ORDER_NAMESPACE,
                                                    array('sysScreenCaption' => 'ORDERS LIST', 'usePagination'    => true)
            );
            $this->view->orderJsSearchResultsDataset = $this->view->sysScreens[self::ORDER_NAMESPACE];
            $this->view->undispatchableOrders        = $this->view->sysScreens[self::ORDER_NAMESPACE][static::UNDISPATCHABLE_ITEMS];

            $this->view->linesJsSearchResults = $this->view->jsSearchResult(
                                                    self::LINES_MODEL_NAME,
                                                    self::LINES_NAMESPACE,
                                                    array('sysScreenCaption' => 'ITEMS LIST', 'usePagination'    => true)
            );
            $this->view->linesJsSearchResultsDataset = $this->view->sysScreens[self::LINES_NAMESPACE];

            $pickModes = $this->minder->getPickModes();
            $this->view->pickModes = $pickModes['data'];
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        $this->view->sysScreens = array();
        $this->view->deviceStatisticsResults = $this->_getJsSearchResultBuilder()->buildEmptyResult(static::DEVICE_STATISTICS_MODEL, static::DEVICE_STATISTICS_NAMESPACE);
        try {
            $deviceStatisticsModel = $this->_getModelBuilder()->buildSysScreenModel(static::DEVICE_STATISTICS_MODEL);
            $this->_rowSelector()->setRowSelection('select_complete', 'init', null, null, $deviceStatisticsModel, true, static::DEVICE_STATISTICS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->deviceStatisticsResults = $this->_getJsSearchResultBuilder()->buildScreenSearchResult(static::DEVICE_STATISTICS_MODEL, static::DEVICE_STATISTICS_NAMESPACE);
            $this->getRequest()->setParam('sysScreens', array(
                self::DEVICE_STATISTICS_NAMESPACE=> array(),
            ));
            $this->view->sysScreens = $this->_buildDatatset(array(static::DEVICE_STATISTICS_MODEL => static::DEVICE_STATISTICS_NAMESPACE));
        } catch (Exception $e) {
            $this->addError($e->getMessage() . ' Device Statistics dialog will not work.');
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
            

	          $session = new Zend_Session_Namespace();
            $tz_to=$session->BrowserTimeZone;


            $key_array=array_keys($this->view->dataset);



 foreach($this->view->dataset as $key=>$val){

                    foreach($val as $key1=>$val1){

 if(DateTime::createFromFormat('Y-m-d H:i:s', $val1)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val1)!==FALSE) {


                                $dt = new DateTime($val1, new DateTimeZone('UTC'));
                                $tz = new DateTimeZone($tz_to); 
                                $dt->setTimezone($tz);
                                $val1=$dt->format('Y-m-d H:i:s');
                                $this->view->dataset[$key][$key1]=$val1;

                    } 


            }

}



	    $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($datasets[$namespace], $this->view->dataset, $this->view->selectedRows, $this->view->paginator);

            if ($namespace == static::ORDER_NAMESPACE) {
                /**
                 * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
                 * @var Minder_SysScreen_Model_WaitingPicking $dataModel
                 */
                $rowSelector = $this->_helper->getHelper('RowSelector');
                $dataModel   = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $this->view->sysScreens[$namespace][static::UNDISPATCHABLE_ITEMS] = $this->_checkPartialDispatchedOrders($dataModel->fetchPickOrderColumn($this->view->dataset));
            }

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    protected function _getModelMap()
    {
        $datasets = array(
            self::ORDER_NAMESPACE => self::ORDER_MODEL_NAME,
            self::LINES_NAMESPACE => self::LINES_MODEL_NAME
        );
        return $datasets;
    }

    protected function _buildMasterSlaveChain($sysScreens, &$rootSysScreens) {
        $rootSysScreens = (is_array($sysScreens)) ? $sysScreens : array($sysScreens);

        $result = $rootSysScreens = array_flip($rootSysScreens);

        $screenBuilder = new Minder_SysScreen_Builder();
        foreach ($result as $masterSsName => &$slaveSysScreens) {
            $slaveSysScreens = $screenBuilder->getSlaveSysScreens($masterSsName);

            foreach ($slaveSysScreens as $slaveSysScreen => $relations) {
//                if (isset($result[$slaveSysScreen]))
//                    throw new Minder_Exception($slaveSysScreen . ' depends on ' . $masterSsName . ' which in turn depends on ' . $slaveSysScreen . '. Check Sys Screen setup.');

                if (isset($rootSysScreens[$slaveSysScreen])) unset($rootSysScreens[$slaveSysScreen]);

                $result[$slaveSysScreen] = isset($result[$slaveSysScreen]) ? $result[$slaveSysScreen] : array();
            }
        }

        $rootSysScreens = array_flip($rootSysScreens);

        return $result;
    }

    protected function _hasCycles($chain) {
        $slaveScreens  = $masterScreens = array_flip(array_keys($chain));

        foreach ($chain as $masterScreen => $tmpSlaveScreens) {
            $masterScreens[$masterScreen] = is_array($masterScreens[$masterScreen]) ? $masterScreens[$masterScreen] : array();
            $slaveScreens[$masterScreen]  = is_array($slaveScreens[$masterScreen])  ? $slaveScreens[$masterScreen]  : array();

            foreach ($tmpSlaveScreens as $slaveScreen => $relations) {
                $masterScreens[$masterScreen][$slaveScreen] = '';

                $slaveScreens[$slaveScreen] = (is_array($slaveScreens[$slaveScreen])) ? $slaveScreens[$slaveScreen] : array();
                $slaveScreens[$slaveScreen][$masterScreen] = '';
            }
        }

        reset($masterScreens);
        while (false !== ($arrayElement = each($masterScreens))) {
            $testingScreen = $arrayElement['key'];
            $unset = false;

            if (empty($masterScreens[$testingScreen])) {
                foreach ($slaveScreens[$testingScreen] as $tmpScreen => $val) {
                    if (isset($masterScreens[$tmpScreen][$testingScreen]))
                        unset($masterScreens[$tmpScreen][$testingScreen]);
                }

                $unset = true;
            }

            if (empty($slaveScreens[$testingScreen])) {
                foreach ($masterScreens[$testingScreen] as $tmpScreen => $val) {
                    if (isset($slaveScreens[$tmpScreen][$testingScreen]))
                        unset($slaveScreens[$tmpScreen][$testingScreen]);
                }
                $unset = true;
            }

            if ($unset) {
                unset($masterScreens[$testingScreen]);
                unset($slaveScreens[$testingScreen]);
                reset($masterScreens);
            }
        }

        return !empty($masterScreens);
    }

    protected function _sortMasterSlaveChain($chain, $rootSysScreens) {
        $result = array();

        foreach ($rootSysScreens as $sysScreen => $val) {
            $slaveSysScreens = isset($chain[$sysScreen]) ? $chain[$sysScreen] : array();
            $result[] = array($sysScreen => $slaveSysScreens);

            if (!empty($slaveSysScreens))
                $result   = array_merge($result, $this->_sortMasterSlaveChain($chain, $slaveSysScreens));
        }

        return $result;
    }

    protected function _buildSortedMSChain($sysScreens) {
        $chain = $this->_buildMasterSlaveChain($sysScreens, $rootSysScreens);

        if ($this->_hasCycles($chain))
            throw new Minder_Exception('Error init Master-Slave datasets: ' . implode(',', array_keys($chain)) . ' sys screens have cycling dependeces.');

        return $this->_sortMasterSlaveChain($chain, array_flip($rootSysScreens));
    }

    protected function _doSubDatasetsInit($masterSysScreen, $slaveSysScreens) {
        if (empty($slaveSysScreens)) return;

        $namespaceMap = array_flip($this->_getModelMap());

        if (!isset($namespaceMap[$masterSysScreen]))
            throw new Minder_Exception('Unsupported sys screen ' . $masterSysScreen);

        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');
        /**
         * @var Minder_SysScreen_Model $masterModel
         */
        $masterModel = $rowSelector->getModel($namespaceMap[$masterSysScreen], self::$defaultSelectionAction, self::$defaultSelectionController);
        $masterModel->addConditions($rowSelector->getSelectConditions($namespaceMap[$masterSysScreen], self::$defaultSelectionAction, self::$defaultSelectionController));

        $selectedRows = $rowSelector->getSelectedCount($namespaceMap[$masterSysScreen], self::$defaultSelectionAction, self::$defaultSelectionController);

        foreach ($slaveSysScreens as $slaveSsName => $relations) {
            if (!isset($namespaceMap[$slaveSsName]))
                throw new Minder_Exception('Unsupported sys screen ' . $slaveSsName);

            /**
             * @var Minder_SysScreen_Model $slaveModel
             */
            $slaveModel = $rowSelector->getModel($namespaceMap[$slaveSsName], self::$defaultSelectionAction, self::$defaultSelectionController);
            $conditions = $slaveModel->getConditionObject();
            $conditionNamespase = 'MASTER_SELECTION_' . $masterSysScreen;
            $conditions->deleteConditions($conditionNamespase);

            if ($selectedRows < 1)
                $conditions->addConditions(array('1 = 2' => array()), $conditionNamespase);
            else {
                foreach ($relations as $relation) {
                    $masterExpression = (empty($relation['MASTER_TABLE'])) ? $relation['MASTER_FIELD'] : $relation['MASTER_TABLE'] . '.' . $relation['MASTER_FIELD'];
                    $slaveExpression  = (empty($relation['SLAVE_TABLE']))  ? $relation['SLAVE_FIELD']  : $relation['SLAVE_TABLE']  . '.' . $relation['SLAVE_FIELD'];

                    $masterValues = $masterModel->selectArbitraryExpression(0, $selectedRows, 'DISTINCT ' . $masterExpression);

                    $filterValues = array();
                    foreach ($masterValues as $valueRow) {
                        $filterValues[] = current($valueRow);
                    }

                    $conditions->addConditions(array($slaveExpression . ' IN (' . substr(str_repeat('?, ', count($filterValues)), 0, -2) . ')' => $filterValues), $conditionNamespase);
                }
            }
            $slaveModel->setConditionObject($conditions);

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $slaveModel, true, $namespaceMap[$slaveSsName], self::$defaultSelectionAction, self::$defaultSelectionController);
        }
    }

    protected function initSubDatasets($namespace = null) {
        $modelMap = $this->_getModelMap();
        if (is_null($namespace))
            $sysScreens = array(self::ORDER_MODEL_NAME, self::LINES_MODEL_NAME);
        else
            $sysScreens = array($modelMap[$namespace]);

        $msChain = $this->_buildSortedMSChain($sysScreens);
        foreach ($msChain as $chainElement) {
            reset($chainElement);
            $this->_doSubDatasetsInit(key($chainElement), current($chainElement));
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

                $this->initSubDatasets($namespace);
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
        $modelMap = array(self::ORDER_NAMESPACE => self::ORDER_MODEL_NAME, self::LINES_NAMESPACE => self::LINES_MODEL_NAME);
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



	$session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;


	$array_new=$searchFields;

	foreach ($array_new as $key => $value) {
            foreach($value as $key1=>$val1){

                if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') {
                                 unset($array_new[$key]);
				 }


            }

        }



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


            if (!empty($searchFields)) {
                /**
                * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
                */
                $rowSelector = $this->_helper->rowSelector;
                /**
                * @var Minder_SysScreen_Model|Minder_SysScreen_Model_WaitingPicking $rowsModel
                */
                $rowsModel    = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($rowsModel instanceof Minder_SysScreen_Model_WaitingPicking) {
                    $rowsModel->setProdAllocateLimit($this->_allocateLimit()->getProductLimit(self::LIMITS_INSTANCE_ID));
                }

                $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

                $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

            $this->initSubDatasets($namespace);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    public function holdAction() {
        $this->view->errors   = (isset($this->view->errors))   ? $this->view->errors : array();
        $this->view->warnings = (isset($this->view->errors)) ? $this->view->warnings : array();
        $this->view->messages = (isset($this->view->messages)) ? $this->view->messages : array();
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try {
            $totalOrders    = $rowSelector->getTotalCount(self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, null, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            foreach ($selectedOrders as $order) {
                if (!$this->minder->pickOrderHold($order['PICK_ORDER'])) {
                    $this->view->warnings[] = 'Order ' . $order['PICK_ORDER'] . ' was not held.';
                } else {
                    $this->view->messages[] = 'Order ' . $order['PICK_ORDER'] . ' was held successfully.';
                }
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        return $this->_forward('get-dataset');
    }

    public function cancelAction() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $this->view->errors   = (isset($this->view->errors))   ? $this->view->errors : array();
        $this->view->warnings = (isset($this->view->warnings)) ? $this->view->warnings : array();
        $this->view->messages = (isset($this->view->messages)) ? $this->view->messages : array();

        $reason = $this->getRequest()->getParam('cancel_reason');
        if (empty($reason)) {
            $this->view->errors[] = 'Please enter a reason for cancelling.';
            return $this->_forward('get-dataset');
        }

        try {
            $totalOrders    = $rowSelector->getTotalCount(self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, null, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            foreach ($selectedOrders as $order) {
                if (!$this->minder->pickOrderCancel($order['PICK_ORDER'], $reason)) {
                    $this->view->warnings[] = 'Order ' . $order['PICK_ORDER'] . ' was not canceled.';
                } else {
                    $this->view->messages[] = 'Order ' . $order['PICK_ORDER'] . ' was canceled.';
                }
            }

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        return $this->_forward('get-dataset');
    }

    public function changePriorityAction() {
        $mode                  = $this->getRequest()->getParam('mode');
        $response              = new Minder_JSResponse();
        $response->updatedRows = array();

        try {
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector = $this->_helper->rowSelector;
            $selectedCount = $rowSelector->getSelectedCount(self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            if ($selectedCount < 1)
                throw new Minder_Exception('Select Order first.');

            $totalOrders    = $rowSelector->getTotalCount(self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, true, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            foreach ($selectedOrders as $rowId => $row) {
                $order = $this->minder->getPickOrder($row['PICK_ORDER'], '');

                switch (strtolower($mode)) {
                    case 'inc':
                        $order->pickPriority++;
                        break;
                    case 'dec':
                        if ($order->pickPriority > 1) {
                            $order->pickPriority--;
                        } else {
                            continue 2;
                        }
                        break;
                    default:
                        throw new Minder_Exception("Unsupported mode: '$mode'");
                }
                if ($this->minder->updatePickOrderPickPriority($order->pickOrder, $order->pickPriority)) {
                    $order = $this->minder->getPickOrder($row['PICK_ORDER'], '');
                    $response->updatedRows[$rowId] = $order->pickPriority;
                } else {
                    $response->errors[] = 'Pick Order #' . $row['PICK_ORDER'] . ' priority was not updated. ' . $this->minder->lastError;
                }
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    public function printPickLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        $namespace = $this->getRequest()->getParam('namespace');
        $modelMap = $this->_getModelMap();
        if (empty($namespace)) {
            $result->errors[] = 'Namespace is empty.';
        } elseif (!isset($modelMap[$namespace])) {
            $result->errors[] = 'Unknown namespace "' . $namespace . '".';
        }

        if (count($result->errors) > 0) {
            echo json_encode($result);
            return;
        }

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector  = $this->_helper->rowSelector;
        $selectedRows = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedRows < 1)
            $result->warnings[] = 'No rows selected. Select one.';

        if (count($result->warnings) > 0) {
            echo json_encode($result);
            return;
        }

        try {
            /**
             *@var Minder_SysScreen_Model_WaitingPicking|Minder_SysScreen_Model_WaitingPickingLine $model
             */
            $model = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $model->addConditions($rowSelector->getSelectConditions($namespace, self::$defaultSelectionAction, self::$defaultSelectionController));

            $labelPrinter = new Minder_LabelPrinter_PickLabel();
            $result = $labelPrinter->doPrint($model->fetchPickLabelNoForPickLabels(0, $selectedRows), $this->minder->getPrinter(), $result);
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }
        echo json_encode($result);
    }

    public function getDeviceStatisticsAction() {
        $this->view->sysScreens = $this->_buildDatatset(array(static::DEVICE_STATISTICS_MODEL => static::DEVICE_STATISTICS_NAMESPACE));
        $this->_viewRenderer()->setNoRender();
        echo $this->_datasetToJson($this->view);
    }

    public function selectDeviceStatisticsRowAction() {
        $result = new Minder_JSResponse();
        $result->sysScreens = array();

        $this->_viewRenderer()->setNoRender();

        try{
            foreach ($this->getRequest()->getParam('sysScreens', array()) as $namespace => $sysScreen) {
                $pagination = $this->restorePagination($namespace);
                $result->sysScreens[$namespace] = $this->_rowSelector()->setScreenSelection($sysScreen, $pagination, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function printPickBlockAction() {
        $this->_viewRenderer()->setNoRender();
        $printer = $this->minder->getPrinter(null, Minder2_Environment::getCurrentPrinter()->DEVICE_ID);

        $response = $this->_waitPickingHelper()->printPickBlock($this->getRequest()->getParam('labelAmount', 0), $printer);

        echo json_encode($response);
    }

    protected function _checkPartialDispatchedOrders($orders) {
        $pickOrders = array_map(function($order) {
            return $order['PICK_ORDER'];
        }, $orders);

        $permission = new Minder_Permission_Order_PartialDespatch();
        $pickOrders = $permission->check($pickOrders);

        return array_values(array_filter($orders, function($order)use($pickOrders){
            return in_array($order['PICK_ORDER'], $pickOrders);
        }));
    }

    /**
     * @return Minder_Controller_Action_Helper_AllocateLimit
     */
    protected function _allocateLimit() {
        return $this->getHelper('AllocateLimit');
    }

    /**
     * @return Minder_Controller_Action_Helper_WaitPicking
     */
    protected function _waitPickingHelper() {
        return $this->getHelper('WaitPicking');
    }

    /**
     * Setup menu shortcuts.
     * @return Minder_Controller_Action Provides a fluent interface.
     */
    /*protected function _setupShortcuts()
    {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('picking')->buildMinderMenuArray();

        return $this;
    }*/
}
