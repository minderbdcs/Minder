<?php

include_once('functions.php');


class PickInvoiceController extends Minder_Controller_Action {

    public static $invoiceModel                 = 'PICKINVOICE';
    
    public static $invoiceSelRowModule          = 'default';

    public static $invoiceLinesModel            = 'PICKINVOICELINES';
    
    public static $invoiceLinesSelRowModule     = 'default';

    public function init(){
        parent::init();
        
        $this->view->reportAction     = 'report';
        $this->view->reportController = 'pick-invoice';
        $this->view->reportModule     = 'default';
        
        $this->view->pageTitle        = 'Pick Invoice';
        
        $this->view->invoiceSysScreenName = self::$invoiceModel;
        $this->view->linesSysScreenName   = self::$invoiceLinesModel;
    }
    
    public function indexAction() {
        try {
            $request = $this->getRequest();
            $formAction = $request->getParam('SEARCH_FORM_ACTION', 'none');
            
            $screenBuilder = new Minder_SysScreen_Builder();
            list($searchFields, $searchActions) = $screenBuilder->buildSysScreenSearchFields(self::$invoiceModel);
            $searchKeeper = $this->_helper->searchKeeper;
            
            switch (strtolower($formAction)) {
                case 'search': 
                    $searchFields = $searchKeeper->makeSearch($searchFields);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields);
            }
            
            $rowSelector  = $this->_helper->rowSelector;
            $invoiceModel = $screenBuilder->buildSysScreenModel(self::$invoiceModel, new Minder_SysScreen_Model_PickInvoice());
            $invoiceModel->setConditions($invoiceModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $invoiceModel, true, self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $this->_preProcessNavigation();
            $this->view->totalCount = count($invoiceModel);
            $this->_postProcessNavigation(array('total' => $this->view->totalCount));
            
            $pageSelector = $this->view->navigation['pageselector'];
            $showBy       = $this->view->navigation['show_by'];

            $this->view->invoices         = array();
            $this->view->selectedInvoices = array();
            $this->view->selectedCount    = $rowSelector->getSelectedCount(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $this->view->invoiceSelNamespace  = self::$invoiceModel;
            $this->view->invoiceSelAction     = self::$defaultSelectionAction;
            $this->view->invoiceSelController = self::$defaultSelectionController;
            $this->view->invoiceSelModule     = self::$invoiceSelRowModule;
            
            $this->view->searchFields  = $searchFields;
            $this->view->searchActions = $searchActions;
            
            list(
                $this->view->fields,
                $this->view->tabs,
                $this->view->colors,
                $this->view->actions,
            )                                 = $screenBuilder->buildSysScreenSearchResult(self::$invoiceModel);

            list($this->view->buttons)        = $screenBuilder->buildScreenButtons(self::$invoiceModel);
            
            if ($this->view->totalCount > 0) {
                $this->view->invoices         = $invoiceModel->getItems($pageSelector * $showBy, $showBy, false);
                $this->view->selectedInvoices = $rowSelector->getSelected($pageSelector, $showBy, true, self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            }
            
            $this->view->selectMode           = $rowSelector->getSelectionMode('', self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function selectRowAction() {
        $request = $this->getRequest();
        
        $response = new stdClass();
        $response->errors                    = array();
        $response->warnings                  = array();
        $response->messages                  = array();
        $response->selected                  = 0;
        $response->selectedRows              = array();
        $response->rowId                     = null;
        $response->selectionNamespace        = 'default';
        $response->totalSelectedInvoiceValue = 0;
        
        $showBy       = $request->getParam('show_by');
        $pageselector = $request->getParam('pageselector');
        $rowId        = $request->getParam('row_id');
        $state        = $request->getParam('state', 'init');
        
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionMode       = $request->getParam('selection_mode');
        
        $rowSelector  = $this->_helper->getHelper('RowSelector');
        
        try {
            $rowSelector->setSelectionMode($selectionMode, $selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowSelector->setRowSelection($rowId, $state, $pageselector, $showBy, null, false, $selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $response->selected           = $rowSelector->getSelectedCount($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $response->selectedRows       = $rowSelector->getSelected($pageselector, $showBy, true, $selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $response->rowId              = $rowId;
            $response->selectionNamespace = $selectionNamespace;

            if ($response->selected > 0) {
                
                $linesModel = $rowSelector->getModel($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $linesModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController));
                
                if (is_callable(array($linesModel, 'getTotalInvoiceValue'))) {
                    $response->totalSelectedInvoiceValue = $linesModel->getTotalInvoiceValue();
                }
            }
            
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function getLinesAction() {
        try {
            $this->view->pageTitle          = 'Invoice Lines List';
            $this->view->errors             = array();
            $this->view->lines              = array();
            $this->view->linesCount         = 0;
            $this->view->selectedLines      = array();
            $this->view->selectedLinesCount = 0;
            
            $this->view->linesSelNamespace  = self::$invoiceLinesModel;
            $this->view->linesSelAction     = self::$defaultSelectionAction;
            $this->view->linesSelController = self::$defaultSelectionController;
            $this->view->linesSelModule     = self::$invoiceLinesSelRowModule;

            $rowSelector                    = $this->_helper->getHelper('RowSelector');
            $this->view->selectMode         = $rowSelector->getSelectionMode('', self::$invoiceLinesModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            $selectedInvoiceCount           = $rowSelector->getSelectedCount(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $this->_preProcessNavigation();
            
            $screenBuilder                  = new Minder_SysScreen_Builder();
            $linesModel                     = new Minder_SysScreen_Model_PickInvoiceLine();
            $linesModel                     = $screenBuilder->buildSysScreenModel(self::$invoiceLinesModel, $linesModel);

            if ($selectedInvoiceCount > 0) {
                $totalInvoices    = $rowSelector->getTotalCount(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
                $invoiceModel     = $rowSelector->getModel(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
                
                $invoiceModel->addConditions($rowSelector->getSelectConditions(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController));
                
                $selectedInvoices = $invoiceModel->selectInvoiceNo(0, $totalInvoices);
                
                $tmpCondString = 'PICK_INVOICE_LINE.INVLINE_INVOICE_NO IN (' . substr(str_repeat('?, ', count($selectedInvoices)), 0, -2) . ')';
                
                $linesModel->setConditions(array($tmpCondString => $selectedInvoices));
                $rowSelector->setRowSelection(
                    'select_complete', 
                    'init', 
                    null, 
                    null, 
                    $linesModel, 
                    true, 
                    self::$invoiceLinesModel, 
                    self::$defaultSelectionAction,
                    self::$defaultSelectionController
                );
            
                $this->view->linesCount         = count($linesModel);
                $this->_postProcessNavigation(array('total' => $this->view->linesCount));

                $pageSelector                   = $this->view->navigation['pageselector'];
                $showBy                         = $this->view->navigation['show_by'];

                $this->view->lines              = $linesModel->getItems($pageSelector*$showBy, $showBy, false);
                $this->view->selectedLines      = $rowSelector->getSelected($pageSelector, $showBy, true, self::$invoiceLinesModel, self::$defaultSelectionAction, self::$defaultSelectionController);
                $this->view->selectedLinesCount = $rowSelector->getSelectedCount(self::$invoiceLinesModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            } else {
                $this->_postProcessNavigation(array('total' => 0));
                $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::$invoiceLinesModel, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

            list(
                $this->view->linesFields,
                $this->view->linesTabs,
                $this->view->linesColors,
                $this->view->linesActions
            )                                   = $screenBuilder->buildSysScreenSearchResult(self::$invoiceLinesModel);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function reportAction() {
        $request             = $this->getRequest();
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionAction     = self::$defaultSelectionAction;
        $selectionController = self::$defaultSelectionController;
        
        $rowSelector         = $this->_helper->rowSelector;
        $this->view->headers = array();
        $this->view->data    = array();
        
        $totalRows            = $rowSelector->getTotalCount($selectionNamespace, $selectionAction, $selectionController);
        $this->view->data     = $rowSelector->getSelected(0, $totalRows, false, $selectionNamespace, $selectionAction, $selectionController);
        $rowsModel            = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
        
        if (reset($this->view->data)) {
            $this->view->headers = array_keys(current($this->view->data));
            $this->view->headers = array_combine($this->view->headers, $this->view->headers);
        }

        if (isset($this->view->headers[$rowsModel->getPkeyAlias()])) {
            //remove synthetic primary key collumn from report
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
    
    /**
    * Build list of readonly fields for given dataset based on current user rights
    * 
    * @param MasterTable_DataSet $dataset - dataset object to build list for
    * @return array - list of readonly fields for given dataset
    */
    protected function _getReadonlyFields($dataset) {
        $readOnlyFields = array('INVOICE_LINE_ID' => 'INVOICE_LINE_ID', 'INVOICE_ID' => 'INVOICE_ID');
        if ($this->minder->userId != 'Admin') {
            $alowedFields   = array('FREIGHT', 'ADMIN_FEE_RATE', 'ADMIN_FEE_AMOUNT', 'INVOICE_STATUS', 'TAX_RATE', 'INVLINE_TAX_RATE', 'INVLINE_LEGACY_LEDGER_CODE');
            $readOnlyFields = array_keys($dataset->getFields());
            $readOnlyFields = array_combine($readOnlyFields, $readOnlyFields);
            
            foreach ($alowedFields as $field) {
                if (isset($readOnlyFields[$field])) unset($readOnlyFields[$field]);
            }
            
        }
        
        return $readOnlyFields;
    }
    
    /**
    * Build edit form for given dataset
    * 
    * @param MasterTable_DataSet $dataset - dataset object to build form for
    * @param array               $readOnlyFields - list of fields, which should be readonly
    * @return Zend_Form - form object
    */
    protected function _buildEditForm($dataset, $readOnlyFields) {
        $dataset->rewind();
        $dataRow        = $dataset->current();
        $form           = new Zend_Form();
        $formElement    = null;
        
        foreach ($dataRow as $key => $value) {
            switch ($key) {
                case 'INVOICE_STATUS': 
                    $formElement = new Zend_Form_Element_Select($key);
                    $statuses    = $this->minder->getOptionsList('INV_STATUS');
                    $statuses    = array_merge(array('' => ''), is_array($statuses) ? $statuses : array());
                    $formElement->setMultiOptions($statuses);
                    
                    if (isset($readOnlyFields[$key])) {
                        $formElement->setAttrib('disabled', true);
                    }
                    
                    break;
                default:
                    $formElement = new Zend_Form_Element_Text($key);
            }
            
            $formElement->setValue($value);
            $fieldDesc = $dataset->getFieldInfo($key);
            
            switch ($fieldDesc->type) {
                case 'CHAR':
                case 'VARCHAR':
                case 'INTEGER':
                    $formElement->setAttrib('DB_FIELD_TYPE', $fieldDesc->type .  ' (' . $fieldDesc->length . ')');
                    $formElement->setLabel($key);
                    $formElement->addValidator('StringLength', false, array('min' => 0, 'max' => $fieldDesc->length));
                    break;
                default:
                    $formElement->setAttrib('DB_FIELD_TYPE', $fieldDesc->type);
                    $formElement->setLabel($key);
            }
            if ($fieldDesc->type == 'TIMESTAMP')
                $formElement->setAttrib('class', 'withdatepicker');
            
            if (isset($readOnlyFields[$key])) {
                $formElement->setAttrib('readonly', true);
            }
            
            $form->addElement($formElement);
        }
        
        return $form;
    }
    
    /**
    * Create MasterTable_DataSet object for selected model and fill it with data of selected record
    * 
    * @param string $modelName - name of the model to build dataset for
    * @param string $rowId     - ROW_ID or record to fetch data
    * @return MasterTable_DataSet
    */
    protected function _getDataset($modelName, $rowId) {
        $clause            = array();
        $screenBuilder     = new Minder_SysScreen_Builder();
    
        switch ($modelName) {
            case self::$invoiceLinesModel : 
                $tableName = 'PICK_INVOICE_LINE';
                $model     = new Minder_SysScreen_Model_PickInvoiceLine();
                $model     = $screenBuilder->buildSysScreenModel(self::$invoiceLinesModel, $model);
                $model->addConditions($model->makeConditionsFromId($rowId, false));
                $result    = $model->selectLineId(0, 1);
                
                if (count($result) < 1)
                    throw new Minder_Exception('Record not found.');
                    
                $clause['PICK_INVOICE_LINE.INVOICE_LINE_ID = ?'] = $result[0];
                break;

            case self::$invoiceModel : 
                $tableName = 'PICK_INVOICE';
                $model     = new Minder_SysScreen_Model_PickInvoice();
                $model     = $screenBuilder->buildSysScreenModel(self::$invoiceModel, $model);
                $model->addConditions($model->makeConditionsFromId($rowId, false));
                $result    = $model->selectInvoiceNo(0, 1);
                
                if (count($result) < 1)
                    throw new Minder_Exception('Record not found.');
                    
                $clause['PICK_INVOICE.INVOICE_NO = ?'] = $result[0];
                break;
            default:
                throw new Minder_Exception('Bad model name.');
        }
        
        $dataset = $this->minder->getMasterTableDataSet($tableName, array('*'), $clause, array());
        
        if (false === $dataset || count($dataset) < 1) 
            throw new Minder_Exception('Record not found.');
            
        return $dataset;
    }
    
    public function getEditFormAction() {
        if (!$this->minder->isAdmin) {
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        $this->view->errors = array();
        
        try {
            $tableName = '';
            $modelName = $this->getRequest()->getParam('model_name');
            $rowId     = $this->getRequest()->getParam('row_id');
            if (empty($rowId))
                throw new Minder_Exception('Record not found.');

            $dataset   = $this->_getDataset($modelName, $rowId);
            $this->view->editForm = $this->_buildEditForm($dataset, $this->_getReadonlyFields($dataset));
            $this->view->modelName = $modelName;
            $this->view->rowId     = $rowId;
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->_helper->viewRenderer->setNoRender();
        }
    }
    
    public function saveEditFormAction() {
        if (!$this->minder->isAdmin) {
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
        
        $result           = new stdClass();
        $result->errors   = array();
        $result->messages = array();
        
        try {
            $tableName      = '';
            $modelName      = $this->getRequest()->getParam('model_name');
            $rowId          = $this->getRequest()->getParam('row_id');

            $dataset        = $this->_getDataset($modelName, $rowId);
            $readonlyFields = $this->_getReadonlyFields($dataset);
            $editForm       = $this->_buildEditForm($dataset, $readonlyFields);
            
            $params = $this->getRequest()->getParams();

            if (isset($params['model_name'])) unset($params['model_name']);
            if (isset($params['row_id'])) unset($params['row_id']);

            foreach ($readonlyFields as $field) {
                if (isset($params[$field])) unset($params[$field]);
            }
            $params['LAST_UPDATE_BY'] = $this->minder->userId;
            
            if ($editForm->isValidPartial($params)) {
                
                $dataset->rewind();
                $selectedRecord = $dataset->getRecord($dataset->current()->id);
                if ($selectedRecord->save($editForm->getValues())) {
                    $dataset->setRecord($selectedRecord);
                    if ($this->minder->updateMasterTableDataSet($dataset)) {
                        $result->messages[] = 'Record ' . $rowId . ' updated successfully.';
                    } else {
                        $result->errors[] = $this->minder->lastError;
                    }
                } else {
                    $result->errors = array_merge($result->errors, $selectedRecord->getValidationErrorList());
                }
            } else {
                $errorMessages   = $editForm->getMessages();
                
                foreach ($errorMessages as $field => $fieldErrors) {
                    foreach ($fieldErrors as $errorCode => $errorMessage) {
                        $result->errors[] = $field . ': ' . $errorCode . ': ' . $errorMessage;
                    }
                }
            }
            
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $params));
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($result);
    }
    
    public function saveInvoiceChangesAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new stdClass();
        $result->errors   = array();
        $result->warnings = array();
        $result->messages = array();
        
        $dataToSave = $this->getRequest()->getParam('data_to_save', array());
        
        if (count($dataToSave) > 0) {
            $rowsToUpdate = array();
        
            foreach ($dataToSave as $field) {
                $tmpArr   = explode('-', $field['name']);
                $tmpTable = $tmpArr[0];
                $tmpName  = $tmpArr[1];
                
                $rowsToUpdate[$field['row_id']][$field['name']] = array(
                    'column_id' => $field['column_id'],
                    'value'     => $field['new_value'],
                    'table'     => $tmpTable,
                    'name'      => $tmpName
                );
            }
            
            $screenBuilder = new Minder_SysScreen_Builder();
            $invoiceModel = $screenBuilder->buildSysScreenModel(self::$invoiceModel, new Minder_SysScreen_Model_PickInvoice());
            
            try {
                $updatedRowsIds = $invoiceModel->updateRecords($rowsToUpdate);
                $result->warnings = $invoiceModel->getWarnings();
            
                foreach ($updatedRowsIds as $rowId) {
                    $result->messages[] = 'Invoice #' . $rowId . ' updated.';
                }
            } catch (Exception $e) {
                $result->errors[] = $e->getMessage();
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            }
        }
        
        foreach ($result->messages as $message) {
            $this->addMessage($message);
        }
        
        foreach ($result->warnings as $warning) {
            $this->addWarning($warning);
        }

        echo json_encode($result);
    }
    
    public function holdInvoiceAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new stdClass();
        $result->errors   = array();
        $result->warnings = array();
        $result->messages = array();
        $result->invoiceHelded = false;
        
        /**
        * @var Minder_Controller_Action_helper_RowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        
        $selectedRows = $rowSelector->getSelectedCount(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        if ($selectedRows < 1) {
            $result->warnings[] = 'No rows selected, select one.';
            echo json_encode($result);
            return;
        }

        /**
        * @var Minder_SysScreen_Model_PickInvoice
        */
        $invoiceModel = $rowSelector->getModel(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        $invoiceModel->addConditions($rowSelector->getSelectConditions(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController));
        
        try {
            $heldInvoices = $invoiceModel->hold();

            foreach ($heldInvoices as $invoiceId) {
                $result->messages[] ='Invoice #' . $invoiceId . ' held.';
            }

            $result->invoiceHelded = true;
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        foreach ($result->messages as $message) {
            $this->addMessage($message);
        }
        
        foreach ($result->warnings as $warning) {
            $this->addWarning($warning);
        }

        echo json_encode($result);
    }

    public function printInvoiceAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        /**
        * @var Minder_Controller_Action_helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->rowSelector;

        $selectedRows = $rowSelector->getSelectedCount(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        if ($selectedRows < 1) {
            $result->warnings[] = 'No rows selected, select one.';
            echo json_encode($result);
            return;
        }

        /**
        * @var Minder_SysScreen_Model_PickInvoice $invoiceModel
        */
        $invoiceModel = $rowSelector->getModel(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        $invoiceModel->addConditions($rowSelector->getSelectConditions(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController));

        try {
            $result->savedInvoices = $invoiceModel->printInvoice($this->minder->getPrinter());

            foreach ($result->savedInvoices as $dataRow) {
                $result->messages[] = 'Print Invoice request was send for PICK_ORDER #' . $dataRow['pickOrder'];
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function printInvoiceReportAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        $invoiceType  = $this->getRequest()->getParam('reportType');
        $paramsMap = $this->getRequest()->getParam('paramsMap', array());
        $displayReports = $this->getRequest()->getParam('displayReports', false);

        if (empty($invoiceType)) {
            $result->errors[] = 'No Report Type.';
            echo json_encode($result);
            return;
        }

        /**
        * @var Minder_Controller_Action_helper_RowSelector $rowSelector
        */
        $rowSelector  = $this->_helper->rowSelector;
        $selectedRows = $rowSelector->getSelectedCount(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        if ($selectedRows < 1) {
            $result->warnings[] = 'No rows selected, select one.';
            echo json_encode($result);
            return;
        }

        /**
        * @var Minder_SysScreen_Model_PickInvoice $invoiceModel
        */
        $invoiceModel = $rowSelector->getModel(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController);
        $invoiceModel->addConditions($rowSelector->getSelectConditions(self::$invoiceModel, self::$defaultSelectionAction, self::$defaultSelectionController));

        try {
            $result->savedInvoices = $invoiceModel->printInvoice($invoiceType, $paramsMap, $this->minder->getPrinter());

            foreach ($result->savedInvoices as $dataRow) {
                $result->messages[] = 'Print Invoice request was send for PICK_ORDER #' . $dataRow['pickOrder'];
            }

            if (!$displayReports)
                $result->savedInvoices = array();

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function showInvoiceAction() {
        $pickOrder = $this->getRequest()->getParam('pickOrder');
        $uniqNo    = $this->getRequest()->getParam('uniqNo');
        $year      = $this->getRequest()->getParam('year');
        $month     = $this->getRequest()->getParam('month');

        /**
         * @var PickOrder $pickOrderObject
         */
        if (false === ($pickOrderObject = $this->minder->getPickOrder($pickOrder))) {
            $this->addError('Pick Order #' . $pickOrder . ' not found.');
            return $this->_redirector->gotoSimple('index');
        }

        $companyList = $this->minder->getCompanyListLimited();
        if (!isset($companyList[$pickOrderObject->companyId])) {
            $this->addError('Access denied.');
            return $this->_redirector->gotoSimple('index');
        }

        /**
         * @var Company $companyObject
         */
        $companyObject = $this->minder->getCompany($pickOrderObject->companyId);
        if (is_null($companyObject)) {
            $this->addError('Company #' . $pickOrderObject->companyId . ' not found.');
            return $this->_redirector->gotoSimple('index');
        }

        try {
            $pdfImage = $companyObject->loadInvoiceImage($pickOrder, $uniqNo, $year, $month);
        } catch (Exception $e) {
            $this->addError('Cannot get Invoice PDF: ' . $e->getMessage());
            return $this->_redirector->gotoSimple('index');
        }

        $this->_helper->viewRenderer->setNoRender(true);
        $this->getResponse()->setHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $companyObject->formatInvoiceFileName($pickOrder, '') . '"');
        echo $pdfImage;

        return;
    }

    /*protected function _setupShortcuts() {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('orders')->buildMinderMenuArray();

        return $this;
    }*/
}
