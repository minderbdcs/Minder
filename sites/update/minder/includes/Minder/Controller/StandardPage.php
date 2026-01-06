<?php

abstract class Minder_Controller_StandardPage extends Minder_Controller_Action {
    public function indexAction() {
        $this->view->masterSlaveChain = array();
        $this->view->searchFields = array();

        $namespaceMap = $this->_getNamespaceMap();

        try {
            $this->view->masterSlaveChain = $this->_masterSlaveHelper()->buildMasterSlaveChain(array_keys($namespaceMap));
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        $searchResults  = array();

        foreach ($namespaceMap as $screenName => $namespace) {
            try {
                list($tmpSearchFields, , , $tmpGISearchFields) = $this->_getScreenBuilder()->buildSysScreenSearchFields($screenName);

                $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
                $searchFields = $this->_searchHelper()->getSearch($searchFields, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $this->view->searchFields[$screenName] = $searchFields;

                $screenModel    = $this->_getModelBuilder()->buildSysScreenModel($screenName, $this->_getModelPrototype($screenName));
                $screenModel->setConditions($screenModel->makeConditionsFromSearch($searchFields));
                $this->_rowSelector()->setRowSelection('select_complete', 'init', null, null, $screenModel, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            } catch(Exception $e) {
                $this->addError($e->getMessage());
            }
        }

        $this->view->sysScreens = array();
        try {
            $this->_masterSlaveHelper()->initSubDatasets(array_flip($this->_getNamespaceMap()), null, $this->_loadDisabledRelations(), self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        try {
            $this->view->sysScreens = $this->_buildDatatset($this->_getNamespaceMap());
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }

        foreach ($namespaceMap as $screenName => $namespace) {
            try {
                $searchResults[$screenName] = $this->_getJsSearchResultBuilder()->buildEmptyResult($screenName, $namespace);
                $searchResults[$screenName] = $this->_getJsSearchResultBuilder()->buildScreenSearchResult($screenName, $namespace);
            } catch(Exception $e) {
                $this->addError($e->getMessage());
            }
        }

        $this->view->searchResults = $searchResults;
    }

    public function getDatasetAction() {
        $this->view->sysScreens = $this->_buildDatatset($this->_getNamespaceMap());
        $this->_viewRenderer()->setNoRender();
        echo $this->_datasetToJson($this->view);
    }

    public function selectRowAction() {
        $result = new Minder_JSResponse();
        $result->sysScreens = array();

        $this->_viewRenderer()->setNoRender();

        try{
            foreach ($this->getRequest()->getParam('sysScreens', array()) as $namespace => $sysScreen) {
                $pagination = $this->restorePagination($namespace);
                $result->sysScreens[$namespace] = $this->_rowSelector()->setScreenSelection($sysScreen, $pagination, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $this->_masterSlaveHelper()->initSubDatasets(array_flip($this->_getNamespaceMap()), $namespace, $this->_loadDisabledRelations(), self::$defaultSelectionAction, self::$defaultSelectionController);
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();

        $searchNamespace = $this->getRequest()->getParam('searchNamespace');

        $modelMap = array_flip($this->_getNamespaceMap());

        try{
            if (empty($searchNamespace)) {
                throw new Exception('Search namespace is not defined.');
            }

            if (!isset($modelMap[$searchNamespace])) {
                throw new Exception('Unknown namespace "' . $searchNamespace . '".');
            }

            $searchKeeper  = $this->_searchHelper();
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields($modelMap[$searchNamespace]);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, $searchNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            $rowSelector = $this->_rowSelector();
            /**
             * @var Minder_SysScreen_Model
             */
            $rowsModel    = $rowSelector->getModel($searchNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, $searchNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->_masterSlaveHelper()->initSubDatasets(array_flip($this->_getNamespaceMap()), $searchNamespace, $this->_loadDisabledRelations(), self::$defaultSelectionAction, self::$defaultSelectionController);

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        $this->getDatasetAction();
    }

    public function reportAction() {
        $namespaceMap = $this->_getNamespaceMap();

        $request = $this->getRequest();
        $request->setParam('selection_namespace', $namespaceMap[$request->getParam('screen', '')]);
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    /**
     * @param $namespace
     * @return Minder_SysScreen_View|null
     */
    protected function _getSelectionsModel($namespace) {
        return $this->_rowSelector()->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    protected function _getModelPrototype($screenName) {
        return null;
    }

    protected abstract function _getNamespaceMap();

}