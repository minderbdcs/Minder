<?php

class Transfer2_MoveableController extends Minder_Controller_Action {
    const LINES_MODEL_NAME = 'TRANSFER_MOVEABLE';
    const LINES_NAMESPACE  = 'TRANSFER2-TRANSFER_MOVEABLE';

    public function init()
    {
        parent::init();
        $this->view->pageTitle = 'Transfer Moveable';
    }


    public function indexAction() {
        try {
            $this->view->linesSsName     = $this->view->searchFormSsName = self::LINES_MODEL_NAME;
            $this->view->linesNamespace = self::LINES_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::LINES_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;
            $searchFormAdapter = new Minder_Form_SysScreenSearchAdapter();
            $searchForm = $searchFormAdapter->build(self::LINES_MODEL_NAME, $searchFields);
            $searchForm->getDisplayGroup('searchFields')->getDecorator('formElements')->setOption('columns', 1);
            $searchForm->getElement('SEARCH_BUTTON')->setLabel('UPDATE');
            $searchForm->getElement('CLEAR_BUTTON')->setLabel('CLEAR');
            $this->view->searchForm = $searchForm;

            /**
            * @var Minder_SysScreen_Model_TransferMoveable $linesModel
            */
            $linesModel   = $screenBuilder->buildSysScreenModel(self::LINES_MODEL_NAME, new Minder_SysScreen_Model_TransferMoveable());

            $linesModel->setConditions(array('1 = 2' => array()));

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens', array(self::LINES_NAMESPACE => array()));
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->linesJsSearchResults = $this->view->jsSearchResult(
                                                    self::LINES_MODEL_NAME,
                                                    self::LINES_NAMESPACE,
                                                    array('sysScreenCaption' => 'ISSN LIST', 'usePagination'    => true)
            );
            $this->view->linesJsSearchResultsDataset = $this->view->sysScreens[self::LINES_NAMESPACE];

            $this->view->locationBarcodeDescriptor = $this->view->SymbologyPrefixDescriptor('LOCATION');
            $this->view->screenButtonBarcodeDescriptor = $this->view->SymbologyPrefixDescriptor('SCREEN_BUTTON');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            $this->view->hasErrors = true;
        }

        return;
    }

    public function getDatasetAction() {
        $datasets = array(
            self::LINES_NAMESPACE   => self::LINES_MODEL_NAME
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

                $result->sysScreens[$namespace] = $tmpSysScreen;
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function searchLocationAction() {
        try {
            $this->view->errors   = array();
            $this->view->warnings = array();
            $this->view->messages = array();

            $fromLocation        = $this->getRequest()->getParam('fromLocation');

            if (empty($fromLocation))
                return $this->_forward('get-dataset');

            if (!Minder::getInstance()->isMoveableLocation(substr($fromLocation, 2))) 
                throw new Exception($fromLocation . ' is not Moveable Location.');

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_TransferMoveable $issnModel
             */
            $issnModel = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $issnModel->filterLocation($fromLocation);

            $rowSelector->setRowSelection('select_complete', 'true', null, null, $issnModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
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
            $this->view->transferComplete = false;

            $intoLocation = $this->getRequest()->getParam('intoLocation');

            if (empty($intoLocation)) {
                $this->view->warnings[] = 'No Into Location given.';
                return $this->_forward('get-dataset');
            }

            $intoWhId = substr($intoLocation, 0, 2);

            $warehouseListLimited = $this->minder->getWarehouseListLimited();
            if (!isset($warehouseListLimited[$intoWhId]))
                throw new Exception('Into Warehouse not found, please enter correct Into Location.');

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_TransferMoveable $issnModel
             */
            $issnModel = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (count($issnModel) < 1) {
                $this->view->warnings[] = 'No ISSN to transfer. Please scan another From Location';
                return $this->_forward('get-dataset');
            }

            $fromLocation = $issnModel->getFromLocation();

            if (empty($fromLocation))
                throw new Exception('Empty From Location.');

            $transaction = new Transaction_TRMIA();
            $transaction->whId          =   $intoWhId;
            $transaction->locnId        =   substr($intoLocation, 2);
            $transaction->subLocation   =   $fromLocation;
            $transaction->reference     =   'On-Screen Moveable Location Transfer';

            if (false === ($result = $this->minder->doTransactionResponse($transaction)))
                throw new Minder_Exception('Error executing ' . $transaction->transClass. ' ' . $transaction->transCode . ': ' . $this->minder->lastError);

            $this->view->messages[] = $result;
            $this->view->transferComplete = true;
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