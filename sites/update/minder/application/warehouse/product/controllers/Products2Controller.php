<?php

class Warehouse_Products2Controller extends Minder_Controller_Action {

    const PRODUCTS_MODEL_NAME = 'PRODUCTS';
    const PRODUCTS_NAMESPACE  = 'WAREHOUSE-PRODUCTS';

    protected $_datasets = array(self::PRODUCTS_NAMESPACE => self::PRODUCTS_MODEL_NAME);

    public function init()
    {
        parent::init();
        $this->view->pageTitle = 'Search Product';
    }

    public function indexAction() {
        try {
            $this->view->productsSsName     = $this->view->searchFormSsName = self::PRODUCTS_MODEL_NAME;
            $this->view->productsNamespace = self::PRODUCTS_NAMESPACE;

            $screenBuilder = new Minder_SysScreen_Builder();
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;

            $screenDescription = $screenBuilder->getSysScreenDescription(self::PRODUCTS_MODEL_NAME);
            $hasSearch = $searchKeeper->hasSearch(self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->viewOnOpen = $hasSearch || (strtoupper($screenDescription['SS_VIEW_ON_OPENING']) !== 'F');

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::PRODUCTS_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->getSearch($searchFields, self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->searchFields = $searchFields;

            /**
            * @var Minder_SysScreen_Model_Products $productsModel
            */
            $productsModel   = $screenBuilder->buildSysScreenModel(self::PRODUCTS_MODEL_NAME, new Minder_SysScreen_Model_Products());
            $productsModel->setConditions($productsModel->makeConditionsFromSearch($searchFields));

            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $productsModel, true, self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if ($this->view->viewOnOpen) {
                $this->fillProductStatistic(new Minder_ProductStatisticType(true, false));
                $this->getDatasetAction();
            }

            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->productsJsSearchResults = $this->view->jsSearchResult(
                                                    self::PRODUCTS_MODEL_NAME,
                                                    self::PRODUCTS_NAMESPACE,
                                                    array('sysScreenCaption' => 'PRODUCTS LIST', 'usePagination'    => true)
            );
            if ($this->view->viewOnOpen) {
                $this->view->productsJsSearchResultsDataset = $this->view->sysScreens[self::PRODUCTS_NAMESPACE];
            }
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
            $this->view->hasErrors = true;
        }
    }

    /**
    * @param Minder_ProductStatisticType $productStatisticType
    */
    protected function fillProductStatistic($productStatisticType = null) {
        $pagination           = $this->restorePagination(self::PRODUCTS_NAMESPACE);

        if (!is_null($productStatisticType)) {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector   = $this->_helper->getHelper('RowSelector');
            /**
            * @var Minder_SysScreen_Model_ProductSearch $productModel
            */
            $productModel = $rowSelector->getModel(self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            if ($productStatisticType->all) {
                $availableAndOnHandQty = $productModel->getTotalAndOnHandQty();

                $pagination['availableQty'] = $availableAndOnHandQty->totalQty;
                $pagination['onHandQty']   = $availableAndOnHandQty->onHandQty;
            }
Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $pagination));

            if ($productStatisticType->selected) {
                if ($rowSelector->getSelectedCount(self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController) < 1)
                    $productModel->addConditions(array('1 = 2' => array()));
                else
                    $productModel->addConditions($rowSelector->getSelectConditions(self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));

                $availableAndOnHandQty = $productModel->getTotalAndOnHandQty();

                $pagination['selectedAvailableQty'] = $availableAndOnHandQty->totalQty;
                $pagination['selectedOnHandQty']   = $availableAndOnHandQty->onHandQty;
            }
        }
        $this->savePagination(self::PRODUCTS_NAMESPACE, $pagination);
    }

    public function getDatasetAction() {
        $datasets = array(
            self::PRODUCTS_NAMESPACE   => self::PRODUCTS_MODEL_NAME
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
            $pagination = array_merge($pagination, $this->view->paginator);
           
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



            $this->savePagination($namespace, $pagination);
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($modelname, $this->view->dataset, $this->view->selectedRows, $pagination);

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function selectRowAction() {
        $result = new Minder_JSResponse();
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
            $pagination = $this->restorePagination(self::PRODUCTS_NAMESPACE);

            if (isset($sysScreens[self::PRODUCTS_NAMESPACE]['paginator'])) {
                $pagination['selectedPage']  = (isset($sysScreens[self::PRODUCTS_NAMESPACE]['paginator']['selectedPage']))  ? $sysScreens[self::PRODUCTS_NAMESPACE]['paginator']['selectedPage']  : $pagination['selectedPage'];
                $pagination['showBy']        = (isset($sysScreens[self::PRODUCTS_NAMESPACE]['paginator']['showBy']))        ? $sysScreens[self::PRODUCTS_NAMESPACE]['paginator']['showBy']        : $pagination['showBy'];
                $pagination['selectionMode'] = (isset($sysScreens[self::PRODUCTS_NAMESPACE]['paginator']['selectionMode'])) ? $sysScreens[self::PRODUCTS_NAMESPACE]['paginator']['selectionMode'] : $pagination['selectionMode'];
            }

            if (isset($sysScreens[self::PRODUCTS_NAMESPACE]['rowId']) && isset($sysScreens[self::PRODUCTS_NAMESPACE]['state'])) {
                $rowSelector->setSelectionMode($pagination['selectionMode'], self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
                $rowSelector->setRowSelection($sysScreens[self::PRODUCTS_NAMESPACE]['rowId'], $sysScreens[self::PRODUCTS_NAMESPACE]['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            }

            $result->selectedRowsTotal = $rowSelector->getSelectedCount(self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            if ($result->selectedRowsTotal > 0) {
                $result->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, self::PRODUCTS_NAMESPACE);
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
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::PRODUCTS_MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields, self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);



	$session = new Zend_Session_Namespace();
        $tz_from=$session->BrowserTimeZone;


	$array_new=$searchFields;

	foreach ($array_new as $key => $value) {
            foreach($value as $key1=>$val1){

                if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') {
unset($array_new[$key]); }


            }

}



            foreach($array_new as $key=>$val){

                                foreach($val as $key1=>$val1){

                                                    if($key1=='SEARCH_VALUE'&& $val1!=''){


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









            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model $rowsModel
            */
            $rowsModel    = $rowSelector->getModel(self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $rowsModel, true, self::PRODUCTS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->fillProductStatistic(new Minder_ProductStatisticType(true, false));
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

    /**
     * Setup menu shortcuts.
     * @return Minder_Controller_Action Provides a fluent interface.
     */
    /*protected function _setupShortcuts()
    {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('warehouse')->buildMinderMenuArray();

        return $this;
    }*/
}
