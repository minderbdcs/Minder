<?php
  
class DespatchAwaitingCheckingController extends Minder_Controller_Action
{
    
    public static $orderModel = 'ORDERCHECK';
    public static $linesModel = 'ORDERCHECKLINES';
    
    protected $mAllowedParams = array(
                                    'LOCATION'   => 'SEARCH-PICK_ORDER-DESPATCH_LOCATION', 
                                    'SALESORDER' => 'SEARCH-PICK_ORDER-PICK_ORDER'
                                );
                                
    protected $mSearchFields  = array(
                                    array('RECORD_ID' => 'DACC_SE_1', 'SSV_ALIAS' => 'DESPATCH_LOCATION', 'SSV_TABLE' => 'PICK_ORDER', 'SSV_NAME' => 'DESPATCH_LOCATION', 'SSV_INPUT_METHOD' => 'IN'), 
                                    array('RECORD_ID' => 'DACC_SE_2', 'SSV_ALIAS' => 'PICK_ORDER',        'SSV_TABLE' => 'PICK_ORDER', 'SSV_NAME' => 'PICK_ORDER',        'SSV_INPUT_METHOD' => 'IN')
                                );
                                
    protected $mStaticConditions = array("(PICK_ORDER.PICK_ORDER_TYPE = ? OR PICK_ORDER.PICK_ORDER_TYPE = ?)" => array('SO', 'TO'));
    
    public function init() {
        parent::init();
        $this->view->pageTitle = 'AWAITING CHECKING';
        $this->_helper->addPrefix('Minder_Controller_Action_Helper');
        
        $this->view->orderSelectionNamespace  = 'order_selection';
        $this->view->orderSelectionAction     = 'select-row';
        $this->view->orderSelectionController = 'despatch-awaiting-checking';
        $this->view->orderSelectionModule     = 'default';

        $this->view->linesSelectionNamespace  = 'lines_selection';
        $this->view->linesSelectionAction     = 'select-row';
        $this->view->linesSelectionController = 'despatch-awaiting-checking';
        $this->view->linesSelectionModule     = 'default';
        
        $this->view->orderSysScreenName       = self::$orderModel;
        $this->view->linesSysScreenName       = self::$linesModel;
    }
    
    public function indexAction() {
        $this->view->errors   = array();
        $this->view->warnings = array();
        $this->_preProcessNavigation();
        
        $request      = $this->getRequest();
        $postAction   = $request->getPost('action', 'none');
        $searchKeeper = $this->_helper->searchKeeper;
        $searchFields = $this->mSearchFields;
        $rowSelector  = $this->_helper->rowSelector;
        
        $selectionState = 'init';

        switch (strtoupper($postAction)) {
            case 'SEARCH' :
                $paramName                                                                     = strtoupper($request->getParam('param_name', null));
                $filteredValue                                                                 = $request->getParam('param_filtered_value', null);
        
                if (!array_key_exists($paramName, $this->mAllowedParams)) {
                    $this->view->errors[] = "Error. Bad search param: '$paramName'. Allowed params: ('" . implode("', '", array_keys($this->mAllowedParams)) . "')";
                    return;
                }
        
                //save search for later use
                $request->setParam($this->mAllowedParams[$paramName], $filteredValue);
                $searchFields = $searchKeeper->makeSearch($searchFields, 'ORDERCHECK_SEARCH', 'orders');
                $this->_helper->viewRenderer->setScriptAction('orders');
                $selectionState = 'true';
                break;
            default:
                $searchFields = $searchKeeper->getSearch($searchFields, 'ORDERCHECK_SEARCH', 'orders');
        }
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        

        try {
            $screenBuilder    = new Minder_SysScreen_Builder();
            $ordercheckModel  = new Minder_SysScreen_Model_AwaitingCheckingOrders();
            $ordercheckModel  = $screenBuilder->buildSysScreenModel(self::$orderModel, $ordercheckModel);
            
            $searchConditions = $ordercheckModel->makeConditionsFromSearch($searchFields);
            $ordercheckModel->setConditions($searchConditions);
            
            if (strtoupper($postAction) == 'SEARCH') {
                //move PICK_ITEMS to device location
                list($whId, $locnId) = $this->minder->getDeviceWhAndLocation();
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $whId, $locnId));
                if (!$this->minder->isDespatchLocation($whId, $locnId)) {
                    $this->view->warnings[] = 'Current device is not defined as despatch location.';
                } else {
                    $ordercheckModel->movePickItemsToDespatchLocation($whId . $locnId, $searchFields);
                }
            }
            
            $rowSelector->setRowSelection('select_complete', $selectionState, null, null, $ordercheckModel, true, $this->view->orderSelectionNamespace, 'select-row');
            
            $totalRows = count($ordercheckModel);
            
            $this->_postProcessNavigation(array('total' => $totalRows));
            $pageSelector        = $this->view->navigation['pageselector'];
            $showBy              = $this->view->navigation['show_by'];
            $this->view->dataSet = array();
            $this->view->selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, 'select-row');
            $this->view->selectedOrders      = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->orderSelectionNamespace, 'select-row');
            if ($totalRows > 0)
                $this->view->dataSet = $ordercheckModel->getItems($pageSelector * $showBy, $showBy, false);
                
            list($this->view->fieldList, $this->view->tabList) = $screenBuilder->buildSysScreenSearchResult(self::$orderModel);
            $this->view->selectMode          = $rowSelector->getSelectionMode('', $this->view->orderSelectionNamespace, 'select-row');
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            return;
        }
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

        $request            = $this->getRequest();
        $selectionNamespace = $request->getParam('selection_namespace', 'default');
        $rowId              = $request->getParam('row_id', 'none');
        $state              = $request->getParam('state',  'none');
        
        $selectionMode      = $request->getParam('selection_mode');

        $rowSelector  = $this->_helper->rowSelector;
        $count = 0;

        try {
            $screenBuilder    = new Minder_SysScreen_Builder();

            switch ($selectionNamespace) {
                case $this->view->orderSelectionNamespace:
                    $rowsModel    = new Minder_SysScreen_Model_AwaitingCheckingOrders();
                    $rowsModel    = $screenBuilder->buildSysScreenModel(self::$orderModel, $rowsModel);
                    $pageselector = $request->getParam('pageselector', $this->session->navigation[$this->_controller]['index']['pageselector']);
                    $showBy       = $request->getParam('show_by', $this->session->navigation[$this->_controller]['index']['show_by']);
                    break;
                case $this->view->linesSelectionNamespace:
                    $rowsModel    = $screenBuilder->buildSysScreenModel(self::$linesModel);
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
                $badStatusArr = array();
                foreach ($selectedRows as $tmpRrowId => $row) {
                    if ($row['PICK_LINE_STATUS'] != 'DS') {
                        $badStatusArr[$row['PICK_LINE_STATUS']] = $row['PICK_LINE_STATUS'];
                    }
                }
                if (count($badStatusArr) > 0) {
                    $response->warnings[] = "Cannot Despatch Lines with Status = ('" . implode("', '", $badStatusArr) . "')";
                }
                
                $rowsModel = $rowSelector->getModel($selectionNamespace);
                $rowsModel->addConditions(array('PICK_ITEM.PICK_LINE_STATUS = ?' => array('DS')));
                $readyForDespTotal              = count($rowsModel);
                $readyForDespRows               = $rowsModel->getItems(0, $readyForDespTotal, true);
                $response->readyForDespSelected = count(array_intersect_key($readyForDespRows, $selectedRows));
            }

        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function printAction() {
        $request            = $this->getRequest();
        $selectionNamespace = $request->getParam('selection_namespace', 'default');
        
        $rowSelector  = $this->_helper->rowSelector;
        $this->view->headers = array();
        $this->view->data    = array();
        
        $screenBuilder = new Minder_SysScreen_Builder();
        
//        switch ($selectionNamespace) {
//                case $this->view->orderSelectionNamespace:
//                    $rowsModel    = new Minder_SysScreen_Model_AwaitingCheckingOrders();
//                    $rowsModel    = $screenBuilder->buildSysScreenModel('ORDERCHECK', $rowsModel);
//                    break;
//                case $this->view->linesSelectionNamespace:
//                    $rowsModel    = $screenBuilder->buildSysScreenModel('PICKITEM');
//                    break;
//        }
//        $rowSelector->setModel($rowsModel);
        $totalRows          = $rowSelector->getTotalCount($selectionNamespace, 'select-row');
        $this->view->data   = $rowSelector->getSelected(0, $totalRows, false, $selectionNamespace, 'select-row');
        $rowsModel          = $rowSelector->getModel($selectionNamespace, 'select-row');
        
        if (reset($this->view->data)) {
            $this->view->headers = array_keys(current($this->view->data));
            $this->view->headers = array_combine($this->view->headers, $this->view->headers);
        }

        if (isset($this->view->headers[$rowsModel->getPkeyAlias()])) {
            //remove synthetic primary key collumn from report
            unset($this->view->headers[$rowsModel->getPkeyAlias()]);
        }
            
        switch (strtoupper($this->getRequest()->getPost('report_format'))) {
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('/reports/report-csv');
                return;
                break;

            case 'REPORT: XML':
                $response = $this->getResponse();
                $response->setHeader('Content-type', 'application/octet-stream');
                $response->setHeader('Content-type', 'application/force-download');
                $response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
                $this->render('/reports/report-xml');
                return;

            case 'REPORT: XLS':
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('/reports/report-xls');
                return;

            case 'REPORT: TXT':
                $this->getResponse()->setHeader('Content-Type', 'text/plain')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.txt"');
                $this->render('/reports/report-txt'); 
                return;
                break;

            case 'REPORT: PDF':
                if ($this->_getParam('step2')) {
                    $this->_exportToPdf();
                } else {
                    $this->view->fonts = array(
                        'courier'      => 'Courier',
                        'courierB'     => 'Courier-Bold',
                        'courierI'     => 'Courier-Oblique',
                        'courierBI'    => 'Courier-BoldOblique',
                        'helvetica'    => 'Helvetica',
                        'helveticaB'   => 'Helvetica-Bold',
                        'helveticaI'   => 'Helvetica-Oblique',
                        'helveticaBI'  => 'Helvetica-BoldOblique',
                        'times'        => 'Times-Roman',
                        'timesB'       => 'Times-Bold',
                        'timesI'       => 'Times-Italic',
                        'timesBI'      => 'Times-BoldItalic',
                    );

                    $this->view->orientations = array(
                        'p' => 'Portrait',
                        'l' => 'Landscape',
                    );
                    $this->view->orientation = 'p';

                    $this->view->formats = array(
                        'a3' => 'A3',
                        'a4' => 'A4',
                        'a5' => 'A5',
                        'letter' => 'Letter',
                        'legal' => 'Legal',
                    );

                    $this->view->size = 11;
                    $this->view->sizes = array(
                        8  => 8,
                        9  => 9,
                        10 => 10,
                        11 => 11,
                        12 => 12,
                        14 => 14,
                        16 => 16,
                        18 => 18,
                        20 => 20,
                    );
                    $this->render('/reports/report-pdf');
                }
                return;

            default:
                break;
        }
    }
    
    public function getLinesAction() {
        $this->view->errors = array();
        $this->_preProcessNavigation();
        
        $request     = $this->getRequest();
        $rowSelector = $this->_helper->rowSelector;

        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $this->view->linesDataSet = array();
        $this->view->selectedLinesCount = 0;
        $this->view->selectedLines      = array();
        $this->view->selectMode         = $rowSelector->getSelectionMode('', $this->view->linesSelectionNamespace, 'select-row');

        try {
            //get selected orders, as we should get only thouse lines, which belongs to selected orders
//            $ordercheckModel  = new Minder_SysScreen_Model_AwaitingCheckingOrders();
//          $ordercheckModel  = $screenBuilder->buildSysScreenModel('ORDERCHECK', $ordercheckModel);
            
            $totalOrders    = $rowSelector->getTotalCount($this->view->orderSelectionNamespace, 'select-row');
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, true, $this->view->orderSelectionNamespace, 'select-row');
            if (count($selectedOrders) > 0) {
                $screenBuilder   = new Minder_SysScreen_Builder();
                $pickItemsModel  = new Minder_SysScreen_Model_OrderCheckLine();
                $pickItemsModel  = $screenBuilder->buildSysScreenModel(self::$linesModel, $pickItemsModel);
                $tmpWrapMethod   = create_function('$el','return "?";');
                $tmpGetPickOrder = create_function('$el', 'return $el["PICK_ORDER"];');
                
                $pickItemsModel->setConditions(array('PICK_ITEM.PICK_ORDER IN (' . implode(', ', array_map($tmpWrapMethod, $selectedOrders)) . ')' => array_map($tmpGetPickOrder, $selectedOrders)));
                
                $rowSelector->setRowSelection('select_complete', 'false', null, null, $pickItemsModel, true, $this->view->linesSelectionNamespace, 'select-row');

                $pickItemsModel->addConditions(array('PICK_ITEM.PICK_LINE_STATUS = ? ' => array('DS')));
                $readyToDespTotal = count($pickItemsModel);
                $readyToDespRows  = $pickItemsModel->getItems(0, $readyToDespTotal, true);
                $pickItemsModel->removeConditions(array('PICK_ITEM.PICK_LINE_STATUS = ? ' => array('DS')));

                $totalRows = count($pickItemsModel);
                $this->_postProcessNavigation(array('total' => $totalRows));
                $pageSelector        = $this->view->navigation['pageselector'];
                $showBy              = $this->view->navigation['show_by'];
                if ($totalRows > 0) {
                    $this->view->selectedLinesCount  = $rowSelector->getSelectedCount($this->view->linesSelectionNamespace, 'select-row');
                    $this->view->selectedLines       = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->linesSelectionNamespace, 'select-row');
                    $this->view->readyToDespTotal    = $readyToDespTotal;
                    $this->view->readyToDespSelected = count(array_intersect_key($this->view->selectedLines, $readyToDespRows));
                    $this->view->linesDataSet        = $pickItemsModel->getItems($pageSelector * $showBy, $showBy, false);
                }
            }
            
            list($this->view->fieldList, $this->view->tabList) = $screenBuilder->buildSysScreenSearchResult(self::$linesModel);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            return;
        }
    }
}
