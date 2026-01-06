<?php

class Despatches_AustpostManifestController extends Minder_Controller_Action {

    const MANIFEST_MODEL_NAME = 'AUSTPOSTMANIFEST';
    const MANIFEST_DATASET_NAMESPACE = 'AUSTPOSTMANIFEST-DESPATCHES';

    public function init()
    {
        $this->view->pageTitle = 'Manifest';

        return parent::init();
    }

    public function indexAction() {
        try {
            $this->view->manifestSsName     = $this->view->searchFormSsName = self::MANIFEST_MODEL_NAME;
            $this->view->manifestNamespace  = self::MANIFEST_DATASET_NAMESPACE;
            $this->view->carriersList       = array_merge(array('' => ''), Minder_ManifestRoutines::getSupportedCarriersList());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->carriersList));
            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MANIFEST_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            /**
            * @var Minder_SysScreen_Model_AustpostManifest $manifestModel
            */
            $manifestModel   = $screenBuilder->buildSysScreenModel(self::MANIFEST_MODEL_NAME, new Minder_SysScreen_Model_AustpostManifest());
            $manifestModel->setConditions($manifestModel->makeConditionsFromSearch($searchFields));

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $manifestModel, true, self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->manifestJsSearchResults = $this->view->jsSearchResult(
                                                    self::MANIFEST_MODEL_NAME,
                                                    self::MANIFEST_DATASET_NAMESPACE,
                                                    array('sysScreenCaption' => 'MANIFESTS LIST', 'usePagination'    => true)
            );
            $this->view->manifestJsSearchResultsDataset = $this->view->sysScreens[self::MANIFEST_DATASET_NAMESPACE];
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }

        return;
    }


    public function getDatasetAction() {
        $datasets = array(
            self::MANIFEST_DATASET_NAMESPACE   => self::MANIFEST_MODEL_NAME
        );
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($datasets as $namespace => $modelname) {
            $pagination = $this->restorePagination($namespace);
            if (isset($sysScreens[$namespace])) {
                $pagination = $this->fillPagination($pagination, $sysScreens[$namespace]);
            }

            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'], $pagination);
            

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
        $result = new Minder_JSResponse();
        $result->namespace    = '';
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try{
            $sysScreens = $this->getRequest()->getParam('sysScreens', array());

            foreach ($sysScreens as $namespace => $sysScreen) {
                $result->namespace = $namespace;
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

                $result->selectedRowsTotal = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($result->selectedRowsTotal > 0) {
                    $result->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
                    $result->selectedRowsOnPage = count($result->selectedRows);
                }
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
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MANIFEST_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            


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

                                //$searchFields[$key][$key1] = $this->minder->getFormatedDateToDb($val1, "Y-m-d H:i:s");
                                //die($searchFields[$key][$key1]." ***");

                            }

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
            $rowsModel    = $rowSelector->getModel(self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    public function showManifestPdfAction() {
        $this->_helper->getHelper('ViewRenderer')->setNoRender(true);

        try {
            $manifestPdfId = $this->getRequest()->getParam('pdfId');

            if (empty($manifestPdfId))
                throw new Exception('No Manifest PDF ID provided.');

            $manifestRoutines = new Minder_ManifestRoutines();

            $this->getResponse()->setHeader('Content-Type', 'application/pdf');
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $manifestRoutines->getStoredPdfImageName($manifestPdfId) . '"');
            echo $manifestRoutines->getStoredPdfImage($manifestPdfId);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function reRunManifestBuildAction() {
        $this->_helper->getHelper('ViewRenderer')->setNoRender(true);
        $result = new Minder_JSResponse();
        $result->generatedPdfId = array();

        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->rowSelector;

            $selectedManifests = $rowSelector->getSelectedCount(self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if ($selectedManifests < 1) {
                $result->errors[] = 'No Manifests selected. Please, select one.';
                echo json_encode($result);
                return;
            }

            /**
             * @var Minder_SysScreen_Model_AustpostManifest $manifestModel
             */
            $manifestModel          = $rowSelector->getModel(self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $manifestModel->addConditions($rowSelector->getSelectConditions(self::MANIFEST_DATASET_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $result->generatedPdfId = $manifestModel->reRunManifestBuild();
            $result->messages[]     = 'Completed.';
        }catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function runManifestBuildAction() {
        $this->_helper->getHelper('ViewRenderer')->setNoRender(true);
        $result = new Minder_JSResponse();
        $result->generatedPdfId = array();

        try {
            $carrierId = $this->getRequest()->getParam('carrierId');

            if (empty($carrierId)) throw new Minder_Exception('No CARRIER selected.');

            $manifestRoutines         = new Minder_ManifestRoutines();
            $result->generatedPdfId[] = $manifestRoutines->runManifestBuild($carrierId);
            $result->messages[]       = 'Completed.';
        }catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode($result);
    }

    /**
     * Setup menu shortcuts.
     * @return Minder_Controller_Action Provides a fluent interface.
     */
    /*protected function _setupShortcuts()
    {
        $shortcuts = array();

        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
            $shortcuts['Assembly']                =   $this->view->url(array('controller' => 'trolley', 'action' => 'index'), null, true);
        } else {
            $shortcuts['Awaiting Checking']     =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-checking', 'module' => 'despatches'), null, true);
        }

        $shortcuts['Consignment Exit']          =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
        $shortcuts['Scan Exit']                 =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);
        $shortcuts['<Austpost Manifest>']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
        $shortcuts['View Despatched Orders']      =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
        $shortcuts['Despatch Activity Reports']   =   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);
        $shortcuts['Person Details']              =   array(
            'PERSON'                              =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
        );

        $this->view->shortcuts = $shortcuts;
    }*/
}
