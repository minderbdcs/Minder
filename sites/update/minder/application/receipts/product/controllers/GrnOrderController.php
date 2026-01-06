<?php

class Receipts_GrnOrderController extends Minder_Controller_Action {

    const ORDER_MODEL_NAME = 'GRN_ORDER';
    const ORDER_DATASET_NAMESPACE = 'RECIEPT-GRN_ORDER';

    public function init() {
        parent::init();

        $this->view->pageTitle = 'GRN ORDER';
    }

    public function indexAction() {
        try {
            $this->view->orderSsName     = $this->view->searchFormSsName = self::ORDER_MODEL_NAME;
            $this->view->orderNamespace  = self::ORDER_DATASET_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ORDER_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            /**
            * @var Minder_SysScreen_Model_GrnOrder $orderModel
            */
            $orderModel   = $screenBuilder->buildSysScreenModel(self::ORDER_MODEL_NAME, new Minder_SysScreen_Model_GrnOrder());
            $orderModel->setConditions($orderModel->makeConditionsFromSearch($searchFields));

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $orderModel, true, self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->orderJsSearchResults = $this->view->jsSearchResult(
                                                    self::ORDER_MODEL_NAME,
                                                    self::ORDER_DATASET_NAMESPACE,
                                                    array('sysScreenCaption' => 'GRN ORDER LIST', 'usePagination'    => true)
            );
            $this->view->orderJsSearchResultsDataset = $this->view->sysScreens[self::ORDER_DATASET_NAMESPACE];
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        return;
    }

    public function getDatasetAction() {
        $datasets = array(
            self::ORDER_DATASET_NAMESPACE   => self::ORDER_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);
            if (isset($sysScreens[$namespace])) {
                $pagination = $this->fillPagination($pagination, $sysScreens[$namespace]);
            }

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelname, $this->view->dataset, $this->view->selectedRows, $this->view->paginator);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new Minder_JSResponse();
        $result->namespace    = '';
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try{
            $sysScreens = $this->getRequest()->getParam('sysScreens', array());

            foreach ($sysScreens as $namespace => $sysScreen) {
                $result->namespace = $namespace;
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

                $result->selectedRowsTotal = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($result->selectedRowsTotal > 0) {
                    $result->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
                    $result->selectedRowsOnPage = count($result->selectedRows);
                }
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();

        try{
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ORDER_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model $rowsModel
            */
            $rowsModel    = $rowSelector->getModel(self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    public function printLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();

        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector       = $this->_helper->getHelper('RowSelector');
        $selectedIssnCount = $rowSelector->getSelectedCount(self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedIssnCount < 1) {
            $response->errors[] = 'No GRN_ORDERs selected. Please, select one.';
            echo json_encode($response);
            return;
        }

        /**
        * @var Minder_SysScreen_Model_GrnOrder $orderModel
        */
        $orderModel = $rowSelector->getModel(self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        $orderModel->addConditions($rowSelector->getSelectConditions(self::ORDER_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        $result = $orderModel->printLabels($this->minder->getPrinter());
        $response->errors   = array_merge($response->errors, $result->errors);
        $response->messages = array_merge($response->messages, $result->messages);

        echo json_encode($response);
    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');

    }
}


