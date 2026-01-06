<?php

class Warehouse_EnquireController extends Minder_Controller_Action {
    const NAMESPACE_PREFIX = 'ENQUIRE';

    /**
     * @var null|Minder_Enquire_List
     */
    protected $_enquireTypes = null;
    /**
     * @var Minder_SysScreen_Builder
     */
    protected $_screenBuilder;

    /**
     * @var Minder_SysScreen_View_Builder
     */
    protected $_modelBuilder;

    /**
     * @var Minder_SysScreen_VarianceList|null
     */
    protected $_screenVariances = null;

    public function indexAction() {
        $this->view->pageTitle = 'Enquire';

        $enquireTypes = $this->_getEnquireTypes();
        $this->_storeDisabledRelations(array());

        if (count($enquireTypes) < 1) {
            $this->addWarning('No Enquire Types Defined, Check system settings.');
        }

        $descriptors = $this->_paramMangerHelper()->generateSymbologyPrefixDescriptors(array(), $enquireTypes->dataType);
        $this->addWarning($this->_paramMangerHelper()->getErrors());

        $this->view->masterSlaveChain = array();
        $this->view->dataIdentifiers = $descriptors;
        $this->view->enquireTypesDescription = $enquireTypes->description;
        $namespaceMap = array();

        try {
            $namespaceMap = $this->_getNamespaceMap();
            $this->view->masterSlaveChain = $this->_masterSlave()->buildMasterSlaveChain(array_keys($namespaceMap));
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        $searchResults  = array();

        foreach ($namespaceMap as $screenName => $namespace) {
            try {
                $screenModel    = $this->_getModelBuilder()->buildSysScreenModel($screenName);
                $this->_getRowSelector()->setRowSelection('select_complete', 'init', null, null, $screenModel, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            } catch(Exception $e) {
                $this->addError($e->getMessage());
            }
        }

        try {
            $this->_masterSlave()->initSubDatasets(array_flip($this->_getNamespaceMap()), null, $this->_loadDisabledRelations(), self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        foreach ($enquireTypes as $enquire) {
            try {
                $searchResults[$enquire->dataType] = $this->_getEnquireSearchResult($enquire);
            } catch(Exception $e) {
                $this->addError($e->getMessage());
            }
        }

        $this->view->searchResults = $searchResults;
    }

    public function getDatasetAction() {
        $namespaceMap = $this->_getNamespaceMap();
        $modelMap = array_flip($namespaceMap);
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        $disabledRelations = $this->getRequest()->getParam('disabledRelations', $this->_loadDisabledRelations());
        $this->_storeDisabledRelations($disabledRelations);
        $this->_masterSlave()->initSubDatasets(array_flip($this->_getNamespaceMap()), null, $disabledRelations, self::$defaultSelectionAction, self::$defaultSelectionController);

        foreach ($sysScreens as $namespace => $data) {
            if (in_array($namespace, $namespaceMap)) {
                $pagination = $this->restorePagination($namespace);
                if (isset($sysScreens[$namespace])) {
                    $pagination = $this->fillPagination($pagination, $sysScreens[$namespace]);
                }

                $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
                $pagination = array_merge($pagination, $this->view->paginator);
                $this->savePagination($namespace, $pagination);
                $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelMap[$namespace], $this->view->dataset, $this->view->selectedRows, $pagination);

                unset($this->view->paginator);
                unset($this->view->dataset);
                unset($this->view->selectedRows);
            }
        }
    }

    public function selectRowAction() {
        $result = new Minder_JSResponse();
        $result->sysScreens = array();
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);

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
                    $this->_getRowSelector()->setSelectionMode($pagination['selectionMode'], $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                    $this->_getRowSelector()->setRowSelection($sysScreen['rowId'], $sysScreen['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                }

                $tmpSysScreen->selectedRowsTotal = $this->_getRowSelector()->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($tmpSysScreen->selectedRowsTotal > 0) {
                    $tmpSysScreen->selectedRows = $this->_getRowSelector()->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
                    $tmpSysScreen->selectedRowsOnPage = count($tmpSysScreen->selectedRows);

                }

                $this->_masterSlave()->initSubDatasets(array_flip($this->_getNamespaceMap()), $namespace, $this->_loadDisabledRelations(), self::$defaultSelectionAction, self::$defaultSelectionController);
                $result->sysScreens[$namespace] = $tmpSysScreen;
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function enquireProductAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $enquireType = $this->getRequest()->getParam('type', '');
        $prodId = $this->getRequest()->getParam('prodId', '');
        $fromDate =  $this->getRequest()->getParam('fromDate', '');
        $tillDate =  $this->getRequest()->getParam('tillDate', '');
        $enquire = $this->_getEnquireManager()->getByType($enquireType);

        if (empty($enquire)) {
            $this->_forward('get-dataset');
            return;
        }

        $affectedNamespaces = array();
        $namespaceMap = $this->_getNamespaceMap();

        foreach ($enquire->screensArray as $screenName) {
            foreach ($this->_getVarianceList()->getScreenVariance($screenName)->variances as $varianceName) {
                list($tmpSearchFields, , , $tmpGISearchFields) = $this->_getScreenBuilder()->buildSysScreenSearchFields($varianceName);
                $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);

                foreach ($searchFields as &$searchField) {
                    $namespace = $namespaceMap[$varianceName];
                    if ($searchField['SSV_ALIAS'] == $enquireType) {
                        $affectedNamespaces[$namespace] = array();
                        $searchField['SEARCH_VALUE'] = $prodId;
                    }

                    if ($searchField['SSV_ALIAS'] == 'FROM_DATE') {
                        $affectedNamespaces[$namespace] = array();
                        if (!empty($fromDate)) {
                            $searchField['SEARCH_VALUE'] = $fromDate;
                        }
                    }

                    if ($searchField['SSV_ALIAS'] == 'TILL_DATE') {
                        $affectedNamespaces[$namespace] = array();
                        if (!empty($tillDate)) {
                            $searchField['SEARCH_VALUE'] = $tillDate;
                        }
                    }

                    if (isset($affectedNamespaces[$namespace])) {
                        $model          = $this->_getRowSelector()->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                        $model->setConditions($model->makeConditionsFromSearch($searchFields));
                        $this->_getRowSelector()->setRowSelection('select_complete', 'init', null, null, $model, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                    }
                }
            }
        }

        $request = $this->getRequest();
        $request->setParam('sysScreens', $affectedNamespaces);

        $this->_forward('get-dataset');
    }

    public function reportAction() {
        $namespaceMap = $this->_getNamespaceMap();

        $request = $this->getRequest();
        $request->setParam('selection_namespace', $namespaceMap[$request->getParam('screen', '')]);
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    protected function _getNamespaceMap() {
        $result = array();

        foreach ($this->_getEnquireTypes()->getScreens() as $screenName) {
            foreach ($this->_getVarianceList()->getScreenVariance($screenName)->variances as $varianceScreenName) {
                $result[$varianceScreenName] = static::NAMESPACE_PREFIX . '-' . $varianceScreenName;
            }
        }

        return $result;
    }

    protected function _getVarianceList() {
        if (is_null($this->_screenVariances)) {
            $this->_screenVariances = $this->_getVarianceManager()->getAll();
        }

        return $this->_screenVariances;
    }

    protected function _getVarianceManager() {
        return new Minder_SysScreen_VarianceManager();
    }

    protected function _fetchEnquireTypes() {
        $enquireTypes = new Minder_Enquire_List();
        try {
            $enquireTypes = $this->_getEnquireManager()->getAll();
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        return $enquireTypes;
    }

    protected function _getEnquireTypes() {
        if (is_null($this->_enquireTypes)) {
            $this->_enquireTypes = $this->_fetchEnquireTypes();
        }

        return $this->_enquireTypes;
    }

    protected function _getEnquireManager() {
        return new Minder_Enquire_Manager();
    }

    protected function _getEnquireSearchResult(Minder_Enquire_Abstract $enquire) {
        switch ($enquire->dataType) {
            case (Minder_Enquire_Manager::PROD_ID):
                return $this->_getProdIdSearchResult($enquire);
            default:
                throw new Exception('Unknown enquire type: ' . $enquire->dataType);
        }
    }

    protected function _getProdIdSearchResult(Minder_Enquire_Product $enquire) {
        $searchResults = array();

        $searchResults['main']  = $this->_buildScreenSearchResults(array_flip($this->_getVarianceList()->getScreenVariance($enquire->primaryScreen)->variances));
        $searchResults['grn']   = $this->_buildScreenSearchResults(array_flip($this->_getVarianceList()->getScreenVariance($enquire->receiptScreen)->variances));
        $searchResults['dsdx']  = $this->_buildScreenSearchResults(array_flip($this->_getVarianceList()->getScreenVariance($enquire->despatchScreen)->variances));

        return $searchResults;
    }

    protected function _buildScreenSearchResults($screens) {
        $result = array();
        foreach ($screens as $screenName => $screenCaption) {
            $result[$screenName] = $this->_buildScreenSearchResult($screenName, $this->_getScreenBuilder()->getSysScreenTitle($screenName));
        }

        return $result;
    }

    protected function _buildScreenSearchResult($screenName, $screenCaption) {
        $namespaceMap   = $this->_getNamespaceMap();
        return array(
            'name'              => $screenName,
            'namespace'         => $namespaceMap[$screenName],
            'searchResults'     => $this->_getJsSearchResultBuilder()->build($screenName, $namespaceMap[$screenName], array('sysScreenCaption' => $screenCaption)),
        );
    }

    /**
     * @return Minder_Controller_Action_Helper_RowSelector
     */
    protected function _getRowSelector()
    {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');;
        return $rowSelector;
    }

    /**
     * @return Minder_SysScreen_Builder
     */
    protected function _getScreenBuilder()
    {
        if (empty($this->_screenBuilder)) {
            $this->_screenBuilder = new Minder_SysScreen_Builder();
        }
        return $this->_screenBuilder;
    }

    protected function _getModelBuilder() {
        if (empty($this->_modelBuilder)) {
            $this->_modelBuilder = new Minder_SysScreen_View_Builder();
        }
        return $this->_modelBuilder;
    }

    /**
     * @return Minder_Controller_Action_Helper_MasterSlave
     */
    protected function _masterSlave() {
        return $this->_helper->getHelper('MasterSlave');
    }

    /**
     * @return Minder_Controller_Action_Helper_JsSearchResultBuilder
     */
    protected function _getJsSearchResultBuilder() {
        return $this->getHelper('JsSearchResultBuilder');
    }

    protected function _storeDisabledRelations($disableRelations) {
        $this->session->disabledRelations = $disableRelations;
    }

    protected function _loadDisabledRelations() {
        return isset($this->session->disabledRelations) ? $this->session->disabledRelations : array();
    }
}