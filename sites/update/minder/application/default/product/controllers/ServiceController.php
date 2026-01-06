<?php

/**
* Performes common tasks with system screens
*/
class ServiceController extends Minder_Controller_Action
{
    public function init() {
        parent::init();
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
        
        $response->totalSummary       = array();
        $response->selectedSummary    = array();
        
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
            
            $response->selectedSummary = $response->totalSummary = $rowsModel->selectSummary();
            foreach ($response->selectedSummary as &$summary) $summary = 0;
            
            if ($response->selected > 0) {
                $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
                $response->selectedSummary = $rowsModel->selectSummary();
            }
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

        $this->view->data     = $rowSelector->getSelectedRows($selectionNamespace, $selectionAction, $selectionController);
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
            case 'REPORT: XML':
            case 'REPORT: TXT':
            case 'REPORT: CSV':
            case 'REPORT: XLS':
            case 'XLSX':
            case 'REPORT: XLSX':
                $this->_helper->viewRenderer->setNoRender(true);
                $this->getHelper('exportTo')->exportTo($this->getRequest()->getParam('report_format'), $this->view->headers, $this->view->data);
                return;

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

    public function runReportAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        $reportId  = $this->getRequest()->getParam('reportId');
        $namespace = $this->getRequest()->getParam('namespace');
        $paramsMap = $this->getRequest()->getParam('paramsMap', array());
        $displayReports = $this->getRequest()->getParam('displayReports', false);

        if (is_string($displayReports))
            $displayReports = ($displayReports == 'true') ? true : false;

        $result->displayReports = $displayReports;

        if (empty($reportId)) {
            $result->errors[] = 'No Report Id.';
            echo json_encode($result);
            return;
        }

        if (empty($namespace)) {
            $result->errors[] = 'No Sys Screen.';
            echo json_encode($result);
            return;
        }

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        $printedReports = 0;
        $printedFiles   = array();

        try {
            $selectedCount = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            if ($selectedCount < 1) {
                $result->errors[] = 'No Rows selected.';
                echo json_encode($result);
                return;
            }

            $report = Minder_Report_Factory::makeReport($reportId);
            foreach ($paramsMap as $modelParam => $reportParam) {
                $matches = array();
                if (preg_match('/^CONST:(.*)/', $modelParam, $matches)) {
                    $report->setQueryFieldValue($reportParam, $matches[1]);
                    unset($paramsMap[$modelParam]);
                }
            }

            if (empty($paramsMap)) {
                for ($index = 0; $index < $selectedCount; $index++) {
                    $printedFiles[] = $report->printAndSavePdfImage($this->minder->getPrinter());
                    $printedReports++;
                }
            } else {
                $model = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $model->addConditions($rowSelector->getSelectConditions($namespace, self::$defaultSelectionAction, self::$defaultSelectionController));

                $fieldsToFetch = array();
                $newMap = array();

                foreach ($paramsMap as $modelField => $queryField) {
                    $fieldsToFetch[] = $modelField . ' AS ' . $queryField;
                    $newMap[strtoupper($queryField)] = $queryField;
                }

                foreach ($model->selectArbitraryExpression(0, $selectedCount, 'DISTINCT ' . implode(', ', $fieldsToFetch)) as $resultRow) {
                    $printedFiles[] = $report
                        ->fillQueryFieldsWithMap($resultRow, $newMap)
                        ->printAndSavePdfImage($this->minder->getPrinter());
                    $printedReports++;
                }
            }

            if ($displayReports) {
                $result->reports = $printedFiles;
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        $result->messages[] = $printedReports . ' reports where printed.';

        echo json_encode($result);
    }

    public function runReportDirectAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        $reportId  = $this->getRequest()->getParam('reportId');
        $paramsMap = $this->getRequest()->getParam('paramsMap', array());
        $displayReports = $this->getRequest()->getParam('displayReports', false);

        if (is_string($displayReports))
            $displayReports = ($displayReports == 'true') ? true : false;

        $result->displayReports = $displayReports;

        if (empty($reportId)) {
            $result->errors[] = 'No Report Id.';
            echo json_encode($result);
            return;
        }

        $reportFiles   = array();

        try {
            $report = Minder_Report_Factory::makeReport($reportId);
            foreach ($paramsMap as $reportParam => $modelParam) {
                $matches = array();
                if (preg_match('/^CONST:(.*)/', $modelParam, $matches)) {
                    $report->setQueryFieldValue($reportParam, $matches[1]);
                    unset($paramsMap[$modelParam]);
                }
            }

            $reportFiles[] = $report->preparePdfImage();

            if ($displayReports) {
                $result->reports = $reportFiles;
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function showReportAction() {
        $reportFileId = $this->getRequest()->getParam('fileId');
        if (empty($reportFileId)) {
            $this->addError('No Report File given.');
            $this->_forward('index', 'index', 'default');
            return;
        }

        try {
            $pdfImage = Minder_Report_Abstract::loadSavedPdfImage($reportFileId);
            $this->_helper->viewRenderer->setNoRender(true);
            $this->getResponse()->setHeader('Content-Type', 'application/pdf');
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="report.pdf"');
            echo $pdfImage;
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            $this->_forward('index', 'index', 'default');
            return;
        }
    }
    
    protected function _getFormController($sysScreen, $type) {
        $controllerBuilder = new Minder_Page_FormController_Builder();
        return $controllerBuilder->build($sysScreen, $type);
    }

    protected function _setupDbAdapters() {
        $dsn = Minder::$dbLiveDsn;
        $dsnArray = explode(':', $dsn);
        $name = isset($dsnArray[1]) ? $dsnArray[1] : $dsnArray[0];
        $host = isset($dsnArray[1]) ? $dsnArray[0] : 'localhost';
        $db = Zend_Db::factory('Firebird', array('adapterNamespace' => 'ZendX_Db_Adapter', 'host' => $host, 'username' => Minder::$dbUser, 'password' => Minder::$dbPass, 'dbname' => $name));
        Zend_Db_Table::setDefaultAdapter($db);
        Minder_Db_SysScreenTable::setDefaultAdapter(Zend_Db::factory('SysScreen', array('adapterNamespace' => 'Minder_Db_Adapter')));
    }

    public function sysScreenAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $response = array(
            'result' => null,
            'error'  => null,
            'id' => $this->getRequest()->getParam('id')
        );

        $sysScreen = $this->getRequest()->getParam('name');
        $type = $this->getRequest()->getParam('type');
        $method = $this->getRequest()->getParam('method');

        try {
            $this->_setupDbAdapters();

            $formController = $this->_getFormController($sysScreen, $type);

            if (is_callable(array($formController, $method))) {
                $response['result'] = call_user_func(array($formController, $method), $this->getRequest()->getParam('params'));
            } else {
                throw new Exception('Method not found.');
            }

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }

    protected function _setupShortcuts()
    {
    }

    protected function _getMenuId()
    {
        return '';
    }

}
