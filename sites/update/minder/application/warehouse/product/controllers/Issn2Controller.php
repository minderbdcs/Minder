<?php
  
class Warehouse_Issn2Controller extends Minder_Controller_Action
{
    const ISSN_MODEL_NAME = 'ISSN';
    const ISSN_DATASET_NAMESPACE = 'WAREHOUSE-ISSN';
    
    public function indexAction() {
        $this->view->pageTitle = 'ISSN';
        
        $this->session->action      = 'index';
        $this->session->controller  = 'issn2';
        $this->session->module      = 'warehouse';
        $this->session->savedParams = array();

        try {
            
            if (!$this->session->defautIssnPaginatorSet) {
                $pagination           = $this->restorePagination(self::ISSN_DATASET_NAMESPACE);
                $pagination['showBy'] = 15;
                $this->savePagination(self::ISSN_DATASET_NAMESPACE, $pagination);
                $this->session->defautIssnPaginatorSet = true;
            }

            $this->view->issnSsName     = $this->view->searchFormSsName = self::ISSN_MODEL_NAME;
            $this->view->issnNamespace  = self::ISSN_DATASET_NAMESPACE;
            
            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ISSN_MODEL_NAME);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            /**
            * @var Minder_SysScreen_Model_PurchaseOrder
            */
            $issnModel   = $screenBuilder->buildSysScreenModel(self::ISSN_MODEL_NAME, new Minder_SysScreen_Model_Issn());
            $issnModel->setConditions($issnModel->makeConditionsFromSearch($searchFields));
        
            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $issnModel, true, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);
                    
                $this->view->hasErrors = true;
            }
        
            $this->view->issnJsSearchResults = $this->view->jsSearchResult(
                                                    self::ISSN_MODEL_NAME, 
                                                    self::ISSN_DATASET_NAMESPACE, 
                                                    array('sysScreenCaption' => 'ISSN LIST', 'usePagination'    => true)
            );
            $this->view->issnJsSearchResultsDataset = $this->view->sysScreens[self::ISSN_DATASET_NAMESPACE];
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }
        
        return;
    }

    public function getDatasetAction() {
        $datasets = array(
            self::ISSN_DATASET_NAMESPACE   => self::ISSN_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector   = $this->_helper->getHelper('RowSelector');
        $this->view->sysScreens = array();
        
        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);
            if (isset($sysScreens[$namespace])) {
                $pagination = $this->fillPagination($pagination, $sysScreens[$namespace]);
            }
        
            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);

    		/*me updated for date conversion for ISSN display last_update_date*/
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
 	

            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelname, $this->view->dataset, $this->view->selectedRows, $this->view->paginator);
        
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
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;
        
        $this->_helper->viewRenderer->setNoRender(true);
        /**
        * @var Minder_Controller_Action_Helper_RowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');
        
        try{
            
            $sysScreens = $this->getRequest()->getParam('sysScreens', array());
            $pagination = $this->restorePagination(self::ISSN_DATASET_NAMESPACE);

            if (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator'])) {
                $pagination['selectedPage']  = (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectedPage']))  ? $sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectedPage']  : $pagination['selectedPage'];
                $pagination['showBy']        = (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['showBy']))        ? $sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['showBy']        : $pagination['showBy'];
                $pagination['selectionMode'] = (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectionMode'])) ? $sysScreens[self::ISSN_DATASET_NAMESPACE]['paginator']['selectionMode'] : $pagination['selectionMode'];
            }
    
            if (isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['rowId']) && isset($sysScreens[self::ISSN_DATASET_NAMESPACE]['state'])) {
                $rowSelector->setSelectionMode($pagination['selectionMode'], self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                $rowSelector->setRowSelection($sysScreens[self::ISSN_DATASET_NAMESPACE]['rowId'], $sysScreens[self::ISSN_DATASET_NAMESPACE]['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            }
            
            $result->selectedRowsTotal = $rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            if ($result->selectedRowsTotal > 0) {
                $result->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, self::ISSN_DATASET_NAMESPACE);
                $result->selectedRowsOnPage = count($result->selectedRows);
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
            * @var Minder_Controller_Action_Helper_SearchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ISSN_MODEL_NAME);
            
            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            

            $session = new Zend_Session_Namespace();
            $tz_from=$session->BrowserTimeZone;


            $array_new = $searchFields;

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
                        if($key1=='SEARCH_VALUE' && $val1!=''){
                            if(DateTime::createFromFormat('Y-m-d H:i:s', $val1)!== FALSE  || DateTime::createFromFormat('Y-m-d',$val1) !== FALSE) {
                                $datetimet=$val1;
                                $tz_tot='UTC';
                                $format='Y-m-d h:i:s';
                                $dtt=new DateTime($datetimet,new DateTimeZone($tz_from));
                                $dtt->setTimeZone(new DateTimeZone($tz_tot));
                                $searchFields[$key][$key1]=$dtt->format($format);
                            }
                        }
                    }
                }
            }
            


            /**
            * @var Minder_Controller_Action_Helper_RowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model
            */
            $rowsModel    = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        return $this->_forward('get-dataset');
    }
    
    public function printLabelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new stdClass();
        $response->errors   = array();
        $response->warnings = array();
        $response->messages = array();
        
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector       = $this->_helper->getHelper('RowSelector');
        $selectedIssnCount = $rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        
        if ($selectedIssnCount < 1) {
            $this->view->warnings[] = 'No ISSNs selected. Please, select one.';
            return $this->_forward('get-dataset');
        }
        
        /**
        * @var Minder_SysScreen_Model_Issn $issnModel
        */
        $issnModel = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        
        $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        $result = $issnModel->printLabels($this->minder->getPrinter());
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $result));
        $response->errors   = array_merge($response->errors, $result->errors);
        $response->messages = array_merge($response->messages, $result->messages);
        
        echo json_encode($response);
    }

    public function printLabelDirectAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new stdClass();
        $response->errors   = array();
        $response->warnings = array();
        $response->messages = array();

        $ssnIdToPrint = $this->getRequest()->getParam('id', null);
        if (is_null($ssnIdToPrint)) {
            $response->errors[] = 'Nothing to print.';
            echo json_encode($response);
            return;
        }

        $screenBuilder = new Minder_SysScreen_Builder();
        $issnModel = $screenBuilder->buildSysScreenModel(self::ISSN_MODEL_NAME, new Minder_SysScreen_Model_Issn());
        $issnModel->addConditions(array('ISSN.SSN_ID = ?' => array($ssnIdToPrint)));

        if (count($issnModel) < 1) {
            $response->errors[] = 'Provided Id not found.';
            echo json_encode($response);
            return;
        }

        $result = $issnModel->printLabels($this->minder->getPrinter());
        $response->errors   = array_merge($response->errors, $result->errors);
        $response->messages = array_merge($response->messages, $result->messages);

        echo json_encode($response);
    }
    
    public function repackAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector       = $this->_helper->getHelper('RowSelector');
        /**
        * @var Minder_SysScreen_Model_Issn $issnModel
        */
        $issnModel = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        
        if ($rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController) > 0) {
            $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        } else {
            $issnModel->addConditions(array('1 = 2' => array()));
        }
        $ssns = $issnModel->selectSsn(0, count($issnModel));
        
        /**
        * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
        */
        $searchKeeper  = $this->_helper->searchKeeper;
        $screenBuilder = new Minder_SysScreen_Builder();
        list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::ISSN_MODEL_NAME);
        
        $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
        $searchFields = $searchKeeper->getSearch($searchFields, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $selectConditions = $issnModel->makeConditionsFromSearch($searchFields);

        $clause = array();
        foreach ($selectConditions as $conditionStr => $conditionParams) {
            $clause[$conditionStr] = (is_array($conditionParams)) ? current($conditionParams) : $conditionParams;
        }
        
        $this->session->conditions['re']['pack']['original'] = array_combine($ssns, $ssns);
        $this->session->conditions['re']['pack']['clause']   = $clause;

        return $this->_forward('pack-init', 're', 'warehouse');
        
    }
    
    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', self::ISSN_DATASET_NAMESPACE);
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);
        
        return $this->_forward('report', 'service', 'default');
        
    }

    public function deleteIssnAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->view->errors = array();
        $this->view->warnings = array();
        $this->view->messages = array();

        if (!Minder2_Environment::getInstance()->getCurrentUser()->isAdmin()) {
            $this->view->errors[] = 'Not allowed.';
            return $this->_forward('get-dataset');
        }

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector       = $this->_helper->getHelper('RowSelector');
        $selectedIssnCount = $rowSelector->getSelectedCount(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        if ($selectedIssnCount < 1) {
            $this->view->warnings[] = 'No ISSNs selected. Please, select one.';
            return $this->_forward('get-dataset');
        }

        /**
         * @var Minder_SysScreen_Model_Issn $issnModel
         */
        $issnModel = $rowSelector->getModel(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        $issnModel->addConditions($rowSelector->getSelectConditions(self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        $response = $issnModel->deleteIssn();

        $rowSelector->setRowSelection('select_complete', 'false', null, null, $issnModel, false, self::ISSN_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

        $this->view->errors = array_merge($this->view->errors, $response->errors);
        $this->view->warnings = array_merge($this->view->warnings, $response->warnings);
        $this->view->messages = array_merge($this->view->messages, $response->messages);


        return $this->_forward('get-dataset');
    }
    
    /*protected function _setupShortcuts()
    {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('warehouse')->buildMinderMenuArray();

        return $this;
    }*/
}
