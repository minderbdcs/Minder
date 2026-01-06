<?php

class Transfer2_WholeController extends Minder_Controller_Action {
    const LINES_MODEL_NAME = 'TRANSFER_WHOLE';
    const LINES_NAMESPACE  = 'TRANSFER2-TRANSFER_WHOLE';

    public function init()
    {
        parent::init();
        $this->view->pageTitle = 'Transfer Whole Location';
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
            * @var Minder_SysScreen_Model_TransferWhole $linesModel
            */
            $linesModel   = $screenBuilder->buildSysScreenModel(self::LINES_MODEL_NAME, new Minder_SysScreen_Model_TransferWhole());

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

    public function searchLocationAction() {
        try {
            $this->view->errors   = array();
            $this->view->warnings = array();
            $this->view->messages = array();

            $fromLocation        = $this->getRequest()->getParam('fromLocation');

            if (empty($fromLocation))
                return $this->_forward('get-dataset');

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_TransferWhole $issnModel
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
             * @var Minder_SysScreen_Model_TransferWhole $issnModel
             */
            $issnModel = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (count($issnModel) < 1) {
                $this->view->warnings[] = 'No ISSN to transfer. Please scan another From Location';
                return $this->_forward('get-dataset');
            }

            $fromLocation = $issnModel->getFromLocation();

            if (empty($fromLocation))
                throw new Exception('Empty From Location.');

            $transaction = new Transaction_TRLOA();
            $transaction->whId      =   substr($fromLocation, 0, 2);
            $transaction->locnId    =   substr($fromLocation, 2);
            $transaction->reference =   'On-Screen Whole Location Transfer';
            if (false === ($result = $this->minder->doTransactionResponse($transaction)))
                throw new Minder_Exception('Error executing ' . $transaction->transClass. ' ' . $transaction->transCode . ': ' . $this->minder->lastError);

            $transaction            =   new Transaction_TRLIA();
            $transaction->whId      =   $intoWhId;
            $transaction->locnId    =   substr($intoLocation, 2);
            $transaction->reference =   'On-Screen Whole Location Transfer';

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