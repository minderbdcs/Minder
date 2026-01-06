<?php
/**
 * Minder
 *
 * PHP version 5.2.5
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Golovin <sergey.golovin@binary-studio.com>
 * @copyright 2010 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 * 
 * @todo move all controllers connected with despatch menu into despatches module
 *
 */


class PersonController extends Minder_Controller_Action
{
    protected static $personModelName           = 'PERSON';
    protected static $personSelectionAction     = 'select-rows';
    protected static $personSelectionController = 'person';
    protected static $personSelectionModule     = 'default';

    protected static $personAddressModelName           = 'PERSONADDRESS';
    protected static $personAddressSelectionAction     = 'select-rows';
    protected static $personAddressSelectionController = 'person';
    protected static $personAddressSelectionModule     = 'default';

    public function init() {
        parent::init();
        
        $this->view->pageTitle = 'Person';
        
        $this->view->reportModule     = 'default';
        $this->view->reportController = 'person';
        $this->view->reportAction     = 'report';
        
        $this->view->addresLabelModule     = 'default';
        $this->view->addresLabelController = 'person';
        $this->view->addresLabelAction     = 'print-label';
        
        $this->view->personSysScreenName   = self::$personModelName;
        $this->view->addressSysScreenName  = self::$personAddressModelName;

        $this->view->personSelNamespace  = self::$personModelName;
        $this->view->personSelAction     = self::$personSelectionAction;
        $this->view->personSelController = self::$personSelectionController;
        $this->view->personSelModule     = self::$personSelectionModule;

        $this->view->addressSelectionNamespace  = self::$personAddressModelName;
        $this->view->addressSelectionAction     = self::$personAddressSelectionAction;
        $this->view->addressSelectionController = self::$personAddressSelectionController;
        $this->view->addressSelectionModule     = self::$personAddressSelectionModule;

    }

    protected function _sortButtons($a, $b) {
        return $a[$a["ORDER_BY_FIELD_NAME"]] - $b[$b["ORDER_BY_FIELD_NAME"]];
    }

    public function indexAction() {
        try {
            $request = $this->getRequest();
            $formAction = $request->getParam('SEARCH_FORM_ACTION', 'none');
            
            $screenBuilder = new Minder_SysScreen_Builder();
            list($searchFields, $searchActions) = $screenBuilder->buildSysScreenSearchFields(self::$personModelName);

            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper = $this->_helper->searchKeeper;

            list($this->view->personButtons) = $screenBuilder->buildScreenButtons(self::$personModelName);
            usort($this->view->personButtons, array($this, '_sortButtons'));

            switch (strtolower($formAction)) {
                case 'search': 
                    $searchFields = $searchKeeper->makeSearch($searchFields);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields);
            }
            
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector = $this->_helper->rowSelector;
            $personModel = $screenBuilder->buildSysScreenModel(self::$personModelName, new Minder_SysScreen_Model_Person());
            $personModel->setConditions($personModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $personModel, true, self::$personModelName, self::$personSelectionAction, self::$personSelectionController);
            
            $this->_preProcessNavigation();
            $this->view->totalCount = count($personModel);
            $this->_postProcessNavigation(array('total' => $this->view->totalCount));
            
            $pageSelector = $this->view->navigation['pageselector'];
            $showBy       = $this->view->navigation['show_by'];

            $this->view->persons         = array();
            $this->view->selectedPersons = array();
            $this->view->selectedCount   = $rowSelector->getSelectedCount(self::$personModelName, self::$personSelectionAction, self::$personSelectionController);
            
            $this->view->searchFields  = $searchFields;
            $this->view->searchActions = $searchActions;
            
            list(
                $this->view->fields,
                $this->view->tabs,
                $this->view->colors,
                $this->view->actions
            )                                 = $screenBuilder->buildSysScreenSearchResult(self::$personModelName);
            
            if ($this->view->totalCount > 0) {
                $this->view->persons         = $personModel->getItems($pageSelector * $showBy, $showBy, false);
                $this->view->selectedPersons = $rowSelector->getSelected($pageSelector, $showBy, true, self::$personModelName, self::$personSelectionAction, self::$personSelectionController);
            }

            $this->view->selectMode           = $rowSelector->getSelectionMode('', self::$personModelName, self::$personSelectionAction, self::$personSelectionController);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function getAddressesAction() {
        $this->view->errors = array();
        $this->_preProcessNavigation();
        
        $request = $this->getRequest();
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $screenBuilder = new Minder_SysScreen_Builder();
        $this->view->addresses              = array();
        $this->view->totalAddresses         = 0;
        $this->view->selectedAddresses      = array();
        $this->view->selectedAddressesCount = 0;
        
        try {
            list($this->view->personAddressButtons) = $screenBuilder->buildScreenButtons(self::$personAddressModelName);
            usort($this->view->personAddressButtons, array($this, '_sortButtons'));            $rowSelector = $this->_helper->getHelper('RowSelector');
            
            $selectedPersonsCount = $rowSelector->getSelectedCount(self::$personModelName, self::$personSelectionAction, self::$personSelectionController);

            if ($selectedPersonsCount > 0) {
                
                $personsModel = $rowSelector->getModel(self::$personModelName, self::$personSelectionAction, self::$personSelectionController);
                $personsModel->addConditions($rowSelector->getSelectConditions(self::$personModelName, self::$personSelectionAction, self::$personSelectionController));
                
                $selectedPersons = $personsModel->selectPersonId(0, count($personsModel));
                $tmpCondString   = 'PERSON_ADDRESS.PERSON_ID IN (' . substr(str_repeat('?, ', count($selectedPersons)), 0, -2) . ')';
                
                $addressesModel  = $screenBuilder->buildSysScreenModel(self::$personAddressModelName, new Minder_SysScreen_Model_PersonAddress());
                $addressesModel->setConditions(array($tmpCondString => $selectedPersons));
                
                $rowSelector->setRowSelection('select_complete', 'init', null, null, $addressesModel, true, self::$personAddressModelName, self::$personAddressSelectionAction, self::$personAddressSelectionController);
            
                $this->view->totalAddresses         = count($addressesModel);
                $this->view->selectedAddressesCount = $rowSelector->getSelectedCount(self::$personAddressModelName, self::$personAddressSelectionAction, self::$personAddressSelectionController);
                $this->_postProcessNavigation(array('total' => $this->view->totalAddresses));
                $pageSelector                       = $this->view->navigation['pageselector'];
                $showBy                             = $this->view->navigation['show_by'];

                $this->view->addresses              = $addressesModel->getItems($pageSelector*$showBy, $showBy, false);
                $this->view->selectedAddresses      = $rowSelector->getSelected($pageSelector, $showBy, true, self::$personAddressModelName, self::$personAddressSelectionAction, self::$personAddressSelectionController);
                $this->view->selectMode             = $rowSelector->getSelectionMode('', self::$personAddressModelName, self::$personAddressSelectionAction, self::$personAddressSelectionController);
            } else {
                $this->_postProcessNavigation(array('total' => 0));
            }
            list($this->view->fields, $this->view->tabs, , $this->view->actions) = $screenBuilder->buildSysScreenSearchResult(self::$personAddressModelName);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function saveChangesAction() {
        $response = new stdClass();
        $response->errors   = array();
        $response->warnings = array();
        $response->messages = array();
        
        $response->location = $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'default'), null, true);
        
        try {
            $namespace = $this->getRequest()->getParam('namespace', 'none');
            $dataModel = null;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            switch ($namespace) {
                case self::$personModelName : 
                    $dataModel = new Minder_SysScreen_Model_Person();
                    break;
                case self::$personAddressModelName : 
                    $dataModel = new Minder_SysScreen_Model_PersonAddress();
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
            
            switch ($namespace) {
                case self::$personModelName :
                    $cretedRecords  = $dataModel->createRecords($rowsToCreate);
                    break;
                case self::$personAddressModelName :
                    $persons = $this->_getSelectedPersonId();

                    if (empty($persons))
                        throw new Exception('No Person selected. Please select one to add address.');

                    if (count($persons) > 1)
                        throw new Exception('Cannot add address to many persons at once. Please select only one PERSON_ID to add address.');

                    $cretedRecords  = $dataModel->createRecords($rowsToCreate, current($persons));
                    break;
                default:
                    throw new Minder_Exception('Unsupported data model "' . $namespace . '".');
            }

            if ($cretedRecords > 0) {
                switch ($namespace) {
                    case self::$personModelName : 
                        $message = $cretedRecords . ' Person record(s) was created.';
                        break;
                    case self::$personAddressModelName : 
                        $message = $cretedRecords . ' Person Adress record(s) was created.';
                        break;
                }
                $response->messages[] = $message;
                $this->addMessage($message);
            }


            $updatedRowsIds = $dataModel->updateRecords($rowsToUpdate);

            foreach ($updatedRowsIds as $rowId) {
                switch ($namespace) {
                    case self::$personModelName : 
                        $message = 'Person #' . $rowId . ' was updated.';
                        break;
                    case self::$personAddressModelName : 
                        $message = 'Person address #' . $rowId . ' was updated.';
                        break;
                }
                $response->messages[] = $message;
                $this->addMessage($message);
                
            }
            
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

    /**
     * @return array
     */
    protected function _getSelectedPersonId() {
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->rowSelector;

        $totalPersonRowsSelected = $rowSelector->getSelectedCount(self::$personModelName, self::$personSelectionAction, self::$personSelectionController);

        if ($totalPersonRowsSelected < 1)
            return array();

        /**
         * @var Minder_SysScreen_Model_Person $model
         */
        $model = $rowSelector->getModel(self::$personModelName, self::$personSelectionAction, self::$personSelectionController);
        $model->addConditions($rowSelector->getSelectConditions(self::$personModelName, self::$personSelectionAction, self::$personSelectionController));

        return $model->selectPersonId(0, $totalPersonRowsSelected);
    }
    
    public function getNewRowAction() {
        $response = new Minder_JSResponse();
        try {
            $namespace = $this->getRequest()->getParam('namespace', 'none');
            $screenBuilder = new Minder_SysScreen_Builder();
            
            switch ($namespace) {

                case self::$personAddressModelName :
                    $persons = array_unique($this->_getSelectedPersonId());

                    if (empty($persons))
                        throw new Exception('No Person selected. Please select one to add address.');

                    if (count($persons) > 1)
                        throw new Exception('Cannot add address to many persons at once. Please select only one PERSON_ID to add address.');

                    $personId = current($persons);
                case self::$personModelName :
                    $dataModel                  = $screenBuilder->buildSysScreenModel($namespace);
                    list(
                        $this->view->fields,
                        $this->view->tabs,
                        $this->view->colors,
                        $this->view->actions
                    )                           = $screenBuilder->buildSysScreenSearchResult($namespace);
                    $this->view->rowDefaults    = $dataModel->getRecordDefaults();

                    if ($namespace == self::$personAddressModelName)
                        $this->view->rowDefaults['PERSON_ID'] = $personId;

                    if ($namespace == self::$personModelName)
                        $this->view->rowDefaults['COMPANY_ID'] = empty($this->view->rowDefaults['COMPANY_ID']) ? 'ALL' : $this->view->rowDefaults['COMPANY_ID'];

                    Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->rowDefaults));
                    break;

                default : 
                    throw new Minder_Exception('Unsupported model namespace "' . $namespace . '".');
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        $this->view->jsonResponse = $response;
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
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
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
    
    public function printLabelAction() {
        $this->_helper->viewRenderer->setNoRender();

        $response                = new stdClass();
        $response->errors        = array();
        $response->warnings      = array();
        $response->messages      = array();
        
        try {
            $request             = $this->getRequest();
            $selectionNamespace  = $request->getParam('selection_namespace', 'default');
            $selectionAction     = $request->getParam('selection_action');
            $selectionController = $request->getParam('selection_controller');
            $rowSelector         = $this->_helper->rowSelector;
            $rowsModel           = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
            $implements          = class_implements($rowsModel);

            if (!isset($implements['Minder_SysScreen_Model_AddressLabelProvider_Interface'])) 
                throw new Exception('Error: cannot print ADDRESS LABEL. ' . get_class($rowsModel) . ' does not implement Minder_SysScreen_Model_AddressLabelProvider_Interface.');
            
            if ($rowSelector->getSelectedCount($selectionNamespace, $selectionAction, $selectionController) < 1) {
                $response->warnings[] = 'No rows selected. Nothing to print.';
                echo json_encode($response);
                return;
            }
            
            $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
            $addressLabels    = $rowsModel->selectAddressLabelData(0, count($rowsModel));
            $labelTypeCache   = array();
            $printerObj       = $this->minder->getPrinter();
            $printedCount     = 0;
            foreach ($addressLabels as $label) {
                $addressType = '';
                if (isset($label['_ADDR_TYPE_'])) {
                    $addressType = $label['_ADDR_TYPE_'];
                    //_ADDR_TYPE_ is service field, so unset it
                    unset($label['_ADDR_TYPE_']);
                }
                
                $labelType                    = (isset($labelTypeCache[$addressType])) ? $labelTypeCache[$addressType] : $this->minder->getLabelTypeForAddress($addressType);
                $labelTypeCache[$addressType] = $labelType;
                
                if (false === ($result = $printerObj->printAddressLabel($label, strtoupper($labelType)))) 
                    $response->errors[] = 'Error printing Address Labels: Unknown error.';
                elseif ($result['RES'] < 0) 
                    $response->errors[] = 'Error printing Address Labels: ' . $result['ERROR_TEXT'] . '.';
                else
                    $printedCount++;
            }
            
            if ($printedCount > 0) {
                $response->messages[] = 'Print request for ' . $printedCount . ' label(s) successfully sent.';
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        echo json_encode($response);
    }
    
}
