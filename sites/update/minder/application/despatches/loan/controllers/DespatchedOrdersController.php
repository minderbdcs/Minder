<?php

class Despatches_DespatchedOrdersController extends Minder_Controller_Action {

    const ORDER_MODEL_NAME = 'DESPATCHED';
    const ORDER_NAMESPACE  = 'DESPATCHED-ORDERS-DESPATCHED';

    const LINES_MODEL_NAME = 'DESPATCHEDLINES';
    const LINES_NAMESPACE  = 'DESPATCHED-ORDERS-DESPATCHEDLINES';

    const LABELS_MODEL_NAME = 'DESPATCHEDLABELS';
    const LABELS_NAMESPACE  = 'DESPATCHED-ORDERS-DESPATCHEDLABELS';

    public function init()
    {
        parent::init();
        $this->view->pageTitle = 'Despatched Orders';
    }

    public function indexAction() {

        try {
            $this->view->orderSsName = $this->view->orderSearchForm = self::ORDER_MODEL_NAME;
            $this->view->orderNamespace = self::ORDER_NAMESPACE;

            $this->view->linesSsName = $this->view->linesSearchForm = self::LINES_MODEL_NAME;
            $this->view->linesNamespace = self::LINES_NAMESPACE;

            $this->view->labelSsName = $this->view->labelSearchForm = self::LABELS_MODEL_NAME;
            $this->view->labelNamespace = self::LABELS_NAMESPACE;

            $this->view->returnForm = $this->_buildReturnForm();

            /**
             * @var Minder_Controller_Action_Helper_MasterSlave $masterSlaveHelper
             */
            $masterSlaveHelper = $this->_helper->getHelper('MasterSlave');

            $this->view->masterSlaveChain = $masterSlaveHelper->buildMasterSlaveChain($this->_getModelMap());

            $screenBuilder                 = new Minder_SysScreen_Builder();
            $this->view->screenOrder       = $screenBuilder->getScreensOrder($this->_getModelMap());

            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper                  = $this->_helper->searchKeeper;

            $this->view->orderSearchFields = $orderSearchFields = $searchKeeper->getScreenSearchFields(self::ORDER_MODEL_NAME, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->linesSearchFields = $linesSearchFields = $searchKeeper->getScreenSearchFields(self::LINES_MODEL_NAME, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
            $this->view->labelSearchFields = $labelSearchFields = $searchKeeper->getScreenSearchFields(self::LABELS_MODEL_NAME, self::LABELS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector   = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_Despatched $orderModel
             */
            $orderModel = $screenBuilder->buildSysScreenModel(self::ORDER_MODEL_NAME, new Minder_SysScreen_Model_Despatched());
            if (!empty($orderSearchFields))
                $orderModel->addConditions($orderModel->makeConditionsFromSearch($orderSearchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $orderModel, true, self::ORDER_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);

            /**
             * @var Minder_SysScreen_Model $linesModel
             */
            $linesModel = $screenBuilder->buildSysScreenModel(self::LINES_MODEL_NAME);
            if (!empty($linesSearchFields))
                $linesModel->addConditions($linesModel->makeConditionsFromSearch($linesSearchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);


            $labelModel = $screenBuilder->buildSysScreenModel(self::LABELS_MODEL_NAME, new Minder_SysScreen_Model());
            if (!empty($orderSearchFields))
                $labelModel->addConditions($labelModel->makeConditionsFromSearch($labelSearchFields));

            $rowSelector->setRowSelection('select_complete', 'init', null, null, $labelModel, true, self::LABELS_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);


            $masterSlaveHelper->initSubDatasets($this->_getModelMap(), null, array(), self::$defaultSelectionAction, self::$defaultSelectionController);

            $this->getRequest()->setParam('sysScreens',
                array(self::ORDER_NAMESPACE => array(),
                    self::LINES_NAMESPACE => array(),
                    self::LABELS_NAMESPACE => array()));
            $this->getDatasetAction();
            if (count($this->view->errors) > 0) {
                foreach ($this->view->errors as $errorMsg)
                    $this->addError($errorMsg);

                $this->view->hasErrors = true;
            }

            $this->view->orderJsSearchResults = $this->view->jsSearchResult(
                self::ORDER_MODEL_NAME,
                self::ORDER_NAMESPACE,
                array('sysScreenCaption' => 'ORDERS LIST', 'usePagination'    => true)
            );
            $this->view->orderJsSearchResultsDataset = $this->view->sysScreens[self::ORDER_NAMESPACE];

            $this->view->linesJsSearchResults = $this->view->jsSearchResult(
                self::LINES_MODEL_NAME,
                self::LINES_NAMESPACE,
                array('sysScreenCaption' => 'ITEMS LIST', 'usePagination'    => true)
            );
            $this->view->linesJsSearchResultsDataset = $this->view->sysScreens[self::LINES_NAMESPACE];

            $this->view->labelJsSearchResults = $this->view->jsSearchResult(
                self::LABELS_MODEL_NAME,
                self::LABELS_NAMESPACE,
                array('sysScreenCaption' => 'LABELS LIST', 'usePagination'    => true)
            );
            $this->view->labelJsSearchResultsDataset = $this->view->sysScreens[self::LABELS_NAMESPACE];

        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
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
        /**
         * @var Minder_Controller_Action_Helper_MasterSlave $masterSlaveHelper
         */
        $masterSlaveHelper = $this->_helper->getHelper('MasterSlave');

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

                $masterSlaveHelper->initSubDatasets($this->_getModelMap(), $namespace, array(), self::$defaultSelectionAction, self::$defaultSelectionController);
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
        $modelMap = array(self::ORDER_NAMESPACE => self::ORDER_MODEL_NAME, self::LINES_NAMESPACE => self::LINES_MODEL_NAME);
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

            /**
             * @var Minder_Controller_Action_Helper_MasterSlave $masterSlaveHelper
             */
            $masterSlaveHelper = $this->_helper->getHelper('MasterSlave');
            $masterSlaveHelper->initSubDatasets($this->_getModelMap(), $namespace, array(), self::$defaultSelectionAction, self::$defaultSelectionController);
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
        $shortcuts = array();

        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
            $shortcuts['Assembly']                =   $this->view->url(array('controller' => 'trolley', 'action' => 'index'), null, true);
        } else {
            $shortcuts['Awaiting Checking']       =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-checking', 'module' => 'despatches'), null, true);
        }

        $shortcuts['OTC-Issues/Returns']          =   $this->view->url(array('action' => 'index', 'controller' => 'otc'), null, true);
        $shortcuts['Consignment Exit']            =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
        $shortcuts['Scan Exit']                   =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);
        $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
        $shortcuts['<View Despatched Orders>']    =   $this->view->url(array('action' => 'index', 'controller' => 'despatched-orders', 'module' => 'despatches'), null, true);
        $shortcuts['Despatch Activity Reports']   =   $this->view->url(array('action' => 'reports', 'controller' => 'despatch'), null, true);
        $shortcuts['Person Details']              =   array(
            'PERSON'                              =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
        );

        $this->view->shortcuts = $shortcuts;
    }*/

    private function _getModelMap()
    {
        return array(
            self::ORDER_NAMESPACE   => self::ORDER_MODEL_NAME,
            self::LINES_NAMESPACE   => self::LINES_MODEL_NAME,
            self::LABELS_NAMESPACE  => self::LABELS_MODEL_NAME
        );
    }

    protected function _buildReturnForm()
    {
        $returnForm = new Zend_Form('return_form');

        $locationList = minder_array_merge(array('' => ''),
            $this->minder->getLocationListByClause(array('STORE_TYPE = ? ' => 'RC')));

        $returnLocation = new Zend_Form_Element_Select('return_location');
        $returnLocation->setLabel('Returned to Location:')
            ->setDecorators(array('ViewHelper',
            array('HtmlTag', array('tag' => 'td')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))));

        $returnLocation->setMultiOptions($locationList);

        $reasonList = minder_array_merge(array('' => ''), $this->minder->getOptionsList('RET_REASON'));

        $returnReason = new Zend_Form_Element_Select('return_reason');
        $returnReason->setLabel('Reason for Return:')
            ->setDecorators(array('ViewHelper',
            array('HtmlTag', array('tag' => 'td')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))));

        $returnReason->setMultiOptions($reasonList);

        // TODO: set validators
        $returnQty = new Zend_Form_Element_Text('return_qty');
        $returnQty->setLabel('Quantity Returned:')
            ->setOptions(array('style' => 'width:50px'))
            ->setDecorators(array('ViewHelper',
            array('HtmlTag', array('tag' => 'td')),
            array('Label', array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))));


        $acceptButton = new Zend_Form_Element_Button('return_accept');
        $acceptButton->setLabel('Accept')
            ->setOptions(array('class' => 'green-button'))
            ->setDecorators(array('ViewHelper',
            array(array('data' => 'HtmlTag'), array('tag' => 'td'))));

        $cancelButton = new Zend_Form_Element_Button('return_cancel');
        $cancelButton->setLabel('Cancel')
            ->setOptions(array('class' => 'green-button'))
            ->setDecorators(array('ViewHelper',
            array(array('data' => 'HtmlTag'), array('tag' => 'td'))));

        $returnForm->addElements(array($returnLocation, $returnReason, $returnQty, $acceptButton, $cancelButton));
        $returnForm->setDecorators(array('FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form'));

        return "Testing".$returnForm;
    }

    public function returnAction()
    {
        $response   = new Minder_JSResponse();

        $mode       = $this->getRequest()->getParam('mode');

        if ($mode == 'single') {
            $location   =   $this->getRequest()->getParam('location');
            $reason     =   $this->getRequest()->getParam('reason');

            $selectedLines = $this->_getSelectedLines();
            if (!empty($selectedLines)) {

                if(!isset($location)) {
                    $location = '';
                }

                if(!isset($reason)) {
                    $reason =   '';
                }

                if($this->minder->limitWarehouse == 'all'){
                    $whId   =   current($this->minder->getListByField('SYS_USER.DEFAULT_WH_ID'));
                } else {
                    $whId   =   $this->minder->limitWarehouse;
                }

                $transaction    =   new Transaction_TRBKA();

                $transaction->objectId  =   $selectedLines[0]['SSN_ID'];
                $transaction->whId      =   $whId;
                $transaction->locnId    =   $location;
                $transaction->quantity  =   $selectedLines[0]['QTY_PICKED'];
                $transaction->reference =   $reason;
                $transaction->subLocnId =   $this->minder->limitPrinter;
        	$transaction->companyId = $selectedLines[0]['COMPANY_ID'];
; // want the company of the issn
        	$transaction->prodId = $selectedLines[0]['PROD_ID'];
 // want the prod  of the issn
        	$transaction->orderNo = $selectedLines[0]['PICK_ORDER'];
; // want the order no for the issns pick_order

                //if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSS', '', 'MASTER    '))) {
                if (false === ($result = $this->minder->doTransactionResponseV6($transaction ))) {
                    $response->errors[]      =   $this->minder->lastError;
                } else {
                    $response->messages[]    =   $result;
                }
            }
            else {
                $response->warnings[]   =   'Please, select items !';
            }
        }

        if ($mode == 'batch') {
            $location   =   $this->getRequest()->getParam('location');
            $reason     =   $this->getRequest()->getParam('reason');

            if(!isset($location)){
                $location = '';
            }
            if(!isset($reason)){
                $reason =   '';
            }
            if($this->minder->limitWarehouse == 'all'){
                $whId   =   current($this->minder->getListByField('SYS_USER.DEFAULT_WH_ID'));
            } else {
                $whId   =   $this->minder->limitWarehouse;
            }

            $selectedLines = $this->_getSelectedLines();
            $successList    = array();

            if (!empty($selectedLines)) {
                foreach($selectedLines as $line) {
                    $pickItemDetail =   $this->minder->getPickItemDetails($line['SSN_ID']);

                    $transaction    =   new Transaction_TRBKA();

                    $transaction->objectId  =   $line['SSN_ID'];
                    $transaction->whId      =   $whId;
                    $transaction->locnId    =   $location;
                    $transaction->quantity  =   abs($pickItemDetail['QTY_PICKED']);
                    $transaction->reference =   $reason;
                    $transaction->subLocnId =   $this->minder->limitPrinter;

                    if (false === ($result = $this->minder->doTransactionResponse($transaction, 'Y', 'SSKKKKKSS', '', 'MASTER    '))) {
                        $response->errors[]     =   $this->minder->lastError . ': ' . $line['SSN_ID'];
                    } else {
                        $response->messages[]   =   $result;
                        array_push($successList, $line['PICK_DETAIL_ID']);

                    }
                }
            }

            else {
                $response->warnings[]   =   'Please, select items !';
            }
        }

        echo json_encode(array('response' => $response, 'ok' => $successList));
    }

    public function resetSelectionAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $pagination = $this->restorePagination(self::LINES_NAMESPACE);
        $rowSelector = $this->_helper->getHelper('RowSelector');

        // get common selected count
        $selected = $rowSelector->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, self::LINES_NAMESPACE);

        // unset succeed rows
        $succeed = $this->getRequest()->getParam('ok');

        if(!empty($succeed)) {
            foreach($succeed as $row) {
                unset($selected[$row]);
            }
        }

        // add sysScreen like in selecRow action
        $tmpSysScreen = new stdClass();
        $tmpSysScreen->selectedRows = $selected;
        $tmpSysScreen->selectedRowsTotal = $rowSelector->getSelectedCount(self::LINES_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $tmpSysScreen->selectedRowsOnPage = count($selected);

        $result = new Minder_JSResponse();
        $result->sysScreens = array();
        $result->selectedRows = array();
        $result->selectedRowsTotal = 0;
        $result->selectedRowsOnPage = 0;


        $result->sysScreens[self::LINES_NAMESPACE] = $tmpSysScreen;

        // change selected rows in session
        $sessionSelection = array();

        foreach($selected as $key => $val) {
            $sessionSelection[$key] = $key;
        }

        // clear selection
        $_SESSION['selector']['selection']['service']['select-row'][self::LINES_NAMESPACE]['selected']   = $sessionSelection;
        $_SESSION['selector']['selection']['service']['select-row'][self::LINES_NAMESPACE]['unselected'] = array();

        echo json_encode($result);
    }

    public function getSelectedIssnAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $issnObject = new stdClass();
        $issnList   = $this->_getSelectedLines();

        if(!empty($issnList)) {
            $issnObject->ssnId  = $issnList[0]['SSN_ID'];
            $issnObject->qty    = $issnList[0]['QTY_PICKED'];
        }
        else {
            $issnObject->ssnId = '';
            $issnObject->qty   = '';
        }

        echo json_encode($issnObject);
    }

    protected function _getSelectedLines()
    {
        $rowSelector = $this->_helper->getHelper('RowSelector');
        $linesModel  = $rowSelector->getModel(self::LINES_NAMESPACE,
                                              self::$defaultSelectionAction,
                                              self::$defaultSelectionController);

        $selectedCount = $rowSelector->getSelectedCount(self::LINES_NAMESPACE,
                                                        self::$defaultSelectionAction,
                                                        self::$defaultSelectionController);

        $linesList = array();

        if($selectedCount > 0) {
            $linesModel->addConditions($rowSelector->getSelectConditions(self::LINES_NAMESPACE,
                                                                         self::$defaultSelectionAction,
                                                                         self::$defaultSelectionController));
            $rowsAmount = count($linesModel);
            $expression = '*';
            $linesList = $linesModel->selectArbitraryExpression(0, $rowsAmount, $expression);
        }

        return $linesList;
    }

}
