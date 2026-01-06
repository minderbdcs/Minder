<?php

class Warehouse_Location2Controller extends Minder_Controller_Action {

    const LOCATION_MODEL_NAME = 'LOCATION';
    const LOCATION_NAMESPACE   = 'WAREHOUSE-LOCATION';

    const ISSN_MODEL_NAME     = 'LOCATIONISSN';
    const ISSN_NAMESPACE      = 'WAERHOUSE-LOCATIONISSN';

    public function indexAction($data=null) {
        $this->view->pageTitle = 'Search Location';

        try {

            $this->view->locationSsName     = $this->view->searchFormSsName = self::LOCATION_MODEL_NAME;
            $this->view->locationNamespace  = self::LOCATION_NAMESPACE;

            $this->view->issnSsName     = self::ISSN_MODEL_NAME;
            $this->view->issnNamespace  = self::ISSN_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::LOCATION_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            /**
            * @var Minder_SysScreen_Model_Location $locationModel
            */
            if(($data=='LOCATION')||($data==null)) {
		$locationModel   = $screenBuilder->buildSysScreenModel(self::LOCATION_MODEL_NAME, new Minder_SysScreen_Model_Location());
	    }
	    if($data=='BORROWER') {
		$locationModel   = $screenBuilder->buildSysScreenModel(self::LOCATION_MODEL_NAME, new Minder_SysScreen_Model_Borrower());
	    }
            $locationModel->setConditions($locationModel->makeConditionsFromSearch($searchFields));

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $locationModel, true, self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            /**
             * @var Minder_SysScreen_Model_LocationIssn issnModel
             */
            $issnModel = $screenBuilder->buildSysScreenModel(self::ISSN_MODEL_NAME, new Minder_SysScreen_Model_LocationIssn());
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens', array(self::ISSN_NAMESPACE => array(), self::LOCATION_NAMESPACE => array()));

            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->locationJsSearchResults = $this->view->jsSearchResult(
                                                    self::LOCATION_MODEL_NAME,
                                                    self::LOCATION_NAMESPACE,
                                                    array('sysScreenCaption' => 'LOCATION LIST', 'usePagination'    => true)
            );
            $this->view->locationJsSearchResultsDataset = array();

            $this->view->issnJsSearchResults = $this->view->jsSearchResult(
                                                    self::ISSN_MODEL_NAME,
                                                    self::ISSN_NAMESPACE,
                                                    array('sysScreenCaption' => 'ISSN LIST', 'usePagination'    => true)
            );
            $this->view->issnJsSearchResultsDataset = array();
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        return;

    }

    protected function initSubDatasets() {
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        $selectedLocationsAmount = $rowSelector->getSelectedCount(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        /**
        * @var Minder_SysScreen_Model_LocationIssn $issnModel
        */
        $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedLocationsAmount > 0) {
            /**
            * @var Minder_SysScreen_Model_Location $locationModel
            */
            $locationModel    = $rowSelector->getModel(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $locationModel->addConditions($rowSelector->getSelectConditions(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $selectedLocationsAmount));

            $issnModel->setLocationLimit($locationModel->selectLocnIdAndWhId(0, $selectedLocationsAmount));
        } else {
            $issnModel->setConditions(array('1 = 2' => array()));
        }
        $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    public function getDatasetAction() {
        $datasets = array(
            self::LOCATION_NAMESPACE   => self::LOCATION_MODEL_NAME,
            self::ISSN_NAMESPACE       => self::ISSN_MODEL_NAME
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

                    if ($namespace == self::ISSN_NAMESPACE)
                        $tmpSysScreen->firstSelectedRow = $this->_getIssnFirstSelectedRow();
                }


                if ($namespace == self::LOCATION_NAMESPACE)
                    $this->initSubDatasets();

                $result->sysScreens[$namespace] = $tmpSysScreen;
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    protected function _getIssnFirstSelectedRow() {
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');
        /**
         * @var Minder_SysScreen_Model_LocationIssn $issnModel
         */
        $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));

        if (false !== ($tmpRow = $issnModel->getItems(0, 1, false)) && (count($tmpRow) > 0)) {
            return current($tmpRow);
        }

        return array();
    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    public function printLocationLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();
	$text='LOCATION';
	$this->indexAction($text);
        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_Location $locationModel
             */
            $locationModel = $rowSelector->getModel(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $locationModel->addConditions($rowSelector->getSelectConditions(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $printResult   = $locationModel->printLabel($this->minder->getPrinter());

            $result->messages += $printResult->messages;
            $result->warnings += $printResult->warnings;
            $result->errors   += $printResult->errors;
        } catch (Exception $e) {
            $result->errors[] = 'Error printing Location Label: ' . $e->getMessage();
        }

        echo json_encode($result);
    }


/****************************/
	public function printBorrowerLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();
	$text='BORROWER';
	$this->indexAction($text);
        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_Borrower $borrowerModel
             */
            $locationModel = $rowSelector->getModel(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $locationModel->addConditions($rowSelector->getSelectConditions(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $printResult   = $locationModel->printLabel($this->minder->getPrinter());

            $result->messages += $printResult->messages;
            $result->warnings += $printResult->warnings;
            $result->errors   += $printResult->errors;
        } catch (Exception $e) {
            $result->errors[] = 'Error printing Location Label: ' . $e->getMessage();
        }

        echo json_encode($result);
    }
/****************************/

    protected function printProductLabelAction() {
        $this->_helper->viewRenderer->setNoRender();
        $response                = new stdClass();
        $response->errors        = array();
        $response->warnings      = array();
        $response->messages      = array();
        $printedCount            = 0;

        try {
            $request             = $this->getRequest();
            $selectionNamespace  = $request->getParam('selection_namespace', 'default');
            $selectionAction     = $request->getParam('selection_action');
            $selectionController = $request->getParam('selection_controller');
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector         = $this->_helper->rowSelector;
            $selectedRowsCount   = $rowSelector->getSelectedCount($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $productLabelType    = $request->getParam('product_label_type', 'PRODUCT_LABEL');
            $firstLabelQty       = $request->getParam('first_label_qty', 1);
            $firstLabelTotal     = $request->getParam('first_label_total', 1);
            $secondLabelQty      = $request->getParam('second_label_qty', 0);
            $secondLabelTotal    = $request->getParam('second_label_total', 0);

            if ($selectedRowsCount < 1) {
                $response->errors[] = 'No rows selected. Nothing to print.';
                return $response;
            }

            /**
             * @var Minder_SysScreen_Model_LocationIssn $rowsModel
             */
            $rowsModel           = $rowSelector->getModel($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController));

            $labelData           = $rowsModel->selectProductLabelData(0, count($rowsModel));

            if (count($labelData) > 1)
                throw new Minder_Exception('Only one Product Label printing at the same time is allowed.');

            $printerObj          = $this->minder->getPrinter();
            $labelPrinter        = Minder_LabelPrinter_Factory::getLabelPrinter($productLabelType);

            foreach ($labelData as $labelDataRow) {
                $labelDataRow['PACK_QTY']       = $firstLabelQty;
                $labelDataRow['TOTAL_ON_LABEL'] = $firstLabelTotal;
                $labelDataRow['labelqty']       = $labelDataRow['PACK_QTY'];
                $result                         = $labelPrinter->directPrint(array($labelDataRow), $printerObj);

                if($result->hasErrors()){
                    throw new Minder_Exception(implode('. ', $result->errors));
                }

                $printedCount++;

                if (is_numeric($secondLabelQty) && $secondLabelQty > 0) {
                    $labelDataRow['PACK_QTY']       = $secondLabelQty;
                    $labelDataRow['TOTAL_ON_LABEL'] = $secondLabelTotal;
                    $labelDataRow['labelqty']       = $labelDataRow['PACK_QTY'];
                    $result                         = $labelPrinter->directPrint(array($labelDataRow), $printerObj);

                    if($result->hasErrors()){
                        throw new Minder_Exception(implode('. ', $result->errors));
                    }
                    $printedCount++;
                }
            }

            if ($printedCount > 0) {
                $response->messages[] = 'Print request successfully sent for ' . $printedCount . ' labels.';
            } else {
                $response->errors[] = 'Product not found.';
            }
        } catch (Exception $e) {
            $response->errors[]  = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($response);
    }

    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();

        try{
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::LOCATION_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);




$session = new Zend_Session_Namespace();
              $tz_from=$session->BrowserTimeZone;


$array_new=$searchFields;

foreach ($array_new as $key => $value) {
            foreach($value as $key1=>$val1){

                if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') {
unset($array_new[$key]); }


            }

}



            foreach($array_new as $key=>$val){

                                foreach($val as $key1=>$val1){

                                                    if($key1=='SEARCH_VALUE'&& $val1!=''){


                                                            if
(DateTime::createFromFormat('Y-m-d
H:i:s', $val1)
!== FALSE  ||
DateTime::createFromFormat('Y-m-d',
$val1) !==
FALSE) {


                                                                                    $datetimet
=
$val1;
                                                                                    $tz_tot
=
'UTC';
                                                                                    $format
=
'Y-m-d
h:i:s';

                                                                                    $dtt
=
new
DateTime($datetimet,
new
DateTimeZone($tz_from));
                                                                                    $dtt->setTimeZone(new
DateTimeZone($tz_tot));

                                                                                    $searchFields[$key][$key1]=$dtt->format($format);

                                                                }

                                                    }


                                }

            }






            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model $rowsModel
            */
            $rowsModel    = $rowSelector->getModel(self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::LOCATION_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->initSubDatasets();

            /**
             * @var Minder_SysScreen_Model_LocationIssn $issnModel
             */
            $issnModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $productCondition = $issnModel->makeProductSearch($searchFields);

            if (empty($productCondition)) {
                $issnModel->removeProductCondition();
            } else {
                $issnModel->addProductCondition($productCondition);
            }
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    public function printIssnLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();

        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector       = $this->_helper->getHelper('RowSelector');
            $selectedIssnCount = $rowSelector->getSelectedCount(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if ($selectedIssnCount < 1) {
                $this->view->errors[] = 'No ISSNs selected. Please, select one.';
                return $this->_forward('get-dataset');
            }

            /**
            * @var Minder_SysScreen_Model_LocationIssn $rowsModel
            */
            $rowsModel = $rowSelector->getModel(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $rowsModel->addConditions($rowSelector->getSelectConditions(self::ISSN_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $result = $rowsModel->printIssnLabel($this->minder->getPrinter());
            $response->errors   = array_merge($response->errors, $result->errors);
            $response->messages = array_merge($response->messages, $result->messages);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        echo json_encode($response);
    }

}
