<?php
  
class Despatches_DespatchExitController extends Minder_Controller_Action
{
    const EXIT_CARRIER_MODEL = 'AWAITING_EXIT_CARRIER';
    const EXIT_CARRIER_NAMESPACE = 'AC-AWAITING_EXIT_CARRIER';

    public static $rowModelName = 'SCANNING_EXIT';
    public static $rownamespace = 'DESPATCH_EXIT-SCANNING_EXIT';
    
    public function indexAction() {
        
        try {
            $this->view->ssName    = self::$rowModelName;
            $this->view->namespace = self::$rownamespace;
            
            $this->view->jsScreenDefinition = array();
            $this->view->jsScreenDataset    = array();
            $this->view->screenButtonsParam = array();
            $this->view->carrierIdParam     = array();
            $this->view->searchForm         = '';
            
            $screenBuilder = new Minder_SysScreen_Builder();
            $rowsModel     = $screenBuilder->buildSysScreenModel(self::$rowModelName, new Minder_SysScreen_Model_ScanningExit());
        
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setSelectionMode('one', self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->getDatasetAction();
            
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);
                    
                $this->view->hasErrors = true;
            }
            
            $this->view->searchForm         = $this->view->sysScreenSearchForm2(self::$rowModelName, array('custom-parse-event-handlers' => true));
            
            $this->view->jsScreenDefinition = $this->view->jsSearchResult(
                                                        self::$rowModelName, 
                                                        self::$rownamespace, 
                                                        array(
                                                            'sysScreenCaption' => 'DESPATCH EXIT',
                                                            'usePagination'    => true
                                                        )
            );

            $this->view->jsScreenDataset    = $this->view->jsSearchResultDataset(
                                                        self::$rowModelName, 
                                                        $this->view->sysScreens[self::$rownamespace]['rows'], 
                                                        $this->view->sysScreens[self::$rownamespace]['selectedRows'], 
                                                        $this->view->sysScreens[self::$rownamespace]['paginator']
            );
            
            $this->view->screenButtonsParam = $this->view->SymbologyPrefixDescriptor('SCREEN_BUTTON');
            $this->view->carrierIdParam      = $this->view->SymbologyPrefixDescriptor('CARRIER_ID');

        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        $this->view->knownCarriers  = array();

        try {
            $this->view->knownCarriers = array_values($this->minder->getShipViaList());
        } catch (Exception $e) {
            $this->view->warnings[] = $e->getMessage();
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
            $this->view->warnings[] = $e->getMessage() . ' Exit Carrier dialog will not work.';
        }

        try {
            $this->view->connoteBarcodeDescriptions = array();
            $connoteBarcodes = $this->minder->getConnoteBarcodeDataIds();
            foreach ($connoteBarcodes as $dataId) {
                try {
                    $this->view->connoteBarcodeDescriptions[] = $this->view->SymbologyPrefixDescriptor($dataId['DATA_ID']);
                } catch (Exception $e) {
                    $this->view->warnings[] = 'CONNOTE_PARAM_ID = "' . $dataId['DATA_ID'] .'" is defined in CARRIER table. But was not found in PARAM table. Check system setup.';
                }
            }
        } catch (Exception $e) {
            $this->view->warnings[] = 'Cannot get CONNOTE barcode label descriptions for CARRIERS: ' . $e->getMessage() . ' Check system setup.';
        }
        return;
    }

    public function getExitCarrierDatasetAction() {
        $this->view->sysScreens = $this->_buildDatatset(array(static::EXIT_CARRIER_MODEL => static::EXIT_CARRIER_NAMESPACE));
        $this->_viewRenderer()->setNoRender();
        echo $this->_datasetToJson($this->view);
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

    public function getDatasetAction() {
        $pagination = $this->restorePagination(self::$rownamespace);
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        
        if (isset($sysScreens[self::$rownamespace])) {
            $pagination = $this->fillPagination($pagination, $sysScreens[self::$rownamespace]);
        }
        
        $this->ajaxBuildDataset(self::$rownamespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
        
        if($this->minder->isNewDateCalculation() == false){
            $session = new Zend_Session_Namespace();
            $tz_to=$session->BrowserTimeZone;

            $key_array=array_keys($this->view->dataset);

            foreach($this->view->dataset as $key=>$val){

                foreach($val as $key1=>$val1){

                    if(DateTime::createFromFormat('Y-m-d H:i:s', $val1)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val1)!==FALSE) {

                        $dt = new DateTime($val1, new DateTimeZone('UTC'));
                        $tz = new DateTimeZone($tz_to); 
                        $dt->setTimezone($tz);
                        $val1=$dt->format('Y-m-d H:i:s');
                        $this->view->dataset[$key][$key1]=$val1;

                    } 

                }

            }
        }

        $this->savePagination(self::$rownamespace, $this->view->paginator);
        
        $this->view->sysScreens = array();
        $this->view->sysScreens[self::$rownamespace] = array(
            'paginator'    => $this->view->paginator,
            'rows'         => $this->view->dataset,
            'selectedRows' => $this->view->selectedRows,
            'ssName'       => self::$rowModelName
        );
        
        unset($this->view->paginator);
        unset($this->view->dataset);
        unset($this->view->selectedRows);
    }
    
    public function selectRowAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        
        try{
            $pagination = $this->restorePagination(self::$rownamespace);
            $sysScreens = $this->getRequest()->getParam('sysScreens', array());
            
            if (isset($sysScreens[self::$rownamespace])) {
                if (isset($sysScreens[self::$rownamespace]['paginator'])) {
                    $pagination['selectedPage']  = (isset($sysScreens[self::$rownamespace]['paginator']['selectedPage']))  ? $sysScreens[self::$rownamespace]['paginator']['selectedPage']  : $pagination['selectedPage'];
                    $pagination['showBy']        = (isset($sysScreens[self::$rownamespace]['paginator']['showBy']))        ? $sysScreens[self::$rownamespace]['paginator']['showBy']        : $pagination['showBy'];
                    $pagination['selectionMode'] = (isset($sysScreens[self::$rownamespace]['paginator']['selectionMode'])) ? $sysScreens[self::$rownamespace]['paginator']['selectionMode'] : $pagination['selectionMode'];
                }
    
                if (isset($sysScreens[self::$rownamespace]['rowId']) && isset($sysScreens[self::$rownamespace]['state'])) {
                    /**
                    * @var Minder_Controller_Action_Helper_RowSelector
                    */
                    $rowSelector = $this->_helper->getHelper('RowSelector');
                    
                    $rowSelector->setSelectionMode($pagination['selectionMode'], self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                    $rowSelector->setRowSelection($sysScreens[self::$rownamespace]['rowId'], $sysScreens[self::$rownamespace]['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                }
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        
        try{
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::$rowModelName);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            

            $session = new Zend_Session_Namespace();
            $tz_from=$session->BrowserTimeZone;


            $array_new=$searchFields;

            foreach ($array_new as $key => $value) {
                foreach($value as $key1=>$val1){

                    if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') {
                                     unset($array_new[$key]);
                     }


                }

            }


            if($this->minder->isNewDateCalculation() == false){
                foreach($array_new as $key=>$val){

                        foreach($val as $key1=>$val1){

                             if($key1=='SEARCH_VALUE'&& $val1!=''){

                                if(DateTime::createFromFormat('Y-m-d H:i:s', $val1)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val1)!==FALSE) {


                                $datetimet=$val1;
                                $tz_tot='UTC';
                                $format='Y-m-d h:i:s';

                                $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                                $dtt->setTimeZone(new DateTimeZone($tz_tot));

                                $searchFields[$key][$key1]=$dtt->format($format);

                                 }

                             }


                        }

                }
            }

            /**
            * @var Minder_SysScreen_Model
            */
            $rowsModel    = $rowSelector->getModel(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->addConditions($rowsModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setSelectionMode('one', self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowSelector->setRowSelection('select_complete', 'false', 0, 0, null, false, self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);

            $foundRows    = $rowsModel->getItems(0, count($rowsModel), true);
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $foundRows));

            foreach ($foundRows as $rowId => $row) {
                $rowSelector->setRowSelection($rowId, 'true', 0, 0, null, false, self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function acceptCancelAction() {
        $this->view->errors   = isset($this->view->errors)   ? $this->view->errors   : array();
        $this->view->warnings = isset($this->view->warnings) ? $this->view->warnings : array();
        $this->view->messages = isset($this->view->messages) ? $this->view->messages : array();
        
        $action = $this->getRequest()->getParam('despatchAction');
        
        try{
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            if ($rowSelector->getSelectedCount(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController) < 1) {
                $this->view->warnings[] = 'Now rows selected. Please select one.';
                return $this->_forward('get-dataset');
            }
            
            /**
             * @var Minder_SysScreen_Model_ScanningExit $rowsModel
            */
            $rowsModel    = $rowSelector->getModel(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->addConditions($rowSelector->getSelectConditions(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController));
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $action));
            switch (strtolower($action)) {
                case 'accept':
                    $this->view->messages = array_merge($this->view->messages, $rowsModel->acceptDespatch());
                    break;
                case 'cancel':
                    $this->view->messages = array_merge($this->view->messages, $rowsModel->cancelDespatch());
                    break;
            }
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function reprintLabelsAction() {
        $this->view->errors   = isset($this->view->errors)   ? $this->view->errors   : array();
        $this->view->warnings = isset($this->view->warnings) ? $this->view->warnings : array();
        $this->view->messages = isset($this->view->messages) ? $this->view->messages : array();

        try{
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            if ($rowSelector->getSelectedCount(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController) < 1) {
                $this->view->warnings[] = 'Now rows selected. Please select one.';
                return $this->_forward('get-dataset');
            }

            /**
            * @var Minder_SysScreen_Model_ScanningExit $rowsModel
            */
            $rowsModel    = $rowSelector->getModel(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->addConditions($rowSelector->getSelectConditions(self::$rownamespace, self::$defaultSelectionAction, self::$defaultSelectionController));

            $printResult = $rowsModel->reprintLabels($this->minder->getPrinter());
            $this->view->messages = array_merge($this->view->messages, $printResult->messages);
            $this->view->warnings = array_merge($this->view->warnings, $printResult->warnings);
            $this->view->errors   = array_merge($this->view->errors, $printResult->errors);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
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

    /*protected function _setupShortcuts()
    {
        $shortcuts = array();
        
        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
            $shortcuts['Assembly']                =   $this->view->url(array('controller' => 'trolley', 'action' => 'index'), null, true);
        } else {
            $shortcuts['Awaiting Checking']     =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-checking', 'module' => 'despatches'), null, true);   
        }
        
        $shortcuts['OTC-Issues/Returns']          =   $this->view->url(array('action' => 'index', 'controller' => 'otc'), null, true);
        $shortcuts['Consignment Exit']            =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
        $shortcuts['<Scan Exit>']                 =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);   
        $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
        $shortcuts['View Despatched Orders']      =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
        $shortcuts['Despatch Activity Reports']   =   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);   
        $shortcuts['Person Details']              =   array(
            'PERSON'                              =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
        );
        
        $this->view->shortcuts = $shortcuts;     
    }*/
}
