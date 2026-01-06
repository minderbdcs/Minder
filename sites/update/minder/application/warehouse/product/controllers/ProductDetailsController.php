<?php
class Warehouse_ProductDetailsController extends Minder_Controller_Action
{
    public static $productScreen = 'PRODUCTDETAILS';
    
    public static $productRowSelAction     = 'select-rows';
    public static $productRowSelController = 'product-details';
    public static $productRowSelModule     = 'warehouse';
    
    
    public function init() {
        parent::init();
        
        $this->view->productsSelectionNamespace = self::$productScreen;
        
        $this->view->reportModule     = 'warehouse';
        $this->view->reportController = 'product-details';
        $this->view->reportAction     = 'report';
        
        $this->view->pageTitle        = 'Product Details';
        
        $this->view->productsSysScreenName      = self::$productScreen;
        
    }
    
    public function indexAction() {
        try {
            $searchKeeper = $this->_helper->searchKeeper;
            $request = $this->getRequest();
            
            $screenBuilder      = new Minder_SysScreen_Builder();
            list($searchFields, $searchActions) = $screenBuilder->buildSysScreenSearchFields(self::$productScreen);
            
            $action = $request->getParam('SEARCH_FORM_ACTION', 'none');
            
            switch (strtolower($action)) {
                case 'search':
                    $searchFields = $searchKeeper->makeSearch($searchFields, self::$productScreen);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields, self::$productScreen);
            }
	    $session = new Zend_Session_Namespace();
            $tz_from=$session->BrowserTimeZone;


$array_new=$searchFields;

foreach ($array_new as $key => $value) { 
            foreach($value as $key1=>$val1){
             
                if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') { unset($array_new[$key]); }
            
                
            }    

}

            
            
            foreach($array_new as $key=>$val){

                                foreach($val as $key1=>$val1){

                                                    if($key1=='SEARCH_VALUE' && $val1!=''){


if (DateTime::createFromFormat('Y-m-d H:i:s', $val1) !== FALSE  || DateTime::createFromFormat('Y-m-d', $val1) !== FALSE) {


                      $datetimet = $val1;
                      $tz_tot = 'UTC';
                      $format = 'Y-m-d h:i:s';

                       $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                       $dtt->setTimeZone(new DateTimeZone($tz_tot));
                                                                                  
                       $searchFields[$key][$key1]=$dtt->format($format);

                                                                }                 

                                                    }


                                }

            }



		
            
            $productsModel = $screenBuilder->buildSysScreenModel(self::$productScreen, new Minder_SysScreen_Model_ProductDetails());
            $productsModel->setConditions($productsModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector  = $this->_helper->rowSelector;
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $productsModel, true, self::$productScreen, self::$productRowSelAction, self::$productRowSelController);
            
            $this->_preProcessNavigation();
            $totalProducts = count($productsModel);
            $this->_postProcessNavigation(array('total' => $totalProducts));
            
            $pageSelector = $this->view->navigation['pageselector'];
            $showBy       = $this->view->navigation['show_by'];
            
            $this->view->searchFields     = $searchFields;
            $this->view->searchActions    = $searchActions;
            
            $this->view->products         = $productsModel->getItems($pageSelector * $showBy, $showBy);
            $this->view->totalCount       = $totalProducts;
            $this->view->selectedProducts = $rowSelector->getSelected($pageSelector, $showBy, true, self::$productScreen, self::$productRowSelAction, self::$productRowSelController);
            $this->view->selectedCount    = $rowSelector->getSelectedCount(self::$productScreen, self::$productRowSelAction, self::$productRowSelController);
            $this->view->selectedOnPage   = count($this->view->selectedProducts);
            $this->view->productsOnPage   = count($this->view->products);
            
            list(
                $this->view->fields, 
                $this->view->tabs, 
                $this->view->colors, 
                $this->view->actions
            )                             = $screenBuilder->buildSysScreenSearchResult(self::$productScreen);
            
            $this->view->rowSelectionNamespace  = self::$productScreen;
            $this->view->rowSelectionAction     = self::$productRowSelAction;
            $this->view->rowSelectionController = self::$productRowSelController;
            $this->view->rowSelectionModule     = self::$productRowSelModule;
            
            $this->view->selectMode             = $rowSelector->getSelectionMode('', self::$productScreen, self::$productRowSelAction, self::$productRowSelController);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        $this->view->firstSelectedLine = (count($this->view->selectedProducts) > 0) ? current($this->view->selectedProducts) : array();
    }
    
    public function selectRowsAction() {
        $request = $this->getRequest();
        
        $response = new stdClass();
        $response->errors             = array();
        $response->warnings           = array();
        $response->messages           = array();
        $response->selected           = 0;
        $response->selectedRows       = array();
        $response->rowId              = null;
        $response->selectionNamespace = 'default';
        
        $showBy       = $request->getParam('show_by');
        $pageselector = $request->getParam('pageselector');
        $rowId        = $request->getParam('row_id');
        $state        = $request->getParam('state', 'init');
        
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionAction     = $request->getParam('selection_action');
        $selectionController = $request->getParam('selection_controller');
        $selectionMode       = $request->getParam('selection_mode');
        
        $rowSelector  = $this->_helper->getHelper('RowSelector');
        
        try {
            $rowSelector->setSelectionMode($selectionMode, $selectionNamespace, $selectionAction, $selectionController);
            $rowSelector->setRowSelection($rowId, $state, $pageselector, $showBy, null, false, $selectionNamespace, $selectionAction, $selectionController);
            $response->selected           = $rowSelector->getSelectedCount($selectionNamespace, $selectionAction, $selectionController);
            $response->selectedRows       = $rowSelector->getSelected($pageselector, $showBy, true, $selectionNamespace, $selectionAction, $selectionController);
            $response->rowId              = $rowId;
            $response->selectionNamespace = $selectionNamespace;

            $rowsModel = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
            
            $response->firstSelectedRow   = array();
            if (is_callable(array($rowsModel, 'selectProdIdAndShortDesc'))) {
                $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
                $response->firstSelectedRow   = $rowsModel->selectProdIdAndShortDesc(0, 1);
                $response->firstSelectedRow   = (count($response->firstSelectedRow) > 0) ? current($response->firstSelectedRow) : array();
            }
            
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

     public function saveChangesAction() {
        $this->_helper->viewRenderer->setNoRender();
        $response = new stdClass();
        $response->errors   = array();
        $response->warnings = array();
        $response->messages = array();
        
        $response->location = $this->view->url(array('action' => 'index', 'controller' => 'product-details', 'module' => 'warehouse'), null, true);
        
        try {
            $namespace = $this->getRequest()->getParam('namespace', 'none');
            $dataModel = null;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            switch ($namespace) {
                case self::$productScreen: 
                    $dataModel = new Minder_SysScreen_Model_ProductDetails();
                    break;
                default:
                    throw new Minder_Exception('Unsupported data model "' . $namespace . '".');
            }
            $dataModel = $screenBuilder->buildSysScreenModel($namespace, $dataModel);
            
            $implements = class_implements($dataModel);
            if (!isset($implements['Minder_SysScreen_Model_Editable_Interface'])) {
                throw new Minder_Exception(get_class($dataModel) . ' does not implement Minder_SysScreen_Model_Editable_Interface.'); 
            }
            
            $rowsToCreate = array();
            $rowsToUpdate = array();
            
            $dataToSave   = $this->getRequest()->getParam('data_to_save', array());
            
            foreach ($dataToSave as $field) {
                $tmpArr  = explode('-', $field['name']);
                $tmpName = $tmpArr[1];
                
                if ($field['is_new'] == 'true') {
                    $rowsToCreate[$field['row_id']][$field['name']] = array(
                        'column_id' => $field['column_id'],
                        'value'     => $field['new_value'],
                        'name'      => $tmpName
                    );
                } else {
                    $rowsToUpdate[$field['row_id']][$field['name']] = array(
                        'column_id' => $field['column_id'],
                        'value'     => $field['new_value'],
                        'name'      => $tmpName
                    );
                }
            }
            
            $cretedRecords  = $dataModel->createRecords($rowsToCreate);

            if ($cretedRecords > 0) {
                switch ($namespace) {
                    case self::$productScreen : 
                        $message = $cretedRecords . ' Product Details record(s) was created.';
                        break;
                }
                $response->messages[] = $message;
                $this->addMessage($message);
            }


            $updatedRowsIds = $dataModel->updateRecords($rowsToUpdate);
            
            foreach ($updatedRowsIds as $rowId) {
                switch ($namespace) {
                    case self::$productScreen : 
                        
                        $message = 'Product Detail #' . $rowId . ' was updated.';
                        break;
                }
                $response->messages[] = $message;
                $this->addMessage($message);
                
            }
            
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        echo json_encode($response);
    }
    
    public function getNewRowAction() {
        $this->view->jsonResponse = new stdClass();
        $this->view->jsonResponse->errors   = array();
        $this->view->jsonResponse->messages = array();
        $this->view->jsonResponse->warnings = array();
        
        try {
            $namespace = $this->getRequest()->getParam('namespace', 'none');
            $screenBuilder = new Minder_SysScreen_Builder();
            
            switch ($namespace) {

                case self::$productScreen :
                    $dataModel                  = $screenBuilder->buildSysScreenModel($namespace);
                    list(
                        $this->view->fields,
                        $this->view->tabs,
                        $this->view->colors,
                        $this->view->actions
                    )                           = $screenBuilder->buildSysScreenSearchResult($namespace);
                    $this->view->rowDefaults    = $dataModel->getRecordDefaults();
                    break;

                default : 
                    throw new Minder_Exception('Unsupported model namespace "' . $namespace . '".');
            }

            $this->view->newRowContent = $this->view->sysScreenNewRow(
                                                        array(), 
                                                        $this->view->tabs, 
                                                        null, 
                                                        $this->view->fields, 
                                                        $this->view->actions, 
                                                        $this->view->rowDefaults
            );
        } catch (Exception $e) {
            $this->view->jsonResponse->errors[] = $e->getMessage();
        }
    }

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
            $rowSelector         = $this->_helper->rowSelector;
            $selectedRowsCount   = $rowSelector->getSelectedCount($selectionNamespace, $selectionAction, $selectionController);
            $productLabelType    = $request->getParam('product_label_type', 'PRODUCT_LABEL');
            $firstLabelQty       = $request->getParam('first_label_qty', 1);
            $firstLabelTotal     = $request->getParam('first_label_total', 1);
            $secondLabelQty      = $request->getParam('second_label_qty', 0);
            $secondLabelTotal    = $request->getParam('second_label_total', 0);
        
            if ($selectedRowsCount < 1) {
                $response->warnings[] = 'No rows selected. Nothing to print.';
                return $response;
            }
        
            $rowsModel           = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
            $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
            
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
        } catch (Exception $e) {
            $response->errors[]  = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        if ($printedCount > 0) {
            $response->messages[] = 'Print request successfully sent for ' . $printedCount . ' labels.';
        }
        
        echo json_encode($response);
    }
    
    public function reportAction() {
        $request             = $this->getRequest();
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionAction     = $request->getParam('selection_action');
        $selectionController = $request->getParam('selection_controller');
        
        $rowSelector         = $this->_helper->rowSelector;
        $this->view->headers = array();
        $this->view->data    = array();
        
        $startingFrom         = 0;
        $windowSize           = 100;
        $totalRows            = $rowSelector->getTotalCount($selectionNamespace, $selectionAction, $selectionController);
        
        $this->view->data     = array();
        for (;$startingFrom <= $totalRows; $startingFrom += $windowSize) {
            $tmpResult        = $rowSelector->getSelected($startingFrom, $windowSize, false, $selectionNamespace, $selectionAction, $selectionController);
            $tmpResult        = (is_array($tmpResult)) ? $tmpResult : array();
            $this->view->data = array_merge($this->view->data, $tmpResult);
        }
        
        $rowsModel            = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
        
        if (reset($this->view->data)) {
            $this->view->headers = array_keys(current($this->view->data));
            $this->view->headers = array_combine($this->view->headers, $this->view->headers);
        }

        if (isset($this->view->headers[$rowsModel->getPkeyAlias()])) {
//            remove synthetic primary key collumn from report
            unset($this->view->headers[$rowsModel->getPkeyAlias()]);
        }
        switch (strtoupper($this->getRequest()->getParam('report_format'))) {
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
    
    /*protected function _setupShortcuts() {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('warehouse')->buildMinderMenuArray();

        return $this;
    }*/
}
