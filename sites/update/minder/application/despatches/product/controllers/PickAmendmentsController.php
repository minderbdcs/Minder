<?php

class Despatches_PickAmendmentsController extends Minder_Controller_Action {
    const LINES_MODEL = 'AMENDMENT_LINES';
    const LINES_NAMESPACE = 'AMENDMENT_LINES';


    public function indexAction() {
        try {
            $this->view->linesModel = self::LINES_MODEL;
            $this->view->linesNamespace = self::LINES_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper  = $this->_helper->getHelper('SearchKeeper');
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            list($searchFields, , , $giSearchFields) = $screenBuilder->buildSysScreenSearchFields(self::LINES_MODEL);

            $searchFields = array_merge($searchFields, $giSearchFields);
            $this->view->searchFields = $searchFields = $searchKeeper->getSearch($searchFields, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $linesModel = $screenBuilder->buildSysScreenModel(self::LINES_MODEL, new Minder_SysScreen_Model_AmendmentLines());
            if (!empty($searchFields))
                $linesModel->addConditions($linesModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens', array(
                self::LINES_NAMESPACE => array(),
            ));
            $this->getDatasetAction();

            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->linesJsSearchResults = $this->view->jsSearchResult(
                self::LINES_MODEL,
                self::LINES_NAMESPACE,
                array('sysScreenCaption' => 'ISSN List', 'usePagination'    => true)
            );
            $this->view->linesJsSearchResultsDataset = $this->view->sysScreens[self::LINES_NAMESPACE];
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            $this->view->hasErrors = true;
        }
    }

    public function getDatasetAction() {
        $datasets = $this->_getModelMap();
        $sysScreens = $this->getRequest()->getParam('sysScreens', array());
        $this->view->sysScreens = array();

        foreach ($sysScreens as $namespace => $sysScreenPagination) {
            if (!isset($datasets[$namespace]))
                continue;

            $pagination = $this->fillPagination($this->restorePagination($namespace), $sysScreenPagination);


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





            $this->ajaxBuildDataset($namespace, $pagination['selectedPage'], $pagination['showBy'],$pagination);
            $this->savePagination($namespace, $this->view->paginator);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($datasets[$namespace], $this->view->dataset, $this->view->selectedRows, $this->view->paginator);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new Minder_JSResponse();
        $result->sysScreens = array();
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;

        $this->_helper->viewRenderer->setNoRender(true);
        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector = $this->_helper->getHelper('RowSelector');

        try{
            foreach ($this->getRequest()->getParam('sysScreens', array()) as $namespace => $sysScreen) {
                $tmpSysScreen = new stdClass();
                $tmpSysScreen->selectedRows = array();
                $tmpSysScreen->selectedRowsTotal = 0;
                $tmpSysScreen->selectedRowsOnPage = 0;

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

                $tmpSysScreen->selectedRowsTotal = $rowSelector->getSelectedCount($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                if ($tmpSysScreen->selectedRowsTotal > 0) {
                    $tmpSysScreen->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
                    $tmpSysScreen->selectedRowsOnPage = count($tmpSysScreen->selectedRows);

                }

                $result->sysScreens[$namespace] = $tmpSysScreen;
            }
        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    public function searchAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        $namespace = $this->getRequest()->getParam('namespace');
        $modelMap = $this->_getModelMap();
        if (!isset($modelMap[$namespace])) return $this->_forward('get-dataset');

        try{
            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields($modelMap[$namespace]);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);


	    
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



            if (!empty($searchFields)) {
                /**
                 * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
                 */
                $rowSelector = $this->_helper->rowSelector;
                /**
                 * @var Minder_SysScreen_Model $rowsModel
                 */
                $rowsModel    = $rowSelector->getModel($namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
                $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

                $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, $namespace, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    public function amendItemsAction() {
        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();
        $this->view->warnings = isset($this->view->warnings) ? $this->view->warnings : array();

        try{
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector = $this->_helper->rowSelector;
            /**
             * @var Minder_SysScreen_Model_AmendmentLines $rowsModel
             */
            $rowsModel    = $rowSelector->getModel(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if (!$rowSelector->hasSelected(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController)) {
                $this->view->errors[] = 'No rows selected. Please select one.';
                return $this->_forward('get-dataset');
            }

            $rowsModel->addConditions($rowSelector->getSelectConditions(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
            $rowsModel->amendItems();
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    private function _getModelMap() {
        $datasets = array(
            self::LINES_NAMESPACE   => self::LINES_MODEL,
        );
        return $datasets;
    }

}
