<?php

class Picking_ServiceController extends Minder_Controller_Action_Picking 
{
    const SCREEN_NAME = 'ALLOCATELIMITS';

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
        $response->orderLinesSelected = 0;
        $response->productsSelected   = 0;
        $response->issnsSelected      = 0;
        $response->nonOPStatus        = array(); //use to show warnings when user select PICK_ITEMS with non OP status
        
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

            if ($response->selected > 0) {
                
                $linesModel = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
                $linesModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
                
                if (is_callable(array($linesModel, 'getOrderLinesCount'))) {
                    $response->orderLinesSelected = $linesModel->getOrderLinesCount();
                }
                
                if (is_callable(array($linesModel, 'getProductCodesCount'))) {
                    $response->productsSelected = $linesModel->getProductCodesCount();
                }
                
                if (is_callable(array($linesModel, 'getISSNsCount'))) {
                    $response->issnsSelected = $linesModel->getISSNsCount();
                }
                
                if (is_callable(array($linesModel, 'selectNonOPStatus'))) {
                    $response->nonOPStatus = $linesModel->selectNonOPStatus(0, count($linesModel));
                }
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
    
    public function allocateAction() {
        $response            = new Minder_JSResponse();
        $response->success   = false;
        $request             = $this->getRequest();
        $method              = $request->getParam('method');
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $selectionAction     = $request->getParam('selection_action');
        $selectionController = $request->getParam('selection_controller');
        $limitsInstanceId    = $request->getParam('limits_instance_id');
        $limitsNamespace     = $request->getParam('limits_namespace', 'default');

        try {
            $hasItemLimit = $this->_hasItemLimit();
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->rowSelector;
            /**
             * @var Minder_Controller_Action_Helper_ScreenDataKeeper $dataKeeper
             */
            $dataKeeper    = $this->_helper->screenDataKeeper;
            $dataKeeper->setInstanceId($limitsInstanceId)->setNamespace($limitsNamespace)->setFieldsPrefix('');
            $orderLimit    = $dataKeeper->getParam('MAX_ORDERS');
            $orderLimit    = (is_null($orderLimit))? 0 : $orderLimit;
            $productLimit  = $dataKeeper->getParam('MAX_PRODUCTS');
            $productLimit  = (is_null($productLimit))? 0 : $productLimit;
            $itemLimit     = $hasItemLimit ? $dataKeeper->getParam('MAX_PICK_ITEMS') : null;
            $userId        = $dataKeeper->getParam('USER_ID');
            $deviceId      = $dataKeeper->getParam('DEVICE_ID');
            $troleyId      = $dataKeeper->getParam('TROLLEY_ID');

            if (empty($userId)) {
                try {
                    $userId = $this->minder->getDevicePerson($deviceId);
                } catch (Exception $e) {
                    $response->warnings[] = 'Error fetching USER_ID from device: ' . $e->getMessage();
                }
            }

            /**
             * @var Minder_SysScreen_Model $dataModel
             */
            $dataModel     = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);

            if ($rowSelector->getSelectedCount($selectionNamespace, $selectionAction, $selectionController) < 1) {
                $dataModel->addConditions(array('1 = 2' => array()));
            } else {
                $dataModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
            }

            $orderAllocator = Minder_OrderAllocator_Abstract::getAllocator($method, $hasItemLimit);
            $allocateResult = $orderAllocator->allocate($dataModel, $userId, $deviceId, $troleyId, $orderLimit, $productLimit, $itemLimit);

            $response->errors   = array_merge($response->errors,   $allocateResult->errors);
            $response->warnings = array_merge($response->warnings, $allocateResult->warnings);
            $response->messages = array_merge($response->messages, $allocateResult->messages);

            $response->success  = (count($response->errors) < 1);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->addError($response->warnings);
        $this->addMessage($response->messages);

        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

    public function runAllocateTransactionAction() {
        $response            = new Minder_JSResponse();
        $response->success   = false;
        $request             = $this->getRequest();
        $selectionNamespace  = $request->getParam('selection_namespace', 'default');
        $limitsInstanceId    = $request->getParam('limits_instance_id');
        $limitsNamespace     = $request->getParam('limits_namespace', 'default');

        try {
            $hasItemLimit = $this->_hasItemLimit();
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->rowSelector;
            /**
             * @var Minder_Controller_Action_Helper_ScreenDataKeeper $dataKeeper
             */
            $dataKeeper    = $this->_helper->screenDataKeeper;
            $dataKeeper->setInstanceId($limitsInstanceId)->setNamespace($limitsNamespace)->setFieldsPrefix('');
            $orderLimit    = $dataKeeper->getParam('MAX_ORDERS');
            $orderLimit    = (is_null($orderLimit))? 0 : $orderLimit;
            $productLimit  = $dataKeeper->getParam('MAX_PRODUCTS');
            $productLimit  = (is_null($productLimit))? 0 : $productLimit;
            $itemLimit     = $hasItemLimit ? $dataKeeper->getParam('MAX_PICK_ITEMS') : null;
            $userId        = $dataKeeper->getParam('USER_ID');
            $deviceId      = $dataKeeper->getParam('DEVICE_ID');
            $troleyId      = $dataKeeper->getParam('TROLLEY_ID');

            /**
             * @var Minder_SysScreen_Model_WaitingPickingLine $dataModel
             */
            $dataModel     = $rowSelector->getModel($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            if ($rowSelector->getSelectedCount($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController) < 1) {
                $dataModel->addConditions(array('1 = 2' => array()));
            } else {
                $dataModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, self::$defaultSelectionAction, self::$defaultSelectionController));
            }

            $orderAllocator = new Minder_OrderAllocator_ProductToDevice();
            $allocateResult = $orderAllocator->allocate($dataModel, $userId, $deviceId, $troleyId, $orderLimit, $productLimit, $itemLimit);

            $response->errors   = array_merge($response->errors,   $allocateResult->errors);
            $response->warnings = array_merge($response->warnings, $allocateResult->warnings);
            $response->messages = array_merge($response->messages, $allocateResult->messages);

            $response->success  = (count($response->errors) < 1);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->addError($response->warnings);
        $this->addMessage($response->messages);

        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }

    protected function _hasItemLimit() {
        $screenBuilder = new Minder_SysScreen_Builder();
        list($fields) = $screenBuilder->buildSysScreenSearchFields(static::SCREEN_NAME);

        foreach ($fields as $fieldDescription) {
            if ($fieldDescription['SSV_NAME'] == 'MAX_PICK_ITEMS') {
                return true;
            }
        }

        return false;
    }
}
