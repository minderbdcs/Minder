<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 09.11.11
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */
 
class Despatches_AwaitingExitController extends Minder_Controller_Action{

    const MODEL_NAME        = 'AWAITING_EXIT';
    const MODEL_NAMESPACE   = 'DESPATCHES-AWAITING_EXIT';

    const LABELS_MODEL_NAME = 'AWAITING_EXIT_LABELS';
    const LABELS_NAMESPACE  = 'DESPATCHES-LABELS';

    public function indexAction()
    {


        $this->view->pageTitle = 'Awaiting exit';

        try {
            $this->view->carriers           = $this->minder->getCarriers();
            $this->view->states             = $this->_getStates();
            $this->view->depots             = $this->_getDepots();
            $this->view->carrierServices    = $this->minder->getCarrierServiceTypes();

        $this->view->despatchedSsName     = $this->view->searchFormSsName = self::MODEL_NAME;
        $this->view->despatchedNamespace  = self::MODEL_NAMESPACE;

        $this->view->labelsSsName         = self::LABELS_MODEL_NAME;
        $this->view->labelsNamespace      = self::LABELS_NAMESPACE;

        $screenBuilder = new Minder_SysScreen_Builder();
        /**
         * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
         */
        $searchKeeper  = $this->_helper->searchKeeper;

        list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MODEL_NAME);

        $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);

        $searchFields = $searchKeeper->getSearch($searchFields,
                                                 self::MODEL_NAMESPACE,
                                                 self::$defaultSelectionAction,
                                                 self::$defaultSelectionController);

        $this->view->searchFields = $searchFields;

        $despatchedModel = $screenBuilder->buildSysScreenModel(self::MODEL_NAME,
                                                               new Minder_SysScreen_Model_AwaitingExit());

        $despatchedModel->setConditions($despatchedModel->makeConditionsFromSearch($searchFields));

        /**
         * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
         */
        $rowSelector   = $this->_helper->getHelper('RowSelector');
        $rowSelector->setRowSelection('select_complete',
                                      'init',
                                      null,
                                      null,
                                      $despatchedModel,
                                      true,
                                      self::MODEL_NAMESPACE,
                                      self::$defaultSelectionAction,
                                      self::$defaultSelectionController);


        // Model for PACK_ID table
        $labelsModel = $screenBuilder->buildSysScreenModel(self::LABELS_MODEL_NAME,
                                                           new Minder_SysScreen_Model_AwaitingExitLabels());

        $rowSelector->setRowSelection('select_complete',
                                      'init',
                                      null,
                                      null,
                                      $labelsModel,
                                      true,
                                      self::LABELS_NAMESPACE,
                                      self::$defaultSelectionAction,
                                      self::$defaultSelectionController);

        $this->getRequest()->setParam('sysScreens', array(self::MODEL_NAMESPACE => array(),
                                                          self::LABELS_NAMESPACE => array()));

        $this->initSubDatasets();
        $this->getDatasetAction();
        if (count($this->view->errors) > 0) {
            foreach ($this->view->errors as $errorMsg)
                $this->addError($errorMsg);

            $this->view->hasErrors = true;
        }

        $this->view->despatchedJsSearchResults = $this->view->jsSearchResult(
            self::MODEL_NAME,
            self::MODEL_NAMESPACE,
            array('sysScreenCaption' => 'ORDERS LIST', 'usePagination' => true)
        );
        $this->view->despatchedJsSearchResultsDataset = $this->view->sysScreens[self::MODEL_NAMESPACE];

        $this->view->labelsJsSearchResults = $this->view->jsSearchResult(
            self::LABELS_MODEL_NAME,
            self::LABELS_NAMESPACE,
            array('sysScreenCaption' => 'LABELS LIST', 'usePagination' => true)
        );
        $this->view->labelsJsSearchResultsDataset = $this->view->sysScreens[self::LABELS_NAMESPACE];

        } catch (Exception $e) {
            $this->addError($e->getMessage());
            $this->view->hasErrors = true;
        }

        return;
    }

    public function getDatasetAction() {
        $datasets = array(
            self::MODEL_NAMESPACE   => self::MODEL_NAME,
            self::LABELS_NAMESPACE  => self::LABELS_MODEL_NAME
        );
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
            $this->view->sysScreens[$namespace] = $this->view->jsSearchResultDataset($datasets[$namespace],
                                                                                     $this->view->dataset,
                                                                                     $this->view->selectedRows,
                                                                                     $this->view->paginator);

            if ($namespace == self::MODEL_NAMESPACE) {
                $selectedDespatches = $this->_getSelectedDespatches($this->view->paginator['selectedRows']);
                $this->view->sysScreens[$namespace]['pickedCarriers'] = array_values(array_unique(Minder_ArrayUtils::mapField($selectedDespatches, 'PICKD_CARRIER_ID')));
                $this->view->sysScreens[$namespace]['pickedCarrierService'] = array_values(array_unique(Minder_ArrayUtils::mapField($selectedDespatches, 'PICKD_SERVICE_RECORD_ID')));
            }

            unset($this->view->paginator);
            unset($this->view->dataset);
            unset($this->view->selectedRows);
        }
    }

    public function searchAction() {

        $this->view->errors = isset($this->view->errors) ? $this->view->errors : array();

        try{
            /**
            * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
            */
            $searchKeeper  = $this->_helper->searchKeeper;
            $screenBuilder = new Minder_SysScreen_Builder();

            list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MODEL_NAME);

            $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
            $searchFields = $searchKeeper->makeSearch($searchFields,
                                                      self::MODEL_NAMESPACE,
                                                      self::$defaultSelectionAction,
                                                      self::$defaultSelectionController);

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
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->rowSelector;
            /**
            * @var Minder_SysScreen_Model $rowsModel
            */
            $rowsModel    = $rowSelector->getModel(self::MODEL_NAMESPACE,
                                                   self::$defaultSelectionAction,
                                                   self::$defaultSelectionController);

            $rowsModel->setConditions($rowsModel->makeConditionsFromSearch($searchFields));

            $rowSelector->setRowSelection('select_complete',
                                          'init',
                                          null,
                                          null,
                                          $rowsModel,
                                          true,
                                          self::MODEL_NAMESPACE,
                                          self::$defaultSelectionAction,
                                          self::$defaultSelectionController);
            $this->initSubDatasets();

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__,
                                                      $e->getMessage(), $e->getTrace()));
        }

        return $this->_forward('get-dataset');
    }

    public function selectRowAction() {
        $result = new stdClass();
        $result->errors   = array();
        $result->warnings = array();
        $result->messages = array();
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
                    $pagination['selectedPage']  = (isset($sysScreen['paginator']['selectedPage']))  ?
                            $sysScreen['paginator']['selectedPage']  : $pagination['selectedPage'];
                    $pagination['showBy']        = (isset($sysScreen['paginator']['showBy']))        ?
                            $sysScreen['paginator']['showBy']        : $pagination['showBy'];
                    $pagination['selectionMode'] = (isset($sysScreen['paginator']['selectionMode'])) ?
                            $sysScreen['paginator']['selectionMode'] : $pagination['selectionMode'];
                }

                if (isset($sysScreen['rowId']) && isset($sysScreen['state'])) {
                    $rowSelector->setSelectionMode($pagination['selectionMode'],
                                                   $namespace,
                                                   self::$defaultSelectionAction,
                                                   self::$defaultSelectionController);

                    $rowSelector->setRowSelection($sysScreen['rowId'],
                                                  $sysScreen['state'],
                                                  $pagination['selectedPage'],
                                                  $pagination['showBy'],
                                                  null,
                                                  false,
                                                  $namespace,
                                                  self::$defaultSelectionAction,
                                                  self::$defaultSelectionController);
                }

                $tmpSysScreen->selectedRowsTotal = $rowSelector->getSelectedCount($namespace,
                                                                                  self::$defaultSelectionAction,
                                                                                  self::$defaultSelectionController);
                if ($tmpSysScreen->selectedRowsTotal > 0) {
                    $tmpSysScreen->selectedRows = $rowSelector->getSelectedOnPage($pagination['selectedPage'],
                                                                                  $pagination['showBy'],
                                                                                  false,
                                                                                  $namespace);

                    $tmpSysScreen->selectedRowsOnPage = count($tmpSysScreen->selectedRows);

                    if ($namespace == self::LABELS_NAMESPACE)
                        $tmpSysScreen->firstSelectedRow = $this->_getPackFirstSelectedRow();
                }


                if ($namespace == self::MODEL_NAMESPACE) {
                    $selectedDespatches = $this->_getSelectedDespatches($tmpSysScreen->selectedRowsTotal);
                    $tmpSysScreen->pickedCarriers = array_values(array_unique(Minder_ArrayUtils::mapField($selectedDespatches, 'PICKD_CARRIER_ID')));
                    $tmpSysScreen->pickedCarrierService = array_values(array_unique(Minder_ArrayUtils::mapField($selectedDespatches, 'PICKD_SERVICE_RECORD_ID')));

                    $this->initSubDatasets();
                    $this->getDatasetAction();
                }

                $result->sysScreens[$namespace] = $tmpSysScreen;
            }

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__,
                                                     $e->getMessage(), $e->getTrace()));
        }

        echo json_encode($result);
    }

    protected function initSubDatasets() {
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->rowSelector;
        $selectedDespatchesAmount = $rowSelector->getSelectedCount(self::MODEL_NAMESPACE,
                                                               self::$defaultSelectionAction,
                                                               self::$defaultSelectionController);

        /**
        * @var Minder_SysScreen_Model_AwaitingExitLabels $labelsModel
        */
        $labelsModel = $rowSelector->getModel(self::LABELS_NAMESPACE,
                                              self::$defaultSelectionAction,
                                              self::$defaultSelectionController);

        if ($selectedDespatchesAmount > 0) {

            $despatchedModel = $rowSelector->getModel(self::MODEL_NAMESPACE,
                                                      self::$defaultSelectionAction,
                                                      self::$defaultSelectionController);

            $despatchedModel->addConditions($rowSelector->getSelectConditions(self::MODEL_NAMESPACE,
                                                                              self::$defaultSelectionAction,
                                                                              self::$defaultSelectionController));

            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__,
                                                      $selectedDespatchesAmount));

            $expression = 'DISTINCT PICK_DESPATCH.DESPATCH_ID';

            $despatches = $despatchedModel->selectArbitraryExpression(0, $selectedDespatchesAmount, $expression);
            $labelsModel->setDespatchLimit($despatches);

        }
        else {
            $labelsModel->setConditions(array('1 = 2' => array()));
        }
        $rowSelector->setRowSelection('select_complete',
                                      'init',
                                      null,
                                      null,
                                      $labelsModel,
                                      true,
                                      self::LABELS_NAMESPACE,
                                      self::$defaultSelectionAction,
                                      self::$defaultSelectionController);
    }

    protected function _getPackFirstSelectedRow() {
        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_helper->getHelper('RowSelector');
        /**
         * @var Minder_SysScreen_Model_LocationIssn $issnModel
         */
        $labelsModel = $rowSelector->getModel(self::LABELS_NAMESPACE,
                                              self::$defaultSelectionAction,
                                              self::$defaultSelectionController);

        $labelsModel->addConditions($rowSelector->getSelectConditions(self::LABELS_NAMESPACE,
                                                                      self::$defaultSelectionAction,
                                                                      self::$defaultSelectionController));

        if (false !== ($tmpRow = $labelsModel->getItems(0, 1, false)) && (count($tmpRow) > 0)) {
            return current($tmpRow);
        }

        return array();
    }

    public function reportAction() {
        $request = $this->getRequest();
        $request->setParam('selection_namespace', $request->getParam('namespace'));
        $request->setParam('selection_action', self::$defaultSelectionAction);
        $request->setParam('selection_controller', self::$defaultSelectionController);

        return $this->_forward('report', 'service', 'default');
    }

    public function printLabelAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_AwaitingExit $despatchedModel
             */
            $despatchedModel = $rowSelector->getModel(self::MODEL_NAMESPACE,
                                                      self::$defaultSelectionAction,
                                                      self::$defaultSelectionController);

            $despatchedModel->addConditions($rowSelector->getSelectConditions(self::MODEL_NAMESPACE,
                                                                              self::$defaultSelectionAction,
                                                                              self::$defaultSelectionController));

            $printResult   = $despatchedModel->printLabel($this->minder->getPrinter());

            $result->messages += $printResult->messages;
            $result->warnings += $printResult->warnings;
            $result->errors   += $printResult->errors;

        } catch (Exception $e) {
            $result->errors[] = 'Error printing Despatch Label: ' . $e->getMessage();
        }

        echo json_encode($result);
    }

    public function reprintLabelAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $response = new Minder_JSResponse();

        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector       = $this->_helper->getHelper('RowSelector');
            $selectedPackCount = $rowSelector->getSelectedCount(self::LABELS_NAMESPACE,
                                                                self::$defaultSelectionAction,
                                                                self::$defaultSelectionController);

            if ($selectedPackCount < 1) {
                $this->view->warnings[] = 'No Packs selected. Please, select one.';
                return $this->_forward('get-dataset');
            }

            /**
            * @var Minder_SysScreen_Model_AwaitingExitLabels $labelsModel
            */
            $labelsModel = $rowSelector->getModel(self::LABELS_NAMESPACE,
                                                  self::$defaultSelectionAction,
                                                  self::$defaultSelectionController);

            $labelsModel->addConditions($rowSelector->getSelectConditions(self::LABELS_NAMESPACE,
                                                                          self::$defaultSelectionAction,
                                                                          self::$defaultSelectionController));

            $result = $labelsModel->printLabel($this->minder->getPrinter());
            $response->errors   = array_merge($response->errors, $result->errors);
            $response->messages = array_merge($response->messages, $result->messages);

        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        echo json_encode($response);
    }

    public function despatchExitAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            /**
            * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
            */
            $rowSelector = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_AwaitingExit $despatchedModel
             */
            $despatchedModel = $rowSelector->getModel(self::MODEL_NAMESPACE,
                                                      self::$defaultSelectionAction,
                                                      self::$defaultSelectionController);

            $despatchedModel->addConditions($rowSelector->getSelectConditions(self::MODEL_NAMESPACE,
                                                                              self::$defaultSelectionAction,
                                                                              self::$defaultSelectionController));

            $rowsAmount = count($despatchedModel);
            if ($rowsAmount < 1) {
                $result->warnings[] = 'Now rows selected.';
                echo json_encode($result);
                return;
            }

            $expression = '*';
            $despatchList = $despatchedModel->selectArbitraryExpression(0, $rowsAmount, $expression);

            foreach ($despatchList as $despatch) {
                $transaction = new Transaction_DSDXL();

                $transaction->reference = $despatch['AWB_CONSIGNMENT_NO'];
                $transaction->qty       = $despatch['PICKD_ADDRESS_QTY'];

                $response = $this->minder->doTransactionResponse($transaction, 'Y', 'SSSSKSKSK', '', 'MASTER    ');
                $response &= $response;

                $this->getDatasetAction();
                $this->initSubDatasets();

            }

            (false === $result) ? $result->errors[]     = 'Error while DSDXL transaction ' :
                                  $result->messages[]   = 'Transaction DSDXL - ' . $response;
        } catch (Exception $e) {
            $result->errors[] = 'Error in Despatch Exit: ' . $e->getMessage();
        }

        echo json_encode($result);
    }

    public function changeDepotAction() {
        $this->_initViewMessagesContainers();

        $carrierDepotRecordId = $this->getRequest()->getParam('carrierDepotRecordId');

        if (is_null($carrierDepotRecordId)) {
            $this->view->errors[] = 'No CarrierDepot record given.';
            return $this->_forward('get-dataset');
        }

        try {
            $despatchesModel = $this->_getDespatchedModel();
            $despatchesModel->addConditions($this->_getDespatchedModelSelectConditions());

            $this->_copyMessagesToView($despatchesModel->changeDepot($carrierDepotRecordId));

        } catch (Exception $e) {
            $this->view->errors[] = 'Error in Despatch Exit: ' . $e->getMessage();
        }

        return $this->_forward('get-dataset');
    }

    public function changeSentFromAction() {
        $this->_viewRenderer()->setNoRender();

        $selectedDespatches = $this->_getSelectedDespatches();
        $newCarrierServiceId = $this->getRequest()->getParam('carrierServiceId');
        $result = new Minder_JSResponse();

        try {
            $result = $this->_changeSentFromHelper()->changeSentFrom($selectedDespatches, $newCarrierServiceId, $result);
        } catch (Exception $e) {
            $result->addErrors($e->getMessage());
        }

        echo json_encode($result);
    }

    /**
     * @return Minder_SysScreen_Model_AwaitingExit
     */
    protected function _getDespatchedModel() {
        return $this->_rowSelector()->getModel(self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    /**
     * @return array
     */
    protected function _getDespatchedModelSelectConditions() {
        return $this->_rowSelector()->getSelectConditions(self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
    }

    protected function _getStates() {
        $stateManager = new StateManager();
        return $stateManager->getStates();
    }

    protected function _getDepots() {
        $depotManager = new DepotManager();
        return $depotManager->getDepots();
    }

    /**
     * @param $selectedRowsAmount
     * @return array
     * @throws Minder_SysScreen_Model_Exception
     */
    protected function _getSelectedDespatches($selectedRowsAmount = null)
    {
        /**
         * @var Minder_SysScreen_Model_AwaitingExit $model
         */
        $model = $this->_rowSelector()->getModel(self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $model->addConditions($this->_rowSelector()->getSelectConditions(self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController));
        return $model->getDespatches($selectedRowsAmount);
    }

    /**
     * @return Minder_Controller_Action_Helper_ChangeSentFrom
     */
    protected function _changeSentFromHelper() {
        return $this->getHelper('ChangeSentFrom');
    }
}
