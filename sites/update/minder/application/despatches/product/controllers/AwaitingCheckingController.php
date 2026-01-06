<?php

class Despatches_AwaitingCheckingController extends Minder_Controller_Action
{
    const CHECK_LINE_DETAILS = 'CHECK_LINE_DETAILS';
    const EDI_ONE_STATUS = 'EDI_ONE_STATUS';
    const SSCC_LABELS = 'SSCC_LABELS';
    const EXIT_CARRIER_MODEL = 'AWAITING_EXIT_CARRIER';
    const EXIT_CARRIER_NAMESPACE = 'AC-AWAITING_EXIT_CARRIER';
    const SEARCH_ACTION = 'index';
    const SEARCH_NAMESPACE = 'default';

    const ORDER_CHECK_LINES_EDI = 'ORDERCHECKLINESEDI';
    const ORDER_CHECK_LINES_EDI_NS = 'AS-ORDERCHECKLINESEDI';
    const OUT_SSCC_KEY = 'OUT_SSCC_KEY';

    public static $orderModel        = 'ORDERCHECK';
    public static $linesModel        = 'ORDERCHECKLINES';
    public static $despatchToModel   = 'ORDERDESPATCHTO';
    public static $instructionsModel = 'ORDERINSTRUCTIONS';
    public static $dimensionsModel   = 'ORDERPACKDIMS';
    public static $ssccDimensionsModel = 'SSCC_PACK_DIMS';
    public static $repackDimensionsModel = 'SSCC_REPACK_DIMS';

    protected $mAllowedParams = array(
                                    'LOCATION'   => 'SEARCH-PICK_ORDER-DESPATCH_LOCATION', 
                                    'SALESORDER' => 'SEARCH-PICK_ORDER-PICK_ORDER'
                                );
                                
    protected $mSearchFields  = array(
                                    array('RECORD_ID' => 'DACC_SE_1', 'SSV_ALIAS' => 'DESPATCH_LOCATION', 'SSV_TABLE' => 'PICK_ORDER', 'SSV_NAME' => 'DESPATCH_LOCATION', 'SSV_INPUT_METHOD' => 'IN'), 
                                    array('RECORD_ID' => 'DACC_SE_2', 'SSV_ALIAS' => 'PICK_ORDER',        'SSV_TABLE' => 'PICK_ORDER', 'SSV_NAME' => 'PICK_ORDER',        'SSV_INPUT_METHOD' => 'IN')
                                );
                                
    protected $mStaticConditions = array("(PICK_ORDER.PICK_ORDER_TYPE IN (?, ?))" => array('SO', 'TO'));
    
    public function init() {
        parent::init();
        $this->view->pageTitle = 'AWAITING CHECKING';
        $this->_helper->addPrefix('Minder_Controller_Action_Helper');
        
        $this->view->orderSelectionNamespace  = self::$orderModel;
        $this->view->orderSelectionAction     = 'select-row';
        $this->view->orderSelectionController = 'awaiting-checking';
        $this->view->orderSelectionModule     = 'despatches';

        $this->view->linesSelectionNamespace  = self::$linesModel;
        $this->view->linesSelectionAction     = 'select-row';
        $this->view->linesSelectionController = 'awaiting-checking';
        $this->view->linesSelectionModule     = 'despatches';
        
        $this->view->orderSysScreenName       = self::$orderModel;
        $this->view->linesSysScreenName       = self::$linesModel;
    }
    
    public function indexAction() {
        $log = Minder_Registry::getLogger()->startDetailedLog();
        $log->starting('Stage 1: searching for Orders....');

        $this->view->volumeRequired = false;
        $this->view->weightRequired = false;
        $this->view->errors   = array();
        $this->view->warnings = array();
        $this->view->scannedSsccLabels = $this->_awaitingCheckingHelper()->loadSsccDimensions();
        $this->view->knownCarriers          = array();
        $this->view->carriersList           = array();
        $this->view->carrierServiceTypes    = array();
        $this->view->isEdi                  = false;
        $this->view->deviceCarriersList     = array();
        $this->view->shouldCheckEachLine    = false;
        $this->view->shouldCreateSSCC       = false;
        $this->view->shouldRecordSerialNumber = false;
        $this->view->canBeChecked           = false;
        $this->view->orderCheckEdiSearchResults = $this->_getJsSearchResultBuilder()->buildEmptyResult(static::ORDER_CHECK_LINES_EDI, static::ORDER_CHECK_LINES_EDI_NS);
        $this->view->orderCheckEdiScreenData = array();
        $this->view->orderStatistics        = new Minder_Controller_Action_Helper_AwaitingCheckingEdi_OrderStatistics();

        try {
            $this->view->knownCarriers          = array_values($this->minder->getShipViaList());
            $this->view->carriersList           = $this->minder->getCarriers();
            $this->view->carrierServiceTypes    = $this->minder->getCarrierServiceTypes();
            $this->view->deviceCarriersList     = $this->minder->getDeviceCarrierList();

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $screenBuilder   = new Minder_SysScreen_Builder();

        if ($this->_awaitingCheckingHelper()->supportsEdiCheckingMethod()) {
            try {
                $screenDescription = $screenBuilder->getSysScreenDescription(self::$orderModel);
                $screenDescription['SS_VIEW_BY_VALUE'] = empty($screenDescription['SS_VIEW_BY_VALUE']) ? 5 : $screenDescription['SS_VIEW_BY_VALUE'];
                $this->session->navigation[$this->_controller][$this->_action]['show_by'] = isset($this->session->navigation[$this->_controller][$this->_action]['show_by']) ?
                    $this->session->navigation[$this->_controller][$this->_action]['show_by'] :
                    $screenDescription['SS_VIEW_BY_VALUE'];
                $this->view->viewByMax = $screenDescription['SS_VIEW_BY_MAX'];
                $this->view->palletOwnerList    = minder_array_merge(array('NONE' => 'NONE'), $this->minder->getPalletOwnerList());

                $tmpModel = new Minder_SysScreen_Model_OrderPackDims();
                $sqlDataSource = new Minder_SysScreen_DataSource_Sql();
                $this->view->dim_screen = $this->_packDimensions()->getScreenDescription(self::$ssccDimensionsModel, $screenBuilder, $sqlDataSource, $tmpModel);
                $this->view->repack_old_screen = $this->_packDimensions()->getScreenDescription(self::$repackDimensionsModel, $screenBuilder, $sqlDataSource, $tmpModel);
                $this->view->repack_new_screen = $this->_packDimensions()->getScreenDescription(self::$repackDimensionsModel, $screenBuilder, $sqlDataSource, $tmpModel);
            } catch (Exception $e) {
                $this->view->errors[] = $e->getMessage();
            }
        }

        $this->_preProcessNavigation();

        $this->view->searchForm         = '';
        $this->view->screenButtonsParam = array();
        $this->view->carrierIdParam     = array();

        try {
            $connoteBarcodes = $this->minder->getConnoteBarcodeDataIds();
            $this->view->connoteBarcodeDescriptions = array();
            foreach ($connoteBarcodes as $dataId) {
                try {
                    $this->view->connoteBarcodeDescriptions[] = $this->view->SymbologyPrefixDescriptor($dataId['DATA_ID']);
                } catch (Exception $e) {
                    $this->view->errors[] = 'CONNOTE_PARAM_ID = "' . $dataId['DATA_ID'] .'" is defined in CARRIER table. But was not found in PARAM table. Check system setup.';
                }
            }
        } catch (Exception $e) {
            $this->view->errors[] = 'Cannot get CONNOTE barcode label descriptions for CARRIERS: ' . $e->getMessage() . ' Check system setup.';
        }

        try {
            $request       = $this->getRequest();
            $postAction    = $request->getPost('action', 'none');
            $searchKeeper  = $this->_helper->searchKeeper;
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::$orderModel, true);
        
            $searchFields  = array_merge($tmpSearchFields, $tmpGISearchFields);

            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->rowSelector;
        
            $selectionState = 'init';
            $defaultSelectionMode = $screenBuilder->getSysScreenDefaultSelectionMode(static::$orderModel);

            switch (strtoupper($postAction)) {
                case 'SEARCH' :
                    $this->_forgetEdiOneStatus();
                    $this->_cleanCheckLineDetails();
                    $this->_awaitingCheckingHelper()->cleanSsccDimensions();
                    $this->_awaitingCheckingHelper()->cleanSsccLabels();
                    $this->_awaitingCheckingHelper()->cleanSerialNumbers();
                    $this->view->scannedSsccLabels = array();
                    $this->_helper->viewRenderer->setScriptAction('orders');

                    $searchFields   = $searchKeeper->makeSearch($searchFields);
                    $selectionState = 'true';
                    $log->classicInfo(__METHOD__ .  ': Got search request: ' . var_export($searchFields, true));
                    $rowSelector->setSelectionMode($defaultSelectionMode, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                    break;
                case 'GET-ORDERS':
                    $this->_helper->viewRenderer->setScriptAction('orders');

                case 'CANCEL-DESPATCH':
                    $this->_helper->viewRenderer->setScriptAction('orders');
                    $searchFields = $searchKeeper->makeSearch($searchFields);
                    $selectionState = 'false';
                    break;

                case 'RELOAD-ORDERS':
                    $this->_helper->viewRenderer->setScriptAction('orders');
                    $searchFields = $searchKeeper->getSearch($searchFields);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields);
            }

            $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
            $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);

            /**
             * @var Minder_SysScreen_Model_AwaitingCheckingOrders $ordercheckModel
             */
            $ordercheckModel  = new Minder_SysScreen_Model_AwaitingCheckingOrders();
            $ordercheckModel  = $screenBuilder->buildSysScreenModel(self::$orderModel, $ordercheckModel);
            
            $searchConditions = $ordercheckModel->makeConditionsFromSearch($searchFields);
            $ordercheckModel->setConditions($searchConditions);
            
            if (strtoupper($postAction) == 'SEARCH') {
                //move PICK_ITEMS to device location
                list($whId, $locnId) = $this->minder->getDeviceWhAndLocation();
                if (!$this->minder->isDespatchLocation($whId, $locnId)) {
                    $this->view->errors[] = 'Current device is not defined as despatch location.';
                } else {
//                    $ordercheckModel->movePickItemsToDespatchLocation($whId . $locnId, $searchFields);
                }
            }

            $totalRows = count($ordercheckModel);

            $rowSelector->setDefaultSelectionMode($defaultSelectionMode, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);

            $rowSelector->setRowSelection('select_complete', $selectionState, null, null, $ordercheckModel, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);

            if ($totalRows > 0) {
                $firstRow = current($ordercheckModel->getItems(0, 1, true));
                $rowId = $firstRow[$ordercheckModel->getPKeyAlias()];
                $rowSelector->setRowSelection($rowId, $selectionState, null, null, $ordercheckModel, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            }

            $this->_postProcessNavigation(array('total' => $totalRows));
            $pageSelector        = $this->view->navigation['pageselector'];
            $showBy              = $this->view->navigation['show_by'];
            $this->view->dataSet = array();
            $this->view->selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $this->view->selectedOrders      = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            if ($totalRows > 0) {
                $this->view->dataSet = $ordercheckModel->getItems($pageSelector * $showBy, $showBy, false);
            } else {
                if (strtoupper($postAction) == 'SEARCH') {
                    $this->view->errors[] = 'No orders found.';
                }
            }

            if ($this->view->selectedOrdersCount > 0) {
                $ordercheckModel->addConditions($rowSelector->getSelectConditions($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController));
                $pickOrders = $ordercheckModel->getPickOrders(0, $this->view->selectedOrdersCount);
                $this->view->isEdi               = $pickOrders->hasEdiOrders();
                $this->view->shouldCreateSSCC    = $this->_awaitingCheckingHelper()->shouldCreateSscc($pickOrders);
                $this->view->shouldCheckEachLine = $this->view->shouldCreateSSCC || $this->_awaitingCheckingHelper()->shouldCheckEachLine($pickOrders);
                $this->view->canBeChecked        = $pickOrders->readyToCheck();
                $this->view->shouldRecordSerialNumber = $this->_awaitingCheckingHelper()->shouldRecordSerialNumber($pickOrders);

                $orderCompany               = $this->_companyHelper()->find(current($pickOrders->COMPANY_ID));
                $this->view->volumeRequired = $orderCompany->isDespatchVolumeRequired();
                $this->view->weightRequired = $orderCompany->isDespatchWeightRequired();

                if ($this->view->isEdi) {
                    $dcNo = $this->_awaitingCheckingEdiHelper()->getDcNoByLocationId($this->_getPickBlockSearchValue());
                    $this->view->orderStatistics    = $this->_awaitingCheckingEdiHelper()->calculateEdiOrderStatistics($pickOrders, $dcNo);
                }
            }

            list($this->view->fieldList, $this->view->tabList) = $screenBuilder->buildSysScreenSearchResult(self::$orderModel, true);
            $this->view->selectMode          = $rowSelector->getSelectionMode('', $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $this->view->searchForm          = $this->view->sysScreenSearchForm2('ORDERCHECK', array('register-search-handlers' => true));
            $this->view->screenButtonsParam  = $this->view->SymbologyPrefixDescriptor('SCREEN_BUTTON');
            $this->view->carrierIdParam      = $this->view->SymbologyPrefixDescriptor('CARRIER_ID');
            $this->view->productParams       = $this->_paramMangerHelper()->generateSymbologyPrefixDescriptors(array(), array('PROD_ID', 'SSCC', 'PROD_EAN', 'SERIAL_NUMBER'));

            if ($this->_awaitingCheckingHelper()->supportsEdiCheckingMethod()) {
                /**
                 * @var Minder_SysScreen_Model_OrderCheckLinesEdi $orderCheckLinesEdiModel
                 */
                $orderCheckLinesEdiModel = $screenBuilder->buildSysScreenModel(static::ORDER_CHECK_LINES_EDI, new Minder_SysScreen_Model_OrderCheckLinesEdi());
                $this->_rowSelector()->setSelectionModel(
                    $orderCheckLinesEdiModel,
                    static::ORDER_CHECK_LINES_EDI_NS,
                    self::$defaultSelectionAction,
                    self::$defaultSelectionController
                );
                $this->view->orderCheckEdiSearchResults = $this->_getJsSearchResultBuilder()->buildScreenSearchResult(
                    static::ORDER_CHECK_LINES_EDI,
                    static::ORDER_CHECK_LINES_EDI_NS,
                    array(
                        'canSelect' => false
                    )
                );
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $this->view->sysScreens = array();
        $this->view->exitCarrierResults = $this->_getJsSearchResultBuilder()->buildEmptyResult(static::EXIT_CARRIER_MODEL, static::EXIT_CARRIER_NAMESPACE);
        try {
            $exitCarrierModel = $this->_getModelBuilder()->buildSysScreenModel(static::EXIT_CARRIER_MODEL);
            $this->_rowSelector()->setRowSelection('select_complete', 'init', null, null, $exitCarrierModel, true, static::EXIT_CARRIER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->exitCarrierResults = $this->_getJsSearchResultBuilder()->buildScreenSearchResult(static::EXIT_CARRIER_MODEL, static::EXIT_CARRIER_NAMESPACE);
            $this->getRequest()->setParam('sysScreens', array(
                self::EXIT_CARRIER_NAMESPACE=> array(),
            ));
            $this->view->sysScreens = $this->_buildDatatset(array(static::EXIT_CARRIER_MODEL => static::EXIT_CARRIER_NAMESPACE));
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage() . ' Exit Carrier dialog will not work.';
        }

        $log->done('.... Done stage 1');
    }

    public function getDatasetAction() {
        $this->view->sysScreens = $this->_buildDatatset(array(static::EXIT_CARRIER_MODEL => static::EXIT_CARRIER_NAMESPACE));
        $this->_viewRenderer()->setNoRender();
        echo $this->_datasetToJson($this->view);
    }

/************************************************/

    //Function to call updatePickItemCheckQty()

    public function getProdidAction() {
	
	$prodid=$_POST['prod_id'];
	$pickorder=$this->session->pick_order_session;
	$this->minder->updatePickItemCheckQty($prodid,$pickorder);
	exit;
    }

    //Function to set pick_order session variable

    public function getPickorderAction() {
	
	$str=$_POST['pickorder'];
	$str1=substr($str,0,3);
	if($str1==']C0') {
		$str2=substr($str,3);
		$this->session->pick_order_session = $str2;
		echo $this->session->pick_order_session;
	}
	exit;
    }

    //Function to select CHECKIN_QTY

    public function getCheckinqtyAction() {
	$prodid=$_POST['prod_id'];
	$pickorder=$this->session->pick_order_session;
	$checkinqty=$this->minder->selectPickItemCheckinQty($prodid,$pickorder);
	echo $checkinqty;
	exit;
    }

/************************************************/

    public function getOrderCheckLinesDataAction() {
        /**
         * @var Minder_SysScreen_Model_OrderCheckLinesEdi $linesModel
         */
        $linesModel = $this->_rowSelector()->getModel(static::ORDER_CHECK_LINES_EDI_NS, static::$defaultSelectionAction, static::$defaultSelectionController);
        $orders = $this->_getSelectedOrders();
        $linesModel->setOrderSelectionLimit($orders);

        $status = $this->_getStoredOrDefaultEdiOneStatus();

        if ($status['checkingStarted'] && !$status['completed']) {
            $linesModel->setOutSsccLimit($status['outSscc']);
        } else {
            $linesModel->setOutSsccLimit('');
        }

        $this->_rowSelector()->setSelectionModel($linesModel, static::ORDER_CHECK_LINES_EDI_NS, self::$defaultSelectionAction, self::$defaultSelectionController);

        $this->view->sysScreens = $this->_buildDatatset(array(static::ORDER_CHECK_LINES_EDI => static::ORDER_CHECK_LINES_EDI_NS));
        $this->_viewRenderer()->setNoRender();

        $screenPagination = $this->restorePagination(static::ORDER_CHECK_LINES_EDI_NS);

        $dcNo = $this->_awaitingCheckingEdiHelper()->getDcNoByLocationId($this->_getPickBlockSearchValue());
        $status = $this->_getStoredOrDefaultEdiOneStatus();

        $this->view->checkingStatus     = $status;
        $this->view->recordIdMap        = $linesModel->selectRecordIdMap($screenPagination['totalRows']);

        $this->_awaitingCheckingEdiHelper()->fillEdiOnePackSsccCheckData($orders, $dcNo, $status['outSscc'], $this->view);

        echo $this->_datasetToJson($this->view);
    }

    public function startSsccCheckAction() {
        $outSscc = $this->getRequest()->getParam('sscc');
        $this->_initViewMessagesContainers();
        $this->_forgetEdiOneStatus();

        $status = $this->_getStoredOrDefaultEdiOneStatus();

        try {
            $result = $this->_awaitingCheckingEdiHelper()->startSsccCheck($outSscc, $this->_getSelectedOrders());

            if (!$result->hasErrors()) {
                $status['outSscc']          = $outSscc;
                $status['checkingStarted']  = true;
                $this->_storeEdiOneStatus($status);
            }

            $this->_copyMessagesToView($result);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $this->_forward('get-order-check-lines-data');
    }

    public function cancelSsccCheckAction() {
        $outSscc = $this->getRequest()->getParam('sscc');
        $this->_initViewMessagesContainers();

        try {
            $this->_awaitingCheckingEdiHelper()->cancelSsccCheck($outSscc);
            $this->_forgetEdiOneStatus();
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $this->_forward('get-order-check-lines-data');
    }

    public function selectCarrierExitRowAction() {
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

    public function selectRowAction() {
        $this->view->errors = array();
        $this->_preProcessNavigation();

        $response = new stdClass();
        $response->selected     = 0;
        $response->errors       = array();
        $response->warnings     = array();
        $response->selectedRows = array();
        $response->selectionNamespace = 'default';
        $response->hasPrintedSsccLabels = false;

        $request            = $this->getRequest();
        $selectionNamespace = $request->getParam('selection_namespace', 'default');
        $rowId              = $request->getParam('row_id', 'none');
        $state              = $request->getParam('state',  'none');
        
        $selectionMode      = $request->getParam('selection_mode');

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector  = $this->_helper->rowSelector;
        $count = 0;

        try {
            $screenBuilder    = new Minder_SysScreen_Builder();

            switch ($selectionNamespace) {
                case $this->view->orderSelectionNamespace:
//                    $rowsModel    = new Minder_SysScreen_Model_AwaitingCheckingOrders();
//                    $rowsModel    = $screenBuilder->buildSysScreenModel(self::$orderModel, $rowsModel);
                    $pageselector = $request->getParam('pageselector', $this->session->navigation[$this->_controller]['index']['pageselector']);
                    $showBy       = $request->getParam('show_by', $this->session->navigation[$this->_controller]['index']['show_by']);
                    $this->_cleanCheckLineDetails();
                    $this->_awaitingCheckingHelper()->cleanSsccLabels();
                    $this->_awaitingCheckingHelper()->cleanSerialNumbers();
                    $this->_forgetEdiOneStatus();
                    break;
                case $this->view->linesSelectionNamespace:
//                    $rowsModel    = $screenBuilder->buildSysScreenModel(self::$linesModel);
                    $pageselector = $request->getParam('pageselector', $this->session->navigation[$this->_controller]['get-lines']['pageselector']);
                    $showBy       = $request->getParam('show_by', $this->session->navigation[$this->_controller]['get-lines']['show_by']);
                    break;
            }

            $rowSelector->setSelectionMode($selectionMode, $selectionNamespace);
            $rowSelector->setRowSelection($rowId, $state, $pageselector, $showBy, null, false, $selectionNamespace);
            $response->selected           = $rowSelector->getSelectedCount($selectionNamespace);
            $response->selectionNamespace = $selectionNamespace;
            
            $totalRows                    = $rowSelector->getTotalCount($selectionNamespace);
            $selectedRows                 = $rowSelector->getSelected(0, $totalRows, false, $selectionNamespace);
            $response->selectedRows       = $selectedRows;

            if ($selectionNamespace == $this->view->linesSelectionNamespace) {
                /**
                 * @var Minder_SysScreen_Model_OrderCheckLine $rowsModel
                 */
                $rowsModel = $rowSelector->getModel($selectionNamespace);
                $badStatusArr = array();
                $readyForDespRows = array();
                $itemsWithZeroQty = array();
                $pickLabelNos = $rowsModel->selectPickLabelNos(0, count($rowsModel));
                foreach ($rowsModel->getDespatchStatus($pickLabelNos) as $tmpRrowId => $row) {
                    if ($row['HAS_PRINTED_SSCC_LABELS'] == 'T') {
                        $response->hasPrintedSsccLabels = true;
                    }

                    if (!$rowsModel->isReadyForDespatch($row)) {
                        $key = $row[$rowsModel->getPKeyAlias()];

                        if (isset($selectedRows[$key])) {
                            $badStatusArr[$row['PICK_LINE_STATUS']] = $row['PICK_LINE_STATUS'];
                        }
                    } else {
                        $readyForDespRows[$row[$rowsModel->getPKeyAlias()]] = $row;
                    }

                    if ($row['PICKED_QTY'] < 1 && $row['PICK_LINE_STATUS'] !== 'AS') {
                        $key = $row[$rowsModel->getPKeyAlias()];
                        if (isset($selectedRows[$key])) {
                            $itemsWithZeroQty[] = $row[$rowsModel->getPKeyAlias()];
                        }
                    }

                }
                if (count($badStatusArr) > 0) {
                    $response->errors[] = "Cannot Despatch Lines with Status = ('" . implode("', '", $badStatusArr) . "')";
                }

                if (count($itemsWithZeroQty) > 0) {
                    $response->errors[] = 'Item(s) ' . implode(', ', $itemsWithZeroQty) . ' have Picked Qty = 0.';
                }
                $response->readyForDespSelected = count(array_intersect_key($readyForDespRows, $selectedRows));
            }

            if ($selectionNamespace == $this->view->orderSelectionNamespace) {
                $pickOrders                     = $this->_getSelectedOrders();
                $orderCompany                   = $this->_companyHelper()->find(current($pickOrders->COMPANY_ID));

                $response->isEdi                = $pickOrders->hasEdiOrders();
                $response->shouldCreateSSCC     = $this->_awaitingCheckingHelper()->shouldCreateSscc($pickOrders);
                $response->shouldCheckEachLine  = $response->shouldCreateSSCC || $this->_awaitingCheckingHelper()->shouldCheckEachLine($pickOrders);
                $response->canBeChecked         = $pickOrders->readyToCheck();
                $response->volumeRequired       = $orderCompany->isDespatchVolumeRequired();
                $response->weightRequired       = $orderCompany->isDespatchWeightRequired();
                $response->shouldRecordSerialNumber = $this->_awaitingCheckingHelper()->shouldRecordSerialNumber($pickOrders);
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function getLinesAction() {
        $log = Minder_Registry::getLogger()->startDetailedLog();
        $log->starting('Stage 2: fetching lines....');

        $this->view->errors = array();
        $this->view->warnings = array();
        $this->view->hasPrintedSsccLabels = false;

        $screenBuilder   = new Minder_SysScreen_Builder();
        try {
            $screenDescription = $screenBuilder->getSysScreenDescription(self::$linesModel);
            $screenDescription['SS_VIEW_BY_VALUE'] = empty($screenDescription['SS_VIEW_BY_VALUE']) ? 5 : $screenDescription['SS_VIEW_BY_VALUE'];
            $this->session->navigation[$this->_controller][$this->_action]['show_by'] = isset($this->session->navigation[$this->_controller][$this->_action]['show_by']) ?
                $this->session->navigation[$this->_controller][$this->_action]['show_by'] :
                $screenDescription['SS_VIEW_BY_VALUE'];
            $this->view->viewByMax = $screenDescription['SS_VIEW_BY_MAX'];
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $this->_preProcessNavigation();
        
        $request     = $this->getRequest();
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->rowSelector;

        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);

        $this->view->shouldCheckEachLine = false;
        $this->view->shouldCreateSSCC = false;
        $this->view->canBeChecked = false;
        $this->view->checkLineDetails = array();
        $this->view->linesDataSet = array();
        $this->view->selectedLinesCount = 0;
        $this->view->selectedLines      = array();
        $this->view->selectMode         = $rowSelector->getSelectionMode('', $this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
        $this->view->packSscc           = array();
        $this->view->serialNumbers      = array();
        $this->view->selectableRows     = true;
        $this->view->orderStatistics    = new Minder_Controller_Action_Helper_AwaitingCheckingEdi_OrderStatistics();

        $checkLineDetails               = array();

        try {
            $selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            if ($selectedOrdersCount > 0) {
                list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::$orderModel, true);

                $searchFields  = array_merge($tmpSearchFields, $tmpGISearchFields);
                $searchFields = $this->_searchHelper()->getSearch($searchFields, static::SEARCH_NAMESPACE, static::SEARCH_ACTION);

                /**
                 * @var Minder_SysScreen_Model_OrderCheckLine $pickItemsModel
                 */
                $pickItemsModel  = new Minder_SysScreen_Model_OrderCheckLine();
                $pickItemsModel  = $screenBuilder->buildSysScreenModel(self::$linesModel, $pickItemsModel);

                /**
                 * @var Minder_SysScreen_Model_AwaitingCheckingOrders $ordersModel
                 */
                $ordersModel = $rowSelector->getModel($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                $ordersModel->addConditions($rowSelector->getSelectConditions($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController));
                $pickOrders = $ordersModel->getPickOrders(0, $selectedOrdersCount);

                if ($pickOrders->hasEdiOrders()) {
                    if ($this->_searchHelper()->isLabelSearch($searchFields, array('PICK_BLOCK'))) {
                        $searchField = $this->_searchHelper()->getLabelSearch($searchFields, 'PICK_BLOCK');
                        $pickItemsModel->addPickBlockLimit($searchField['SEARCH_VALUE']);
                    } else {
                        $pickItemsModel->removePickBlockLimit();
                    }
                } else {
                    $pickItemsModel->removePickBlockLimit();
                }

                $this->view->canBeChecked = $pickOrders->readyToCheck();
                $this->view->badLines    = false;
                $this->view->selectableRows = !$pickOrders->hasOrdersWhichCannotBePartiallyDespatched();

                foreach ($pickOrders->PICK_STATUS as $status) {
                    if ($status != 'DA') {
                        $this->view->badLines    = true;
                        
                        $this->view->errors[] = 'Bad selected Order status.';
                    }
                }

                $this->view->shouldCreateSSCC    = $this->_awaitingCheckingHelper()->shouldCreateSscc($pickOrders);
                $this->view->shouldCheckEachLine = $this->view->shouldCreateSSCC || $this->_awaitingCheckingHelper()->shouldCheckEachLine($pickOrders);

                $pickItemsModel->setConditions(array('PICK_ITEM.PICK_ORDER IN (' . substr(str_repeat('?, ', $selectedOrdersCount), 0, -2) . ')' => $pickOrders->PICK_ORDER));
                $rowSelector->setRowSelection('select_complete', 'false', null, null, $pickItemsModel, true, $this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);

                $totalRows = count($pickItemsModel);
                $readyToDespRows  = array();
                $itemsWithBadWh = array();
                $itemsWithZeroQty = array();
                $pickLabelNos = $pickItemsModel->selectPickLabelNos(0, $totalRows);

                foreach ($pickItemsModel->getDespatchStatus($pickLabelNos) as $row) {
                    if ($row['HAS_PRINTED_SSCC_LABELS'] == 'T') {
                        $this->view->hasPrintedSsccLabels = true;
                    }

                    if ($pickItemsModel->isReadyForDespatch($row)) {
                        if ($pickItemsModel->isWhValid($row)) {
                            $pkey = $row[$pickItemsModel->getPKeyAlias()];
                            $rowSelector->setRowSelection($pkey, 'true', 0, 1, $pickItemsModel, false, $this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
                            $readyToDespRows[$pkey] = $row;
                        } else {
                            $itemsWithBadWh[] = $row[$pickItemsModel->getPKeyAlias()];
                        }

                        if ($row['PICKED_QTY'] < 1 && $row['PICK_LINE_STATUS'] !== 'AS') {
                            $itemsWithZeroQty[] = $row[$pickItemsModel->getPKeyAlias()];
                        }
                    }
                }

                if (count($itemsWithBadWh) > 0) {
                    $this->view->errors[] = 'Item(s) ' . implode(', ', $itemsWithBadWh) . ' have wrong Warehouse.';
                }

                if (count($itemsWithZeroQty) > 0) {
                    $this->view->errors[] = 'Item(s) ' . implode(', ', $itemsWithZeroQty) . ' have Picked Qty = 0.';
                }

                $readyToDespTotal = count($readyToDespRows);

                if ($readyToDespRows > 1 && $this->view->shouldCheckEachLine) {
                    $checkLineDetails = $pickItemsModel->selectCheckLineDetails($pickLabelNos);
                    $checkLineDetails = array_merge($checkLineDetails, $this->_loadCheckLineDetails());

                    foreach ($checkLineDetails as $key => &$details) {
                        $details['SELECTED'] = isset($readyToDespRows[$key]);
                    }
                }

                $this->_postProcessNavigation(array('total' => $totalRows));
                $pageSelector            = $this->view->navigation['pageselector'];
                $showBy                  = $this->view->navigation['show_by'];
                $this->view->badStatuses = array();
                
                if ($totalRows > 0) {
                    $this->view->linesDataSet        = $pickItemsModel->getItems($pageSelector * $showBy, $showBy, false);
                    $this->view->selectedLines       = $rowSelector->filterSelectedRowsLegacy($pickLabelNos, $this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
                    $this->view->selectedLinesCount  = count($this->view->selectedLines);
                    $this->view->readyToDespTotal    = $readyToDespTotal;
                    $this->view->readyToDespSelected = count(array_intersect_key($this->view->selectedLines, $readyToDespRows));

                    foreach ($this->view->linesDataSet as &$row) {
                        $pkey = $row[$pickItemsModel->getPKeyAlias()];

                        if (!isset($readyToDespRows[$pkey])) {
                            $row['BAD_ROW'] = true;
                        }
                    }
                }
                $ssccLabels = $pickItemsModel->selectSsccLabels($pickLabelNos);
                $ssccLabels = array_merge($ssccLabels, $this->_awaitingCheckingHelper()->loadSsccLabels());

                if ($this->view->shouldCreateSSCC) {
                    $checkLineDetails = $this->_awaitingCheckingHelper()->fillCheckDetailsFromSscc($checkLineDetails, $ssccLabels);
                }
                $orderCompany = $this->_companyHelper()->find(current($pickOrders->COMPANY_ID));

                $this->view->packSscc = $ssccLabels;
                $this->_awaitingCheckingHelper()->storeSsccLabels($ssccLabels);
                $this->view->checkLineDetails = $checkLineDetails;
                $this->_storeCheckLineDetails($checkLineDetails);
                $this->view->serialNumbers = $this->_awaitingCheckingHelper()->loadSerialNumbers();

                if ($pickOrders->hasEdiOrders()) {
                    $dcNo = $this->_awaitingCheckingEdiHelper()->getDcNoByLocationId($this->_getPickBlockSearchValue());
                    $this->view->orderStatistics    = $this->_awaitingCheckingEdiHelper()->calculateEdiOrderStatistics($pickOrders, $dcNo);
                }
            }
            
            list($this->view->fieldList, $this->view->tabList) = $screenBuilder->buildSysScreenSearchResult(self::$linesModel, true);
        } catch (Exception $e) {
            $log->doneWithErrors($e->getMessage());
            $this->view->errors[] = $e->getMessage();
            return;
        }
        $log->done();
    }

    public function acceptSsccAction() {
        $this->_viewRenderer()->setNoRender();
        $response = $this->_awaitingCheckingHelper()->validateAcceptSsccRequest($this->getRequest());

        if ($response->hasErrors()) {
            echo json_encode($response);
            return;
        }

        $labelData = $this->getRequest()->getParam('labelData');
        $storedSscc = $this->_awaitingCheckingHelper()->loadSsccLabels();
        $ssccList = $this->getRequest()->getParam('sscc', array());
        $checkedSscc = array();
        $checkedProdAmount = 0;
        $hasMoreSku = false;
        $toSplit = $labelData['SSCC'];

        foreach ($ssccList as &$sscc) {
            if (in_array(strtoupper($sscc['PS_SSCC_STATUS']), array('GO', 'AC'))) {
                $checkedQty = isset($sscc['CHECKED_QTY']) ? intval($sscc['CHECKED_QTY']) : 0;

                if ($checkedQty > 0) {
                    $checkedSscc[] = $sscc;
                    $checkedProdAmount += $checkedQty;

                    if (!isset($sscc['completed'])) {
                        $hasMoreSku = true;
                        $toSplit = $sscc['PS_SSCC'];
                    }
                } else {
                    if (isset($sscc['completed'])) {
                        $checkedSscc[] = $sscc;
                        $sscc['checkedQty'] = 0;
                    } else {
                        $hasMoreSku = true;
                        unset($storedSscc[$sscc['PS_SSCC']]);
                    }
                }
            }
        }

        if ($checkedProdAmount < 1) {
            $response->errors[] = 'No product checked.';
            echo json_encode($response);
            return;
        }

        $linesSscc = array();
        $dsgsnResponse = null;

        try {
            $currentDevice = Minder2_Environment::getInstance()->getCurrentDevice();
            $tmpSscc = current($checkedSscc);
            $pickOrder = $this->minder->getPickOrder($tmpSscc['PS_PICK_ORDER']);
            $printer = $this->_awaitingCheckingHelper()->getDespatchPrinterForPickOrder($pickOrder->pickOrder);

            foreach ($checkedSscc as $sscc) {
                $dsgsd = new Transaction_DSGSD();

                $dsgsd->SSCC = $sscc['PS_SSCC'];
                $dsgsd->length = $labelData['L'];
                $dsgsd->width = $labelData['W'];
                $dsgsd->height = $labelData['H'];
                $dsgsd->volume = $labelData['CALCULATED']['TOTAL_VOL'];
                $dsgsd->weight = $labelData['CALCULATED']['TOTAL_WT'];
                $dsgsd->totalOuters = $labelData['QTY'];
                $dsgsd->dimUom = $labelData['UOM']['DT'];
                $dsgsd->volumeUom = $labelData['UOM']['VT'];
                $dsgsd->weightUom = $labelData['UOM']['WT'];
                $dsgsd->packType  = $labelData['TYPE'];
                $dsgsd->labelPrinter = $printer;
                $dsgsd->scannedItems = $sscc['CHECKED_QTY'];

                $dsgsd->setPickOrder($pickOrder);

                $dsgsd->despatchPCLocnId = $currentDevice->getLocation();
                $this->minder->doTransactionResponseV6($dsgsd);
                $storedSscc[$sscc['PS_SSCC']]['accepted'] = true;
            }

            if ($hasMoreSku) {
                $dsgsu = new Transaction_DSGSN();
                $dsgsu->SSCC = $toSplit;
                $dsgsu->labelPrinter = $printer;
                $dsgsu->setPickOrder($pickOrder);
                $dsgsu->despatchPCLocnId = $currentDevice->getLocation();

                $transactionMessage = $this->minder->doTransactionResponseV6($dsgsu);
                $response->addMessages($transactionMessage);
                $dsgsnResponse = $dsgsu->parseResponse($transactionMessage);
            }

            $this->_awaitingCheckingHelper()->storeSsccDimensions($this->getRequest()->getParam('allLabels', array()));
            $response->messages[] = 'Accepted';

            $screenBuilder = new Minder_SysScreen_Builder();
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::$orderModel, true);

            $searchFields  = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $this->_searchHelper()->getSearch($searchFields, static::SEARCH_NAMESPACE, static::SEARCH_ACTION);

            $pickItemsModel  = new Minder_SysScreen_Model_OrderCheckLine();
            $pickItemsModel  = $screenBuilder->buildSysScreenModel(self::$linesModel, $pickItemsModel);
            /**
             * @var Minder_SysScreen_Model_OrderCheckLine $pickItemsModel
             */

            if ($this->_searchHelper()->isLabelSearch($searchFields, array('PICK_BLOCK'))) {
                $searchField = $this->_searchHelper()->getLabelSearch($searchFields, 'PICK_BLOCK');
                $pickItemsModel->addPickBlockLimit($searchField['SEARCH_VALUE']);
            } else {
                $pickItemsModel->removePickBlockLimit();
            }

            $pickItemsModel->addConditions($this->_rowSelector()->getSelectConditions($this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController));

            $total = count($pickItemsModel);
            if ($total > 0) {
                $linesSscc = $pickItemsModel->selectSsccLabels($pickItemsModel->selectPickLabelNos(0, $total));
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        if ($dsgsnResponse) {
            $ssccList = Minder_ArrayUtils::mapKey($ssccList, 'PS_SSCC');
            if (isset($ssccList[$toSplit]['completed'])) {
                $linesSscc[$dsgsnResponse->getSscc()]['completed'] = true;
                $linesSscc[$dsgsnResponse->getSscc()]['checkedQty'] = 0;
            } else {
                $linesSscc[$toSplit]['completed'] = true;
            }
        }

        $this->_awaitingCheckingHelper()->storeSsccLabels($linesSscc);

        $response->packSscc = $linesSscc;
        $response->hasMoreSku = $hasMoreSku;

        $pickOrders = $this->_getSelectedOrders();
        $dcNo = $this->_awaitingCheckingEdiHelper()->getDcNoByLocationId($this->_getPickBlockSearchValue());
        $response->orderStatistics = $this->_awaitingCheckingEdiHelper()->calculateEdiOrderStatistics($pickOrders, $dcNo);

        echo json_encode($response);
    }

    function acceptSsccOneAction() {
        $this->_viewRenderer()->setNoRender();
        $this->_initViewMessagesContainers();

        $orders = $this->_getSelectedOrders();
        $response = new Minder_JSResponse();

        $dimensions = $this->getRequest()->getParam('labelData');
        $status = $this->_filterEdiOneStatus($this->getRequest()->getParam('checkStatus'));
        $this->_storeEdiOneStatus($status);

        try {
            $status = $this->_awaitingCheckingEdiHelper()->acceptSscc($orders, $status, $dimensions, $response);
            $this->_storeEdiOneStatus($status);
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        if (!$response->hasErrors()) {
            $response->addMessages('SSCC #' . $status['outSscc'] . ' accepted.');
            $this->view->success = true;
        }

        $this->_copyMessagesToView($response);
        $this->_forward('get-order-check-lines-data');
    }

    public function rePackAction() {
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();
        $response->ssccMap      = array();
        $response->checkDetails = array();

        $repackData = $this->getRequest()->getParam('rows', array());
        $prodId = $this->getRequest()->getParam('prodId', '');
        try {

            $screenBuilder = new Minder_SysScreen_Builder();
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::$orderModel, true);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $this->_searchHelper()->getSearch($searchFields, static::SEARCH_NAMESPACE, static::SEARCH_ACTION);

            $pickItemsModel = new Minder_SysScreen_Model_OrderCheckLine();
            $pickItemsModel = $screenBuilder->buildSysScreenModel(self::$linesModel, $pickItemsModel);
            /**
             * @var Minder_SysScreen_Model_OrderCheckLine $pickItemsModel
             */

            if ($this->_searchHelper()->isLabelSearch($searchFields, array('PICK_BLOCK'))) {
                $searchField = $this->_searchHelper()->getLabelSearch($searchFields, 'PICK_BLOCK');
                $pickItemsModel->addPickBlockLimit($searchField['SEARCH_VALUE']);
            } else {
                $pickItemsModel->removePickBlockLimit();
            }

            $total = count($pickItemsModel);
            if ($total > 0) {
                $pickLabelNos = $pickItemsModel->selectPickLabelNos(0, $total);
                $linesSscc = $pickItemsModel->selectSsccLabels($pickLabelNos);
                $checkLineDetails = $this->_loadCheckLineDetails();
                $checkLineDetails = $this->_awaitingCheckingHelper()->fillCheckDetailsFromSscc($checkLineDetails, $linesSscc);
                $this->_awaitingCheckingHelper()->rePack($linesSscc, $repackData, $checkLineDetails, $prodId, $response);

                $linesSscc = $pickItemsModel->selectSsccLabels($pickLabelNos);
                $checkLineDetails = $this->_awaitingCheckingHelper()->fillCheckDetailsFromSscc($checkLineDetails, $linesSscc);

                $this->_storeCheckLineDetails($checkLineDetails);
                $this->_awaitingCheckingHelper()->storeSsccLabels($linesSscc);

                $response->ssccMap      = $linesSscc;
                $response->checkDetails = $checkLineDetails;

                $response->addMessages('Accepted.');
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        echo json_encode($response);
    }

    public function getConnoteAction() {
        $log = Minder_Registry::getLogger()->startDetailedLog();
        $log->starting('Stage 3: getting connote form....');

        $this->view->errors   = array();
        $this->view->warnings = array();
        $this->view->volumeRequired = false;
        $this->view->weightRequired = false;

        $this->view->ssccTotals = $this->getRequest()->getParam('ssccPackTotals');
        $ssccLastLabelData = current($this->getRequest()->getParam('ssccAllLabels', array()));
        $this->view->ssccDespatchId = '';

        if (!empty($ssccLastLabelData)) {
            try {
                $packSscc = $this->_awaitingCheckingHelper()->getPackSscc($ssccLastLabelData['SSCC']);

                if (!empty($packSscc)) {
                    $this->view->ssccDespatchId = $packSscc['PS_AWB_CONSIGNMENT_NO'];
                    //todo: run DSASC transaction
                }
            } catch (Exception $e) {
                $this->view->errors[] = $e->getMessage();
            }
        }

        try {
            $dsotUoms = $this->minder->getDsotUoms();
        
            $this->view->connoteWTCode      = $dsotUoms['WT'];
            $this->view->connoteVTCode      = $dsotUoms['VT'];
            $this->view->connoteDTForVTCode = $dsotUoms['DT_FOR_VT'];

            $this->view->palletOwnerList    = minder_array_merge(array('NONE' => 'NONE'), $this->minder->getPalletOwnerList());
            $this->view->palletOwner        = 'NONE';

            $request     = $this->getRequest();
            $rowSelector = $this->_helper->rowSelector;
            
            $selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            
            if ($selectedOrdersCount > 0) {
                $ordersModel = $rowSelector->getModel($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                $ordersModel->addConditions($rowSelector->getSelectConditions($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController));
                
                $selectedOrders     = $ordersModel->selectPickOrder(0, $selectedOrdersCount);
                $tmpConditionString = 'PICK_ORDER.PICK_ORDER IN (' . substr(str_repeat('?, ', $selectedOrdersCount), 0, -2) . ')';
                
                try {
                    $screenBuilder     = new Minder_SysScreen_Builder();
                    $despatchToModel   = $screenBuilder->buildSysScreenModel(self::$despatchToModel);
                    $despatchToModel->addConditions(array($tmpConditionString => $selectedOrders));
                    $this->view->despatchToRows = $despatchToModel->getItems(0, count($despatchToModel));

                    list(
                        $this->view->d_fieldList, 
                        $this->view->d_tabList,
                        $this->view->d_colors,
                        $this->view->d_actions
                    )                           = $screenBuilder->buildSysScreenSearchResult(self::$despatchToModel);
                    $this->view->d_sysScreenName = self::$despatchToModel;
                } catch (Exception $e) {
                    $this->view->errors[] = $e->getMessage();
                }
                
                try {
                    $instructionsModel = $screenBuilder->buildSysScreenModel(self::$instructionsModel);
                    $instructionsModel->addConditions(array($tmpConditionString => $selectedOrders));
                    $this->view->instructionsRows = $instructionsModel->getItems(0, count($instructionsModel));
                
                    list(
                        $this->view->i_fieldList, 
                        $this->view->i_tabList,
                        $this->view->i_colors,
                        $this->view->i_actions
                    )                           = $screenBuilder->buildSysScreenSearchResult(self::$instructionsModel);
                    $this->view->i_sysScreenName = self::$instructionsModel;
                } catch (Exception $e) {
                    $this->view->errors[] = $e->getMessage();
                }
                
                try{
                    $this->view->dimensions = array();
                    list(
                        $this->view->dim_fieldList, 
                        $this->view->dim_tabList,
                        $this->view->dim_colors,
                        $this->view->dim_actions
                    )                           = $screenBuilder->buildSysScreenSearchResult(self::$dimensionsModel, true);
                    $this->view->dim_sysScreenName = self::$dimensionsModel;

                    $dimensionsModel = new Minder_SysScreen_Model_OrderPackDims();
                    $dimensionsModel->setPickOrderLimit($selectedOrders);
                    $sqlDataSource = new Minder_SysScreen_DataSource_Sql();

                    foreach ($this->view->dim_fieldList as &$fieldDesc) {
                        if (!empty($fieldDesc['SSV_DROPDOWN_DEFAULT'])) {
                            $sqlDataSource->sql = $fieldDesc['SSV_DROPDOWN_DEFAULT'];
                            $fieldDesc['DEFAULT_VALUE'] = $sqlDataSource->fetchOne($dimensionsModel);
                        }
                    }
                
                } catch (Exception $e) {
                    $this->view->errors[] = $e->getMessage();
                }
                
                $this->view->carriers                   = $this->minder->getCarriersList();
                $this->view->carriers                   = is_array($this->view->carriers) ? $this->view->carriers : array();

                $selectedOrderNo                        = current($selectedOrders);
                $selectedOrder                          = $this->minder->getPickOrder($selectedOrderNo);
                $orderCompany                           = $this->_companyHelper()->find($selectedOrder->companyId);
                $shipVia                                = $selectedOrder->shipVia;
                
                if (empty($shipVia) || !isset($this->view->carriers[$shipVia])) {
                    $this->view->errors[] = empty($shipVia) ? 'PICK_ORDER #' . $selectedOrderNo . ' has empty SHIP_VIA. Will use order company DEFAULT_CARRIER_ID: "' . $orderCompany->DEFAULT_CARRIER_ID . '".' : 'PICK_ORDER #' . $selectedOrderNo . ' SHIP_VIA field contains unknown CARRIER_ID: "' . $shipVia . '". Will use order company DEFAULT_CARRIER_ID: "' . $orderCompany->DEFAULT_CARRIER_ID . '" instead.';
                    $shipVia = $orderCompany->DEFAULT_CARRIER_ID;
                }

                if (empty($shipVia) || !isset($this->view->carriers[$shipVia])) {
                    $this->view->errors[] = empty($shipVia) ? 'Company #' . $selectedOrder->companyId . ' has empty DEFAULT_CARRIER_ID. Will use CONTROL.DEFAULT_CARRIER_ID: "' . $this->minder->defaultControlValues['DEFAULT_CARRIER_ID'] . '".' : 'Company #' . $selectedOrder->companyId . ' DEFAULT_CARRIER_ID field contains unknown CARRIER_ID: "' . $shipVia . '". Will use CONTROL.DEFAULT_CARRIER_ID: "' . $this->minder->defaultControlValues['DEFAULT_CARRIER_ID'] . '" instead.';
                    $shipVia = $this->minder->defaultControlValues['DEFAULT_CARRIER_ID'];
                }

                if (empty($shipVia) || !isset($this->view->carriers[$shipVia])) {
                    $firstCarrier = current($this->view->carriers);
                    $this->view->errors[] = empty($shipVia) ? 'DEFAULT_CARRIER_ID is empty. Will use first CARRIER_ID: "' . $firstCarrier . '" from CARRIER table.' : 'DEFAULT_CARRIER_ID contains unknown CARRIER_ID: "' . $shipVia . '". Will use first CARRIER_ID: "' . $firstCarrier . '" from CARRIER table instead.';
                    $shipVia = $firstCarrier;
                }
                
                if (empty($shipVia)) {
                    $this->view->errors[] = 'No Carriers defined in CARRIER table. Please check system setup.';
                }

                $this->view->firstSelectedOrder         = $selectedOrderNo;
                $this->view->carriers                   = array_merge(array('' => ''), $this->view->carriers);
                $this->view->carriersList               = $this->minder->getCarriers();
                $this->view->carriersList               = array_merge(array(buildEmptyRow($this->view->carriersList)), $this->view->carriersList);
                $this->view->shipVia                    = $shipVia;
                $this->view->carrierServiceTypes        = $this->minder->getCarrierServiceTypes();
                $this->view->carrierDefaultServiceTypes = $this->minder->getCarrirersDefaultServiceTypesList();
                $this->view->deviceCarriersList         = $this->minder->getDeviceCarrierList();
                $this->view->volumeRequired             = $orderCompany->isDespatchVolumeRequired();
                $this->view->weightRequired             = $orderCompany->isDespatchWeightRequired();
            }

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
        $log->done();
    }

    public function acceptEdiOneAction() {
        $this->_viewRenderer()->setNoRender();
        $orders = $this->_getSelectedOrders();
        $response = new Minder_JSResponse();

        try {
            $pickBlock = $this->_getPickBlockSearchValue();
            $response = $this->_awaitingCheckingEdiHelper()->acceptConnote($orders, $pickBlock, $response);
        } catch (Exception $e) {
            $response->addErrors($e->getMessage());
        }

        echo json_encode($response);
    }

    public function acceptAction() {
        $log = Minder_Registry::getLogger()->startDetailedLog();
        $log->starting('Stage 4: accepting despatch....');

        $this->_helper->viewRenderer->setNoRender(true);
        $jsonData = array('success' => false, 'messages' => array(), 'errors' => array());

        try {
            $connoteProccess = new Minder_ConnoteProccess();
            $connoteProccess->skipLabelPrinting = $this->getRequest()->getParam('skipLabelPrinting', 'false') == 'true';

            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector         = $this->_helper->getHelper('RowSelector');
            
            $selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            /**
             * @var Minder_SysScreen_Model_AwaitingCheckingOrders $ordersModel
             */
            $ordersModel         = $rowSelector->getModel($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            
            if ($selectedOrdersCount < 1) {
                throw new Minder_Exception('No Orders selected for despatch.');
            }
            
            $selectedLinesCount  = $rowSelector->getSelectedCount($this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
            /**
             * @var Minder_SysScreen_Model_OrderCheckLine $linesModel
             */
            $linesModel          = $rowSelector->getModel($this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
            $linesModel->addConditions($rowSelector->getSelectConditions($this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController));

            if ($selectedLinesCount < 1) {
                throw new Minder_Exception('No Lines selected for despatch.');
            }
            
            $tmpBadSatuses = array();
            $selectedPickLabelNos = $linesModel->selectPickLabelNos(0, $selectedLinesCount);
            foreach ($linesModel->getDespatchStatus($selectedPickLabelNos) as $statusRow) {
                if (!$linesModel->isReadyForDespatch($statusRow)) {
                    $tmpBadSatuses[$statusRow['STATUS']] = $statusRow['STATUS'];
                }
            }
            if (count($tmpBadSatuses) > 0)
                throw new  Minder_Exception("Cannot Despatch Lines with Status = ('" . implode("', '", $tmpBadSatuses) . "')");

            $ordersModel->addConditions($rowSelector->getSelectConditions($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController));

            $pickOrders = $ordersModel->getPickOrders(0, $selectedOrdersCount);
            $connoteProccess->orders = array_unique($pickOrders->PICK_ORDER);

            if ($this->_awaitingCheckingHelper()->shouldCreateSscc($pickOrders)) {
                $connoteProccess->pickBlock = $this->_getPickBlockSearchValue($connoteProccess);
            }

            if ($this->_awaitingCheckingHelper()->shouldRecordSerialNumber($pickOrders)) {
                $serialNumbers = $this->_awaitingCheckingHelper()->loadSerialNumbers();
                $result = $this->_awaitingCheckingHelper()->commitSerialNumbers($serialNumbers);

                if ($result->hasErrors()) {
                    throw new Exception('Cannot record Serial Numbers: ' . implode('\n', $result->errors));
                }
            }
            
            $connoteProccess->lines           = array_values($selectedPickLabelNos);
            $connoteProccess->palletQty       = 0;
            $connoteProccess->cartonQty       = 0;
            $connoteProccess->satchelQty      = 0;
            
            $connoteProccess->palletOwner     = $this->_request->getParam('palletOwner'); 
            $connoteProccess->carrierId       = $this->_request->getParam('carrier');
            $connoteProccess->carrierServiceRecordId = $this->_request->getParam('carrierService');
            $connoteProccess->printerId       = $this->minder->limitPrinter;
            $connoteProccess->accountNo       = $this->_request->getParam('accountNo');
            $connoteProccess->connoteNo       = $this->_request->getParam('consignment');
            
            $connoteProccess->payerFlag = $this->_request->getParam('payer');
            if(!empty($connoteProccess->payerFlag)) 
                $connoteProccess->payerFlag = substr($connoteProccess->payerFlag, 0, 1);
                
            $packDimensions = $this->getRequest()->getParam('dimentions', array());
            array_walk_recursive($packDimensions, 'trim');
            
            foreach ($packDimensions as &$dimension) {
                switch ($dimension['TYPE']) {
                    case 'C':
                        $connoteProccess->cartonQty  += $dimension['QTY'];
                        break;
                    case 'P':
                        $connoteProccess->palletQty  += $dimension['QTY'];
                        $dimension['VOL'] = 0;
                        break;
                    case 'S':
                        $connoteProccess->satchelQty += $dimension['QTY'];
                        break;
                }
            }
            
            $connoteProccess->qtyAddressLabel = $connoteProccess->cartonQty + $connoteProccess->palletQty + $connoteProccess->satchelQty;
            $connoteProccess->packDims        = $packDimensions;
            
            $connoteProccess->run();
            
            $jsonData['success'] = true;
        } catch (Exception $e) {
            $jsonData['errors'][] = $e->getMessage();

            $log->error('Error during Accept Connote proccess: "' . $e->getMessage() . '". in ' . __FILE__ . ':' .  __LINE__);
        }
        
        $jsonData['messages'] = $connoteProccess->messages;
        $jsonData['warnings'] = $connoteProccess->warnings;
        echo json_encode($jsonData);

        $log->done();
    }

    public function getRetailUnitAction() {
        $this->_viewRenderer()->setNoRender();
        $this->_initViewMessagesContainers();

        $code = $this->getRequest()->getParam('code');
        $this->view->retailUnit = null;

        if (empty($code)) {
            $this->view->errors[] = 'No code given.';
        } else {
            try {
                $this->view->retailUnit = $this->_awaitingCheckingHelper()->getRetailUnit($code);
            } catch (Exception $e) {
                $this->view->errors[] = $e->getMessage();
            }
        }

        echo $this->_datasetToJson($this->view);
    }

    public function despatchPackAction() {
        $response = $this->_carrierPack()->doDespatch(
            $this->getRequest()->getParam('despatchLabelNo'),
            $this->getRequest()->getParam('carrierId')
        );

        $response = $this->_carrierPack()->fillCarrierPackStatistics($this->getRequest()->getParam('scannedCarriers', array()), $response);

        try {
            $response->sysScreens = $this->_buildDatatset(array(static::EXIT_CARRIER_MODEL => static::EXIT_CARRIER_NAMESPACE));
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->_viewRenderer()->setNoRender();
        echo json_encode($response);
    }

    public function storeCheckLineDetailsAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_storeCheckLineDetails($this->_getParam('checkLineDetails', array()));
        $this->_awaitingCheckingHelper()->storeSsccLabels($this->_getParam('packSscc', array()));
        $this->_awaitingCheckingHelper()->storeSerialNumbers($this->_getParam('serialNumbers', array()));
        echo json_encode(array('status' => 'ok'));
    }

    public function commitSerialNumbersAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_storeCheckLineDetails($this->_getParam('checkLineDetails', array()));
        $result = $this->_awaitingCheckingHelper()->commitSerialNumbers($this->_getParam('serialNumbers', array()));
        $this->_awaitingCheckingHelper()->storeSerialNumbers($result->serialNumbers);
        echo json_encode($result);
    }

    public function searchOrderByProductAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        echo json_encode(array(
            'orders' => array_values($this->_findOrders($this->getRequest()->getParam('scannedProducts', array()))),
        ));
    }

    public function changeCarrierAction() {
        $response = new Minder_JSResponse();
        $this->_viewRenderer()->setNoRender();

        $carrierId = $this->getRequest()->getParam('carrier_id');
        $carrierService = $this->getRequest()->getParam('service_id');

        $selectedRows = $this->_rowSelector()->getSelectedCount(static::$linesModel, $this->view->linesSelectionAction, $this->view->linesSelectionController);

        if ($selectedRows < 1) {
            $pickLabelNos = array();
        } else {
            /**
             * @var Minder_SysScreen_Model_OrderCheckLine $linesModel
             */
            $linesModel = $this->_rowSelector()->getModel(static::$linesModel, $this->view->linesSelectionAction, $this->view->linesSelectionController);
            $linesModel->addConditions($this->_rowSelector()->getSelectConditions(static::$linesModel, $this->view->linesSelectionAction, $this->view->linesSelectionController));
            $pickLabelNos = array_values($linesModel->selectPickLabelNos(0, $selectedRows));
        }

        $response = $this->_awaitingCheckingHelper()->_changeCarrierService($pickLabelNos, $carrierId, $carrierService, $response);

        echo json_encode($response);
    }

    public function printNextSsccLabelAction() {
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();
        $response->orderStatistics = new Minder_Controller_Action_Helper_AwaitingCheckingEdi_OrderStatistics();

        $pickOrders = $this->_getSelectedOrders();

        if (count($pickOrders) > 0) {
            try {
                $dcNo = $this->_awaitingCheckingEdiHelper()->getDcNoByLocationId($this->_getPickBlockSearchValue());
                $orderId = current($pickOrders->PICK_ORDER);

                $nextNotPrintedSscc = $this->_awaitingCheckingEdiHelper()->getNextNotPrintedSscc($pickOrders, $dcNo);

                if (!empty($nextNotPrintedSscc)) {
                    $transaction = new Transaction_DSPSR();
                    $transaction->ssccId = $nextNotPrintedSscc;
                    $transaction->printerId = $this->_awaitingCheckingHelper()->getDespatchPrinterForPickOrder($orderId);
                    $transaction->pickOrder = $orderId;

                    $message = $this->minder->doTransactionResponseV6($transaction);
                    $response->addMessages($message);
                } else {
                    $response->addErrors('No not printed labels found.');
                }
                $response->orderStatistics = $this->_awaitingCheckingEdiHelper()->calculateEdiOrderStatistics($pickOrders, $dcNo);
            } catch (Exception $e) {
                $response->addErrors($e->getMessage());
            }
        }

        echo json_encode($response);
    }

    public function printAllSsccLabelsAction() {
        $this->_viewRenderer()->setNoRender();
        $response = new Minder_JSResponse();
        $response->orderStatistics = new Minder_Controller_Action_Helper_AwaitingCheckingEdi_OrderStatistics();

        $pickOrders = $this->_getSelectedOrders();

        if (count($pickOrders) > 0) {
            try {
                $dcNo = $this->_awaitingCheckingEdiHelper()->getDcNoByLocationId($this->_getPickBlockSearchValue());
                $packSsccList = $this->_awaitingCheckingEdiHelper()->getSsccPacks($pickOrders, $dcNo);

                $orderId = current($pickOrders->PICK_ORDER);
                $companyId = current($pickOrders->COMPANY_ID);
                $printerId = $this->_awaitingCheckingHelper()->getDespatchPrinterForPickOrder($orderId);

                $this->_awaitingCheckingEdiHelper()->printAllLabels($packSsccList, $orderId, $companyId, $printerId);
                $response->orderStatistics = $this->_awaitingCheckingEdiHelper()->calculateEdiOrderStatistics($pickOrders, $dcNo);
                $response->addMessages('All labels printed');
            } catch (Exception $e) {
                $response->addErrors($e->getMessage());
            }
        }

        echo json_encode($response);
    }

    public function printSsccLabelAction() {
        $this->_initViewMessagesContainers();
        $this->view->success = false;
        $totalPrinted = 0;
        try {
            $selectedRows = $this->_rowSelector()->getSelectedCount(static::$linesModel, $this->view->linesSelectionAction, $this->view->linesSelectionController);

            if ($selectedRows < 1) {
                $this->view->errors[] = 'No rows selected.';
            } else {
                /**
                 * @var Minder_SysScreen_Model_OrderCheckLine $linesModel
                 */
                $linesModel = $this->_rowSelector()->getModel(static::$linesModel, $this->view->linesSelectionAction, $this->view->linesSelectionController);
                $linesModel->addConditions($this->_rowSelector()->getSelectConditions(static::$linesModel, $this->view->linesSelectionAction, $this->view->linesSelectionController));
                $pickLabelNos = $linesModel->selectPickLabelNos(0, count($linesModel));
                $pickOrder = $linesModel->selectOrderFromSscc($pickLabelNos);
                $printer = $this->_awaitingCheckingHelper()->getDespatchPrinterForPickOrder($pickOrder);
                $totalPrinted = $linesModel->printSsccPackLabels($pickLabelNos, $printer);
                $this->view->success = true;

            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        if ($totalPrinted > 0) {
            $this->view->messages[] = 'Label(s) where printed.';
        } else {
            $this->view->errors[] = 'No label was printed.';
        }

        $this->_viewRenderer()->setNoRender();
        echo $this->_datasetToJson()->render();
    }

    public function storeEdiOneStatusAction() {
        $this->_storeEdiOneStatus($this->_filterEdiOneStatus($this->getRequest()->getParam('status', array())));
        $this->_viewRenderer()->setNoRender();
        echo json_encode(array('status' => 'ok'));
    }

    protected function _findOrders($products) {
        return $this->minder->getOrdersByProduct($products);
    }

    protected function _cleanCheckLineDetails() {
        $this->_storeCheckLineDetails(array());
    }

    protected function _storeCheckLineDetails($data) {
        $this->session->{static::CHECK_LINE_DETAILS} = $data;
    }

    protected function _loadCheckLineDetails() {
        return isset($this->session->{static::CHECK_LINE_DETAILS}) ?  $this->session->{static::CHECK_LINE_DETAILS} : array();
    }

    /**
     * @return Minder_Controller_Action_Helper_AwaitingChecking
     */
    protected function _awaitingCheckingHelper() {
        return $this->getHelper('AwaitingChecking');
    }

    /**
     * @return Minder_Controller_Action_Helper_PackDimensions
     */
    protected function _packDimensions() {
        return $this->getHelper('PackDimensions');
    }

    /**
     * @param $outSscc
     * @return mixed
     */
    protected function _storeOutSscc($outSscc)
    {
        return $this->session->{static::OUT_SSCC_KEY} = $outSscc;
    }


    /**
     * @return Minder_Controller_Action_Helper_AwaitingCheckingEdi
     */
    protected function _awaitingCheckingEdiHelper() {
        return $this->getHelper('AwaitingCheckingEdi');
    }

    /**
     * @return Minder_PickOrder_Collection
     * @throws Minder_Controller_Action_Helper_RowSelector_Exception
     * @throws Minder_SysScreen_Model_Exception
     */
    protected function _getSelectedOrders()
    {
        $selectedOrdersCount = $this->_rowSelector()->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);

        $orders = new Minder_PickOrder_Collection();
        if ($selectedOrdersCount > 0) {
            /**
             * @var Minder_SysScreen_Model_AwaitingCheckingOrders $ordersModel
             */
            $ordersModel = $this->_rowSelector()->getModel($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $ordersModel->addConditions($this->_rowSelector()->getSelectConditions($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController));
            $orders = $ordersModel->getPickOrders(0, $selectedOrdersCount);
            return $orders;
        }
        return $orders;
    }

    /**
     * @param $data
     */
    protected function _storeEdiOneStatus($data)
    {
        $this->session->{static::EDI_ONE_STATUS} = $data;
    }

    protected function _getStoredOrDefaultEdiOneStatus()
    {
        return isset($this->session->{static::EDI_ONE_STATUS}) ? $this->session->{static::EDI_ONE_STATUS} : array(
            "outSscc"               => '',
            "checkingStarted"       => false,
            "completed"             => false,
            "dimensionsStarted"     => false,
            "packSsccCheckStatus"   => array()
        );
    }

    protected function _forgetEdiOneStatus() {
        if (isset($this->session->{static::EDI_ONE_STATUS})) {
            unset($this->session->{static::EDI_ONE_STATUS});
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function _filterEdiOneStatus($data)
    {
        $data['checkingStarted'] = isset($data['checkingStarted']) ? $data['checkingStarted'] == 'true' : false;
        $data['dimensionsStarted'] = isset($data['dimensionsStarted']) ? $data['dimensionsStarted'] == 'true' : false;
        $data['completed'] = isset($data['completed']) ? $data['completed'] == 'true' : false;
        return $data;
    }

    /**
     * @throws Minder_Controller_Action_Helper_SearchKeeper_Exception
     * @throws Minder_SysScreen_Builder_Exception
     * @return string
     */
    protected function _getPickBlockSearchValue()
    {
        $screenBuilder = new Minder_SysScreen_Builder();
        list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::$orderModel, true);

        $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
        $searchFields = $this->_searchHelper()->getSearch($searchFields, static::SEARCH_NAMESPACE, static::SEARCH_ACTION);

        if ($this->_searchHelper()->isLabelSearch($searchFields, array('PICK_BLOCK'))) {
            $searchField = $this->_searchHelper()->getLabelSearch($searchFields, 'PICK_BLOCK');
            return $searchField['SEARCH_VALUE'];
        }

        return '';
    }
}
