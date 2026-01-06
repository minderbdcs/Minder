<?php

class Transfer2_IssnController extends Minder_Controller_Action {

    const ISSN_MODEL_NAME = 'TRANSFER_ISSN';
    const ISSN_NAMESPACE  = 'TRANSFER2-TRANSFER_ISSN';

    protected $_session = null;

    public function init()
    {
        $this->view->pageTitle = 'Transfer ISSN';

        return parent::init();
    }

    protected function _getSession() {
        if (is_null($this->_session))
            $this->_session = new Zend_Session_Namespace(self::ISSN_NAMESPACE);

        return $this->_session;
    }

    protected function _getScannedIssns() {
        $session = $this->_getSession();
        return (isset($session->scannedIssns)) ? $session->scannedIssns : array();
    }

    protected function _saveScannedIssns($issns) {
        $session               = $this->_getSession();
        $session->scannedIssns = $issns;
    }

    protected function _getIsTransferred() {
        $session = $this->_getSession();
        return (isset($session->isTransferred)) ? (boolean)$session->isTransferred : false;
    }

    protected function _saveIsTransferred($isTransferred) {
        $session                = $this->_getSession();
        $session->isTransferred = (boolean)$isTransferred;
    }

    public function indexAction() {
        try {
            $this->view->issnSsName     = $this->view->searchFormSsName = self::ISSN_MODEL_NAME;
            $this->view->issnNamespace = self::ISSN_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ISSN_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;
            $searchFormAdapter = new Minder_Form_SysScreenSearchAdapter();
            $searchForm = $searchFormAdapter->build(self::ISSN_MODEL_NAME, $searchFields);
            $searchForm->getDisplayGroup('searchFields')->getDecorator('formElements')->setOption('columns', 1);
            $searchForm->getElement('SEARCH_BUTTON')->setLabel('UPDATE');
            $searchForm->getElement('CLEAR_BUTTON')->setLabel('CLEAR');
            $this->view->searchForm = $searchForm;

            /**
            * @var Minder_SysScreen_Model_TransferIssn $issnModel
            */
            $issnModel   = $screenBuilder->buildSysScreenModel(self::ISSN_MODEL_NAME, new Minder_SysScreen_Model_TransferIssn());
            $issnModel->transferCompleted = $this->_getIsTransferred();

            $scannedIssns = $this->_getScannedIssns();

            if (count($scannedIssns) > 0)
                $issnModel->filterIssnId($scannedIssns);
            else
                $issnModel->setConditions(array('1 = 2' => array()));

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens', array(self::ISSN_NAMESPACE => array()));
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->issnJsSearchResults = $this->view->jsSearchResult(
                                                    self::ISSN_MODEL_NAME,
                                                    self::ISSN_NAMESPACE,
                                                    array('sysScreenCaption' => 'ISSN LIST', 'usePagination'    => true)
            );
            $this->view->issnJsSearchResultsDataset = $this->view->sysScreens[self::ISSN_NAMESPACE];

            $this->view->ssnBarcodeDescriptor      = $this->view->SymbologyPrefixDescriptor('BARCODE');
            $this->view->locationBarcodeDescriptor = $this->view->SymbologyPrefixDescriptor('LOCATION');
            $this->view->screenButtonBarcodeDescriptor = $this->view->SymbologyPrefixDescriptor('SCREEN_BUTTON');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        return;
    }

    public function getDatasetAction() {
        $datasets = array(
            self::ISSN_NAMESPACE   => self::ISSN_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($sysScreens as $namespace => $sysScreenPagination) {
            if (!isset($datasets[$namespace]))
                continue;

            $pagination = $this->fillPagination($this->restorePagination($namespace), $sysScreenPagination);

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($datasets[$namespace], $this->view->dataset, $this->view->selectedRows, $this->view->paginator);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new stdClass();
        $result->errors   = array();
        $result->warnings = array();
        $result->messages = array();
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

                $result->sysScreens[$namespace] = $tmpSysScreen;
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function searchIssnAction() {
        try {
            $this->view->errors   = array();
            $this->view->warnings = array();
            $this->view->messages = array();

            $scannedIssns = $this->_getScannedIssns();
            $ssnId        = $this->getRequest()->getParam('ssnId');

            if (empty($ssnId))
                return $this->_forward('get-dataset');

            $scannedIssns[$ssnId] = $ssnId;

            $sycScreenbuilder = new Minder_SysScreen_Builder();
            /**
             * @var Minder_SysScreen_Model_TransferIssn $issnSearchModel
             */
            $issnSearchModel = $sycScreenbuilder->buildSysScreenModel(self::ISSN_MODEL_NAME, new Minder_SysScreen_Model_TransferIssn());

            $foundIssns = $issnSearchModel->findIssn($ssnId);
            if (count($foundIssns) < 1) {
                $this->view->warnings[] = 'ISSN #' . $ssnId . ' not found.';
                return $this->_forward('get-dataset');
            }

            if ($this->_getIsTransferred()) {
                $scannedIssns = array();
                $this->_saveIsTransferred(false);
            }

            reset($foundIssns);
            $foundIssns = current($foundIssns);

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_TransferIssn $issnModel
             */
            $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $issnModel->transferCompleted = $this->_getIsTransferred();

            if (count($scannedIssns) < 1)
                $issnModel->addConditions(array('1 = 2' => array()));
            else
                $issnModel->filterIssnId($scannedIssns);
            
            $rowSelector->setRowSelection($foundIssns[$issnSearchModel->getPKeyAlias()], 'true', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->_saveScannedIssns($scannedIssns);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage()));
            $this->view->errors[] = $e->getMessage();
        }

        return $this->_forward('get-dataset');
    }

    public function deleteSelectedIssnsAction() {
        try {
            $this->view->errors   = array();
            $this->view->warnings = array();
            $this->view->messages = array();

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $selectedCount = $rowSelector->getSelectedCount(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            if ($selectedCount  < 1) {
                $this->view->warnings[] = 'No rows selected. Please select one.';
                return $this->_forward('get-dataset');
            }

            /**
             * @var Minder_SysScreen_Model_TransferIssn $issnModel
             */
            $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));

            $scannedIssns = $this->_getScannedIssns();

            foreach ($issnModel->selectSsnId(0, $selectedCount) as $ssnId)
                if (isset($scannedIssns[$ssnId])) unset($scannedIssns[$ssnId]);

            if (count($scannedIssns) < 1)
                $issnModel->addConditions(array('1 = 2' => array()));
            else
                $issnModel->filterIssnId($scannedIssns);

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->_saveScannedIssns($scannedIssns);
        } catch (Exception $e) {
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage()));
            $this->view->errors[] = $e->getMessage();
        }

        return $this->_forward('get-dataset');
    }

    public function transferToLocationAction() {
        try {
            $this->view->errors   = array();
            $this->view->warnings = array();
            $this->view->messages = array();

            $location = $this->getRequest()->getParam('location');

            if (empty($location)) {
                $this->view->warnings[] = 'No Location given.';
                return $this->_forward('get-dataset');
            }

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_TransferIssn $issnModel
             */
            $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (count($issnModel) < 1) {
                $this->view->warnings[] = 'No ISSN scanned to transfer. Please scan one.';
                return $this->_forward('get-dataset');
            }

            $transferedIssns = $issnModel->transferToLocnId($location);
            $this->_saveIsTransferred(true);
            $issnModel->transferCompleted = $this->_getIsTransferred();
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (count($transferedIssns) > 0) {
                $this->view->messages[] = 'Transferred ISSNs: ' . implode(', ', $transferedIssns) . '.';
            } else {
                $this->view->warnings[] = 'No ISSNs were transferred.';
            }
        } catch (Exception $e) {
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage()));
            $this->view->errors[] = $e->getMessage();
        }

        return $this->_forward('get-dataset');
    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    /**
     * Setup menu shortcuts.
     * @return Minder_Controller_Action Provides a fluent interface.
     */
    /*protected function _setupShortcuts()
    {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('warehouse')->buildMinderMenuArray();

        return $this;
    }*/
}