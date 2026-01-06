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
 */


class Receipts_GrnController extends Minder_Controller_Action
{
    protected static $grnModelName                  = 'GRN';
    protected static $grnSelectionAction            = 'select-rows';
    protected static $grnSelectionController        = 'grn';
    protected static $grnSelectionModule            = 'receipts';
    
    protected static $grnLinesModelName             = 'GRNLINE';
    protected static $grnLinesSelectionAction       = 'select-rows';
    protected static $grnLinesSelectionController   = 'grn';
    protected static $grnLinesSelectionModule       = 'receipts';

    protected static $grnPDModelName                = 'GRNPRODUCTDETAILS';
    protected static $grnPDSelectionAction          = 'select-rows';
    protected static $grnPDSelectionController      = 'grn';
    protected static $grnPDSelectionModule          = 'receipts';

    protected static $grnCPIDModelName              = 'CHANGE_PROD_ID';
    protected static $grnCPIDSelectionAction        = 'select-rows';
    protected static $grnCPIDSelectionController    = 'grn';
    protected static $grnCPIDSelectionModule        = 'receipts';

    public function init() {
        parent::init();
        
        $this->view->reportModule               = 'default';
        $this->view->reportController           = 'service';
        $this->view->reportAction               = 'report';
        
        $this->view->grnSelNamespace            = self::$grnModelName;
        $this->view->grnSelAction               = self::$grnSelectionAction;
        $this->view->grnSelController           = self::$grnSelectionController;
        $this->view->grnSelModule               = self::$grnSelectionModule;
        $this->view->grnSysScreenName           = self::$grnModelName;

        $this->view->grnLineSelectionNamespace  = self::$grnLinesModelName;
        $this->view->grnLineSelectionAction     = self::$grnLinesSelectionAction;
        $this->view->grnLineSelectionController = self::$grnLinesSelectionController;
        $this->view->grnLineSelectionModule     = self::$grnLinesSelectionModule;
        $this->view->grnLineSysScreenName       = self::$grnLinesModelName;

        $this->view->grnPDSelectionNamespace  = self::$grnPDModelName;
        $this->view->grnPDSelectionAction     = self::$grnPDSelectionAction;
        $this->view->grnPDSelectionController = self::$grnPDSelectionController;
        $this->view->grnPDSelectionModule     = self::$grnPDSelectionModule;
        $this->view->grnPDSysScreenName       = self::$grnPDModelName;

        $this->view->changeProdIdSelectionNamespace  = self::$grnCPIDModelName;
        $this->view->changeProdIdSelectionAction     = self::$grnCPIDSelectionAction;
        $this->view->changeProdIdSelectionController = self::$grnCPIDSelectionController;
        $this->view->changeProdIdSelectionModule     = self::$grnCPIDSelectionModule;
        $this->view->changeProdIdSysScreenName       = self::$grnCPIDModelName;
    }

    public function indexAction() {
        try {
            $this->view->pageTitle = 'Search GRN';
            
            $request = $this->getRequest();
            $formAction = $request->getParam('SEARCH_FORM_ACTION', 'none');
            
            $screenBuilder = new Minder_SysScreen_Builder();
            list($searchFields, $searchActions) = $screenBuilder->buildSysScreenSearchFields(self::$grnModelName);
            $searchKeeper = $this->_helper->searchKeeper;
            

            switch (strtolower($formAction)) {
                case 'search': 
                  
                $array_new=$searchFields;

                foreach ($array_new as $key => $value) { 
                    

                            foreach($value as $key1=>$val1){
                             
                                if ($key1=='SSV_INPUT_METHOD' && $val1!='DP') { unset($array_new[$key]); }
                            
                                
                            }    

                }

                

                if($this->minder->isNewDateCalculation() == false){
                    foreach($array_new as $key=>$val){

                        foreach($val as $key1=>$val1){

                            if($key1=='SEARCH_VALUE' && $val1!=''){

                                if (DateTime::createFromFormat('Y-m-d H:i:s', $val1) !== FALSE  || DateTime::createFromFormat('Y-m-d', $val1)!== FALSE) {

                                    $datetimet = $val1;
                                    $tz_tot = 'UTC';
                                    $format = 'Y-m-d h:i:s';
                                    $dtt = new DateTime($datetimet, new DateTimeZone($tz_from));
                                    $dtt->setTimeZone(new DateTimeZone($tz_tot));                                                             
                                    $searchFields[$key][$key1]=$dtt->format($format);

                                }                 

                            }

                        }

                    }
                }
            


                    $searchFields = $searchKeeper->makeSearch($searchFields);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields);
            }
            
            $rowSelector = $this->_helper->rowSelector;
            $grnModel = $screenBuilder->buildSysScreenModel(self::$grnModelName, new Minder_SysScreen_Model_Grn());
            $grnModel->setConditions($grnModel->makeConditionsFromSearch($searchFields));
            
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $grnModel, true, self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);

            $linesModel = $screenBuilder->buildSysScreenModel(self::$grnLinesModelName, new Minder_SysScreen_Model_GrnLine());
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);

            $this->_preProcessNavigation();
            $this->view->totalCount = count($grnModel);
            $this->_postProcessNavigation(array('total' => $this->view->totalCount));
            
            $pageSelector = $this->view->navigation['pageselector'];
            $showBy       = $this->view->navigation['show_by'];

            $this->view->grns          = array();
            $this->view->selectedGrns  = array();
            $this->view->selectedCount = $rowSelector->getSelectedCount(self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
            
            $this->view->searchFields     = $searchFields;
            $this->view->searchActions    = $searchActions;
            
            list(
                $this->view->fields,
                $this->view->tabs,
                $this->view->colors,
                $this->view->actions
            )                             = $screenBuilder->buildSysScreenSearchResult(self::$grnModelName);

            list($this->view->grnButtons)     = $screenBuilder->buildScreenButtons(self::$grnModelName);

            if ($this->view->totalCount > 0) {
                $this->view->grns         = $grnModel->getItems($pageSelector * $showBy, $showBy, false);
                $this->view->selectedGrns = $rowSelector->getSelected($pageSelector, $showBy, true, self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
            }



            file_put_contents("/tmp/GrnLog_1.txt", "Date Calculation Mod : ".$this->minder->isNewDateCalculation()." \n");
            file_put_contents("/tmp/GrnLog_1.txt", print_r($this->view->grns, true), FILE_APPEND);


            $this->view->selectMode       = $rowSelector->getSelectionMode('', self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function getGrnLinesAction () {
        $this->view->errors        = array();
        $this->_preProcessNavigation();
        $request                   = $this->getRequest();
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $screenBuilder                  = new Minder_SysScreen_Builder();
        $this->view->lines              = array();
        $this->view->totalLines         = 0;
        $this->view->selectedLines      = array();
        $this->view->selectedLinesCount = 0;
        
        try {
            list(
                $this->view->fields, 
                $this->view->tabs, 
                , 
                $this->view->actions
            )                      = $screenBuilder->buildSysScreenSearchResult(self::$grnLinesModelName);

            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector           = $this->_helper->getHelper('RowSelector');
            $selectedGrnsCount     = $rowSelector->getSelectedCount(self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
            $rowSelector->setDefaultSelectionMode('one', self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
            
            if ($selectedGrnsCount > 0) {
                $grnsModel         = $rowSelector->getModel(self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
                $grnsModel->addConditions($rowSelector->getSelectConditions(self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController));
                $grnNos            = $grnsModel->selectGrnNo(0, count($grnsModel));
                $tmpCondString     = 'GRN.GRN IN (' . substr(str_repeat('?, ', count($grnNos)), 0, -2) . ')';
                
                $linesModel = $rowSelector->getModel(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
                $linesModel->setConditions(array($tmpCondString => $grnNos));
                $rowSelector->setRowSelection('select_complete', 'init', null, null, $linesModel, true, self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
            
                $this->view->totalLines         = count($linesModel);
                $this->view->selectedLinesCount = $rowSelector->getSelectedCount(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
                $this->_postProcessNavigation(array('total' => $this->view->totalLines));
                $pageSelector                   = $this->view->navigation['pageselector'];
                $showBy                         = $this->view->navigation['show_by'];
                $this->view->lines              = $linesModel->getItems($pageSelector*$showBy, $showBy, false);
                $this->view->selectedLines      = $rowSelector->getSelected($pageSelector, $showBy, true, self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
                
                $linesModel->addConditions($rowSelector->getSelectConditions(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController));
                $this->view->firstSelectedLine  = $linesModel->getItems(0, 1, false);
                $this->view->firstSelectedLine  = (count($this->view->firstSelectedLine) > 0) ? current($this->view->firstSelectedLine) : array();
                Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->firstSelectedLine));
            } else {
                $this->_postProcessNavigation(array('total' => 0));
            }
            $this->view->selectMode         = $rowSelector->getSelectionMode('', self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
            list($this->view->grnLineButtons) = $screenBuilder->buildScreenButtons(self::$grnLinesModelName);
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->view->selectMode));
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }
    
    public function getGrnProductDetailsAction() {
        $this->view->errors        = array();
        $this->_preProcessNavigation();
        $request                   = $this->getRequest();
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $screenBuilder                  = new Minder_SysScreen_Builder();
        $this->view->details              = array();
        $this->view->totalDetails         = 0;
        $this->view->selectedDetails      = array();
        $this->view->selectedDetailsCount = 0;
        
        try {
            list(
                $this->view->fields, 
                $this->view->tabs, 
                , 
                $this->view->actions
            )                      = $screenBuilder->buildSysScreenSearchResult(self::$grnPDModelName);

            list($this->view->grnPDButtons) = $screenBuilder->buildScreenButtons(self::$grnPDModelName);

            $rowSelector           = $this->_helper->getHelper('RowSelector');
            $selectedGrnLinesCount = $rowSelector->getSelectedCount(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
            
            if ($selectedGrnLinesCount > 0) {
                $grnLinesModel     = $rowSelector->getModel(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
                $grnLinesModel->addConditions($rowSelector->getSelectConditions(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController));
                $prodIds           = $grnLinesModel->selectProdId(0, count($grnLinesModel));
                $tmpCondString     = 'PROD_PROFILE.PROD_ID IN (' . substr(str_repeat('?, ', count($prodIds)), 0, -2) . ')';
                
                $detailsModel      = $screenBuilder->buildSysScreenModel(self::$grnPDModelName, new Minder_SysScreen_Model_GrnProductDetails());
                $detailsModel->setConditions(array($tmpCondString => $prodIds));
                $rowSelector->setRowSelection('select_complete', 'init', null, null, $detailsModel, true, self::$grnPDModelName, self::$grnPDSelectionAction, self::$grnPDSelectionController);
            
                $this->view->totalDetails         = count($detailsModel);
                $this->view->selectedDetailsCount = $rowSelector->getSelectedCount(self::$grnPDModelName, self::$grnPDSelectionAction, self::$grnPDSelectionController);
                $this->_postProcessNavigation(array('total' => $this->view->totalDetails));
                $pageSelector                   = $this->view->navigation['pageselector'];
                $showBy                         = $this->view->navigation['show_by'];
                $this->view->details            = $detailsModel->getItems($pageSelector*$showBy, $showBy, false);
                $this->view->selectedDetails    = $rowSelector->getSelected($pageSelector, $showBy, true, self::$grnPDModelName, self::$grnPDSelectionAction, self::$grnPDSelectionController);
            } else {
                $this->_postProcessNavigation(array('total' => 0));
            }
            $this->view->selectMode         = $rowSelector->getSelectionMode('', self::$grnPDModelName, self::$grnPDSelectionAction, self::$grnPDSelectionController);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }

    public function getChangeProdIdAction() {
        $this->view->errors        = array();
        $this->_preProcessNavigation();
        $request                   = $this->getRequest();

        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);

        $screenBuilder                  = new Minder_SysScreen_Builder();
        $this->view->details              = array();
        $this->view->totalDetails         = 0;
        $this->view->selectedDetails      = array();
        $this->view->selectedDetailsCount = 0;

        try {
            $formAction = $request->getParam('SEARCH_FORM_ACTION', 'none');

            list($searchFields, $searchActions) = $screenBuilder->buildSysScreenSearchFields(self::$grnCPIDModelName);
            /**
             * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
             */
            $searchKeeper = $this->_helper->searchKeeper;

            switch (strtolower($formAction)) {
                case 'search':
                    $searchFields = $searchKeeper->makeSearch($searchFields);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields);
            }

            list(
                $this->view->fields,
                $this->view->tabs,
                ,
                $this->view->actions
                )                      = $screenBuilder->buildSysScreenSearchResult(self::$grnCPIDModelName);

            list($this->view->changeProdIdActions) = $screenBuilder->buildScreenButtons(self::$grnCPIDModelName);

            $rowSelector           = $this->_helper->getHelper('RowSelector');
            $selectedGrnLinesCount = $rowSelector->getSelectedCount(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);

            $changeProdIdModel      = $screenBuilder->buildSysScreenModel(self::$grnCPIDModelName, new Minder_SysScreen_Model_ChangeProdId());
            if ($selectedGrnLinesCount > 0) {
                $grnLinesModel     = $rowSelector->getModel(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);
                $grnLinesModel->addConditions($rowSelector->getSelectConditions(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController));
                $prodIds           = $grnLinesModel->selectProdId(0, count($grnLinesModel));

                foreach ($searchFields as &$searchField) {
                    if ($searchField['SSV_ALIAS'] == 'FROM_PRODUCT') {
                        $searchField['SSV_TITLE'] .= ' ' . implode(' ', $prodIds);
                    }
                }
            }

            $changeProdIdModel->addConditions($changeProdIdModel->makeConditionsFromSearch($searchFields));
            $rowSelector->setRowSelection('select_complete', 'init', null, null, $changeProdIdModel, true, self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController);

            $this->view->totalDetails         = count($changeProdIdModel);
            $this->view->selectedDetailsCount = $rowSelector->getSelectedCount(self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController);
            $this->_postProcessNavigation(array('total' => $this->view->totalDetails));
            $pageSelector                   = $this->view->navigation['pageselector'];
            $showBy                         = $this->view->navigation['show_by'];
            $this->view->details            = $changeProdIdModel->getItems($pageSelector*$showBy, $showBy, false);
            $this->view->selectedDetails    = $rowSelector->getSelected($pageSelector, $showBy, true, self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController);
            $this->view->selectMode         = $rowSelector->getSelectionMode('', self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController);
            list($this->view->buttons)      = $screenBuilder->buildScreenButtons(self::$grnCPIDModelName);
            $this->view->searchFields       = $searchFields;
            $this->view->searchActions      = $searchActions;
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
    }

//edit GRN record block -----------------------------------
    /**
     * Show detailed info about object selected in Index
     *
     * @return void
     */
    public function showAction() {
        $formAction = $this->getRequest()->getPost('action', 'none');
        $rowId      = $this->getRequest()->getParam('row_id');
        
        switch (strtolower($formAction)) {
            case 'edit':
                $this->_helper->redirector('edit', 'grn', 'receipts', array('row_id' => $rowId));
                break;
            case 'return':
                $this->_helper->redirector('index', 'grn', 'receipts');
                break;
        }
        
        $rowSelector            = $this->_helper->getHelper('RowSelector');
        $grnModel               = $rowSelector->getModel(self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
        $grnModel->addConditions($grnModel->makeConditionsFromId($rowId));
        $this->view->pageTitle  = 'Show';
        $this->view->grnObj     = current($grnModel->selectGrnLineObject(0, 1));
   
    }

    /**
     * Perform edit and save grnLine object
     *
     * @return void
     */
    public function editAction() {
        $this->view->pageTitle  = 'edit';
        $rowId                  = $this->getRequest()->getParam('row_id');
        $rowSelector            = $this->_helper->getHelper('RowSelector');
        $grnModel               = $rowSelector->getModel(self::$grnModelName, self::$grnSelectionAction, self::$grnSelectionController);
        $grnModel->addConditions($grnModel->makeConditionsFromId($rowId));
        $this->view->grnObj     = current($grnModel->selectGrnLineObject(0, 1));

        if (count($this->getRequest()->getPost('action')) > 0) {
            switch (strtolower($this->getRequest()->getPost('action'))) {
                case 'save':
                    $result = true;
                    if ($this->getRequest()->getPost('pallet_owner') != $this->view->grnObj->palletsYn ||
                    $this->getRequest()->getPost('pallet_qty') != $this->view->grnObj->grnPalletQty) {

                        /*if ( $this->getRequest()->getPost('pallet_owner') != 'NOT_FOUND'
                         && $this->getRequest()->getPost('pallet_owner') != null
                         && $this->getRequest()->getPost('pallet_qty') != null) {*/
                        $transaction = new Transaction_UGHPA();
                        $transaction->grnId       = $this->view->grnObj->grn;
                        $transaction->palletQty   = $this->getRequest()->getParam('pallet_qty');
                        if ($this->getRequest()->getPost('pallet_owner') == 'N') {
                            $transaction->palletOwner = 0;
                        } else {
                            $transaction->palletOwner = $this->getRequest()->getParam('pallet_owner');
                        }

                        $currentResult = $this->minder->doTransactionResponse($transaction);
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }

                    if ($this->getRequest()->getPost('pack_crate_owner') != $this->view->grnObj->packCrateOwner ||
                    $this->getRequest()->getPost('pack_crate_type') != $this->view->grnObj->packCrateType ||
                    $this->getRequest()->getPost('pack_crate_qty') != $this->view->grnObj->packCrateQty) {

                        $transaction             = new Transaction_UGHCA();
                        $transaction->grnId      = $this->view->grnObj->grn;
                        $transaction->crateOwner = $this->getRequest()->getPost('pack_crate_owner');
                        $transaction->crateType  = $this->getRequest()->getPost('pack_crate_type');
                        if ( $this->getRequest()->getPost('pack_crate_type') != 'NO') {
                            $transaction->crateQty   = $this->getRequest()->getPost('pack_crate_qty');
                        } else {
                            $transaction->crateQty   = 0;
                        }

                        $currentResult            = $this->minder->doTransactionResponse($transaction);
                        $result                   = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('carrier') != $this->view->grnObj->carrier) {

                        $transaction               = new Transaction_UGCAA();
                        $transaction->grnId        = $this->view->grnObj->grn;
                        $transaction->carrierId    = $this->getRequest()->getPost('carrier');

                        $currentResult            = $this->minder->doTransactionResponse($transaction);
                        $result                   = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('return_id') != $this->view->grnObj->returnId) {

                        $transaction = new Transaction_UGRIA();
                        $transaction->grnId    = $this->view->grnObj->grn;
                        $transaction->returnId = $this->getRequest()->getPost('return_id');
                        $currentResult            = $this->minder->doTransactionResponse($transaction);
                        $result                   = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('awb_consignment_no') != $this->view->grnObj->awbConsignmentNo) {

                        $transaction = new Transaction_UGCNA();
                        $transaction->grnId        = $this->view->grnObj->grn;
                        $transaction->awbconnoteNo = $this->getRequest()->getPost('awb_consignment_no');
                        $currentResult            = $this->minder->doTransactionResponse($transaction);
                        $result                   = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('owner_id') != $this->view->grnObj->ownerId) {

                        $transaction = new Transaction_UGONA();
                        $transaction->grnId   = $this->view->grnObj->grn;
                        $transaction->ownerId = $this->getRequest()->getPost('owner_id');
                        $currentResult            = $this->minder->doTransactionResponse($transaction);
                        $result                   = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('container_no') != $this->view->grnObj->containerNo ||
                    $this->getRequest()->getPost('ship_container_type') != $this->view->grnObj->shipContainerType) {

                        $transaction = new Transaction_UGSNA();
                        $transaction->grnId         = $this->view->grnObj->grn;
                        $transaction->containerNo   = $this->getRequest()->getPost('container_no');
                        $transaction->containerType = $this->getRequest()->getPost('ship_container_type');
                        $currentResult            = $this->minder->doTransactionResponse($transaction);
                        $result                   = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage('UGSNA failed ' . $this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('shipped_date') != $this->view->grnObj->shippedDate) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPED_DATE', $this->getRequest()->getPost('shipped_date'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage('Shipped date update failed ' . $this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('order_line_no') != $this->view->grnObj->orderLineNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'ORDER_LINE_NO', $this->getRequest()->getPost('order_line_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('receipt_flag') != $this->view->grnObj->receiptFlag) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'RECEIPT_FLAG', $this->getRequest()->getPost('receipt_flag'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('comments') != $this->view->grnObj->comments) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'COMMENTS', $this->getRequest()->getPost('comments'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('grn_type') != $this->view->grnObj->grnType) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_TYPE', $this->getRequest()->getPost('grn_type'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('total_qty_packs') != $this->view->grnObj->totalQtyPacks) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'TOTAL_QTY_PACKS', $this->getRequest()->getPost('total_qty_packs'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('pack_ean_ssc') != $this->view->grnObj->packEanSscc) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'PACK_EAN_SSCC', $this->getRequest()->getPost('pack_ean_ssc'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('vehicle_id') != $this->view->grnObj->vehicleId) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'VEHICLE_ID', $this->getRequest()->getPost('vehicle_id'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('delivery_docket') != $this->view->grnObj->deliveryDocket) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'DELIVERY_DOCKET', $this->getRequest()->getPost('delivery_docket'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('delivery_docket') != $this->view->grnObj->deliveryDocket) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'DELIVERY_DOCKET', $this->getRequest()->getPost('delivery_docket'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('grn_printed') != $this->view->grnObj->grnPrinted) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_PRINTED', $this->getRequest()->getPost('grn_printed'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('user_id') != $this->view->grnObj->userId) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'USER_ID', $this->getRequest()->getPost('user_id'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('device_id') != $this->view->grnObj->deviceId) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'DEVICE_ID', $this->getRequest()->getPost('device_id'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('grn_date') != $this->view->grnObj->grnDate) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_DATE', $this->getRequest()->getPost('grn_date'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('order_no') != $this->view->grnObj->orderNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'ORDER_NO', $this->getRequest()->getPost('order_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('order_no') != $this->view->grnObj->orderNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'ORDER_NO', $this->getRequest()->getPost('order_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('last_update') != $this->view->grnObj->lastUpdateDate) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_UPDATE_DATE', $this->getRequest()->getPost('last_update'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('last_line_no') != $this->view->grnObj->lastLineNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_LINE_NO', $this->getRequest()->getPost('last_line_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('last_pallet_no') != $this->view->grnObj->lastPalletNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_PALLET_NO', $this->getRequest()->getPost('last_pallet_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('container_type') != $this->view->grnObj->containerType) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'CONTAINER_TYPE', $this->getRequest()->getPost('container_type'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('shipping_crate_owner') != $this->view->grnObj->shippingCrateOwner) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPING_CRATE_OWNER', $this->getRequest()->getPost('shipping_crate_owner'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('shipping_crate_type') != $this->view->grnObj->shippingCrateType) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPING_CRATE_TYPE', $this->getRequest()->getPost('shipping_crate_type'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('shipping_crate_qty') != $this->view->grnObj->shippingCrateQty) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPING_CRATE_QTY', $this->getRequest()->getPost('shipping_crate_qty'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('due_date') != $this->view->grnObj->grnDueDate) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_DUE_DATE', $this->getRequest()->getPost('due_date'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('status') != $this->view->grnObj->grnStatus) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_STATUS', $this->getRequest()->getPost('status'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('freight_forwarder') != $this->view->grnObj->grnFreightForwarder) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_FREIGHT_FORWARDER', $this->getRequest()->getPost('freight_forwarder'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('legacy_internal_id') != $this->view->grnObj->grnLegacyInternalId) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_LEGACY_INTERNAL_ID', $this->getRequest()->getPost('legacy_internal_id'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('legacy_memo') != $this->view->grnObj->grnLegacyMemo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_LEGACY_MEMO', $this->getRequest()->getPost('legacy_memo'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('wh_id') != $this->view->grnObj->whId) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'WH_ID', $this->getRequest()->getPost('wh_id'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('posting_period') != $this->view->grnObj->grnPostingPeriod) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_POSTING_PERIOD', $this->getRequest()->getPost('posting_period'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('last_lot_no') != $this->view->grnObj->lastLotNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_LOT_NO', $this->getRequest()->getPost('last_lot_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('freight_account_no') != $this->view->grnObj->grnFreightAccountNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_FREIGHT_ACCOUNT_NO', $this->getRequest()->getPost('freight_account_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('vessel_name') != $this->view->grnObj->vesselName) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'VESSEL_NAME', $this->getRequest()->getPost('vessel_name'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('voyage_no') != $this->view->grnObj->voyageNo) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'VOYAGE_NO', $this->getRequest()->getPost('voyage_no'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('other1') != $this->view->grnObj->other1) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'OTHER1', $this->getRequest()->getPost('other1'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    if ($this->getRequest()->getPost('other2') != $this->view->grnObj->other2) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'OTHER2', $this->getRequest()->getPost('other2'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('other3') != $this->view->grnObj->other3) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'OTHER3', $this->getRequest()->getPost('other3'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    
                    if ($this->getRequest()->getPost('grn_status') != $this->view->grnObj->grnStatus) {
                        $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_STATUS', $this->getRequest()->getPost('grn_status'));
                        $result        = $result && $currentResult;
                        If (false == $currentResult) {
                            $this->view->flashMessenger->addMessage($this->minder->lastError);
                        }
                    }
                    
                    if ($result) {
                        $this->view->flashMessenger->addMessage('Record ' . $this->view->grnObj->grn . ' updated successfully');
                    }
                    break;
            }

            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('show',
                                     'grn',
                                     'receipts',
                                     array('row_id' => $rowId));
        }

        $tempArray = $this->minder->getGrnTypeList();
        if (!array_key_exists($this->view->grnObj->grnType, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->grnType => $this->view->grnObj->grnType), $tempArray);
        }
        $this->view->grnTypeList =  $tempArray;
        //$this->view->grnTypeList       = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'),$this->minder->getGRNTypeList()
        //);

        $tempArray = $this->minder->getPalletOwnerList();
        if (!array_key_exists($this->view->grnObj->palletsYn, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->palletsYn => $this->view->grnObj->palletsYn), $tempArray);
        }
        $this->view->palletOwnerList =  $tempArray;
        //$this->view->palletOwnerList   = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'),
        //$this->minder->getPalletOwnerList());

        $tempArray = $this->minder->getContainerTypeList();
        if (!array_key_exists($this->view->grnObj->shipContainerType, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->shipContainerType => $this->view->grnObj->shipContainerType), $tempArray);
        }
        $this->view->containerTypeList =  $tempArray;
        //$this->view->containerTypeList = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'),
        //$this->minder->getContainerTypeList());

        $tempArray = $this->minder->getPersonList(array('CO','CS','CU','RP'), null, 2);
        if (count($tempArray) > 0 ) {
            $tempArray = array_combine(array_keys($tempArray), array_keys($tempArray));
        } else {
            $tempArray = array();
        }
        if (!array_key_exists($this->view->grnObj->returnId, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->returnId => $this->view->grnObj->returnId), $tempArray);
        }
        $this->view->personList        = $tempArray;
        //$this->view->personList        = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'), $tempArray);

        $tempArray = $this->minder->getCompanyList();
        if (!array_key_exists($this->view->grnObj->ownerId, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->ownerId => $this->view->grnObj->ownerId), $tempArray);
        }
        $this->view->companyList = $tempArray;
        //$this->view->companyList       = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'), $this->minder->getCompanyList());

        $tempArray = $this->minder->getShipViaList();
        if (!array_key_exists($this->view->grnObj->carrier, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->carrier => $this->view->grnObj->ownerId), $tempArray);
        }
        $this->view->carrierList = $tempArray;
        //$this->view->carrierList       = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'), $this->minder->getShipViaList());

        $tempArray = $this->minder->getPackagingOwnerList();
        if (!array_key_exists($this->view->grnObj->packCrateOwner, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->packCrateOwner => $this->view->grnObj->packCrateOwner), $tempArray);
        }
        $this->view->packOwnerList = $tempArray;

        //$this->view->packOwnerList     = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'),
        //                                             array_combine(array_values($this->minder->getPackagingOwnerList()),
        //                                                           array_values($this->minder->getPackagingOwnerList())));

        $tempArray = $this->minder->getPackagingTypeList();
        if (!array_key_exists($this->view->grnObj->packCrateType, $tempArray)) {
            $tempArray = minder_array_merge(array($this->view->grnObj->packCrateType => $this->view->grnObj->packCrateType), $tempArray);
        }
        $this->view->packTypeList = $tempArray;
        //$this->view->packTypeList      = minder_array_merge(array('NOT_FOUND' => 'NOT_FOUND'),
        //                                             $this->minder->getPackagingTypeList());
        
        $this->view->grnStatusList  =   minder_array_merge(array(' ' => ' '), $this->minder->getOptionsList('GRN_STATUS'));
    }
//edit GRN record block -----------------------------------

//service block -------------------------------------------
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
        $response->firstSelectedRow   = array();
        
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
            $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
            $response->firstSelectedRow   = $rowsModel->getItems(0, 1, false);
            $response->firstSelectedRow   = (count($response->firstSelectedRow) > 0) ? current($response->firstSelectedRow) : array();
            
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function saveChangesAction() {
        $this->_helper->viewRenderer->setNoRender();
        $response = new stdClass();
        $response->errors   = array();
        $response->warnings = array();
        $response->messages = array();
        
        $response->location = $this->view->url(array('action' => 'index', 'controller' => 'grn', 'module' => 'receipts'), null, true);
        
        try {
            $namespace = $this->getRequest()->getParam('namespace', 'none');
            $dataModel = null;
            $screenBuilder = new Minder_SysScreen_Builder();
            
            switch ($namespace) {
                case self::$grnPDModelName : 
                    $dataModel = new Minder_SysScreen_Model_GrnProductDetails();
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
            
            $cretedRecords  = $dataModel->createRecords($rowsToCreate);

            if ($cretedRecords > 0) {
                switch ($namespace) {
                    case self::$grnPDModelName : 
                        $message = $cretedRecords . ' Product Details record(s) was created.';
                        break;
                }
                $response->messages[] = $message;
                $this->addMessage($message);
            }


            $updatedRowsIds = $dataModel->updateRecords($rowsToUpdate);
            
            foreach ($updatedRowsIds as $rowId) {
                switch ($namespace) {
                    case self::$grnPDModelName : 
                        
                        $message = 'Product Detail #' . $rowId . ' was updated.';
                        break;
                }
                $response->messages[] = $message;
                $this->addMessage($message);
                
            }
            
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        echo json_encode($response);
    }
    
    protected function printLabel($labelType) {
        $response                = new stdClass();
        $response->errors        = array();
        $response->warnings      = array();
        $response->messages      = array();
        $printedCount            = 0;
        
        try {
            $request             = $this->getRequest();
            $selectionNamespace  = $request->getParam('selection_namespace', 'default');
            $selectionAction     = $request->getParam('selection_action');
            $selectionController = $request->getParam('selection_controller');
            $rowSelector         = $this->_helper->rowSelector;
            $selectedRowsCount   = $rowSelector->getSelectedCount($selectionNamespace, $selectionAction, $selectionController);
        
            if ($selectedRowsCount < 1) {
                $response->warnings[] = 'No rows selected. Nothing to print.';
                return $response;
            }
        
            $rowsModel           = $rowSelector->getModel($selectionNamespace, $selectionAction, $selectionController);
            $rowsModel->addConditions($rowSelector->getSelectConditions($selectionNamespace, $selectionAction, $selectionController));
            
            switch ($labelType) {
                case 'GRN':
                    $labelData           = $rowsModel->selectGrnLabelData(0, count($rowsModel));
                    $labelPrinter        = Minder_LabelPrinter_Factory::getLabelPrinter('GRN');
                    break;
                case 'ISSN':
                    $labelData           = $rowsModel->selectIssnLabelData(0, count($rowsModel));
                    $labelPrinter        = Minder_LabelPrinter_Factory::getLabelPrinter('ISSN');
                    break;
                case 'PRODUCT_DETAIL':
                    $productLabelType    = $request->getParam('product_label_type', '');
                    $firstLabelQty       = $request->getParam('first_label_qty', 1);
                    $firstLabelTotal     = $request->getParam('first_label_total', 1);
                    $secondLabelQty      = $request->getParam('second_label_qty', 0);
                    $secondLabelTotal    = $request->getParam('second_label_total', 0);
                    $labelData           = $rowsModel->selectProductLabelData(0, count($rowsModel));
                    $labelPrinter        = Minder_LabelPrinter_Factory::getLabelPrinter($productLabelType);

                    if (count($labelData) > 1) 
                        throw new Minder_Exception('Only one Product Label printing at the same time is allowed.');
                    
                    break;
                default:
                    throw new Minder_Exception('1. Cannot print labels for "' . $labelType . '"');
            }
            
            $printerObj          = $this->minder->getPrinter();
            
            foreach ($labelData as $labelDataRow) {

                switch ($labelType) {
                    case 'GRN':
                        $result  = $labelPrinter->directPrint(array($labelDataRow), $printerObj);
                        break;
                    case 'ISSN':
                        $result  = $labelPrinter->directPrint(array($labelDataRow), $printerObj);
                        break;
                    case 'PRODUCT_DETAIL':
                        $labelDataRow['PACK_QTY']       = $firstLabelQty;
                        $labelDataRow['TOTAL_ON_LABEL'] = $firstLabelTotal;
                        $labelDataRow['labelqty']       = $labelDataRow['PACK_QTY'];
                        $result                         = $labelPrinter->directPrint(array($labelDataRow), $printerObj);

                        if($result->hasErrors())
                            throw new Minder_Exception(implode('. ', $result->errors));

                        if (is_numeric($secondLabelQty) && $secondLabelQty > 0) {
                            $labelDataRow['PACK_QTY']       = $secondLabelQty;
                            $labelDataRow['TOTAL_ON_LABEL'] = $secondLabelTotal;
                            $labelDataRow['labelqty']       = $labelDataRow['PACK_QTY'];
                            $result                         = $labelPrinter->directPrint(array($labelDataRow), $printerObj);

                            if($result->hasErrors())
                                throw new Minder_Exception(implode('. ', $result->errors));

                            $printedCount++;
                        }
                        break;
                    
                    default:
                        throw new Minder_Exception('2. Cannot print labels for "' . $labelType . '"');
                }

                if($result->hasErrors())
                    throw new Minder_Exception(implode('. ', $result->errors));

                $printedCount++;
            }
        } catch (Exception $e) {
            $response->errors[]  = $e->getMessage();
            Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $e->getMessage(), $e->getTrace()));
        }
        
        if ($printedCount > 0) {
            $response->messages[] = 'Print request successfully sent for ' . $printedCount . ' labels.';
        }
        
        return $response;
    }
    
    public function printGrnLabelAction () {
        $this->_helper->viewRenderer->setNoRender();
        $response = $this->printLabel('GRN');
        echo json_encode($response);

    }
    
    public function printProductLabelAction () {
        $this->_helper->viewRenderer->setNoRender();
        $response = $this->printLabel('PRODUCT_DETAIL');
        echo json_encode($response);
    }

    public function printIssnLabelAction() {
        $this->_helper->viewRenderer->setNoRender();
        $response = $this->printLabel('ISSN');
        echo json_encode($response);
    }

    public function updateProductCodeAction() {
        try {
            $this->view->errors = array();
            $this->view->messages = array();
            /**
             * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
             */
            $rowSelector           = $this->_helper->getHelper('RowSelector');

            /**
             * @var Minder_SysScreen_Model_GrnLine $grnLineModel
             */
            $grnLineModel = $rowSelector->getModel(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController);

            if ($rowSelector->hasSelected(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController)) {
                $grnLineModel->addConditions($rowSelector->getSelectConditions(self::$grnLinesModelName, self::$grnLinesSelectionAction, self::$grnLinesSelectionController));
            } else {
                $grnLineModel->addConditions(array('1=2' => array()));
            }

            $tmpLines = array();
            $grnLine = $grnLineModel->selectDataForGnnpTransaction(0, 1000);
            $grnLineCount = 0;

            foreach ($grnLine as $line) {
                $tmpLines[$line['GRN']] = (isset($tmpLines[$line['GRN']])) ? $tmpLines[$line['GRN']] : array();

                if (!isset($tmpLines[$line['GRN']][$line['PROD_ID']])) {
                    $grnLineCount++;
                    $tmpLines[$line['GRN']][$line['PROD_ID']] = $line['PROD_ID'];
                }
            }
            reset($grnLine);
            $grnLine = current($grnLine);

            if (empty($grnLine) || empty($grnLine['PROD_ID'])) {
                $this->addError('No PROD_ID selected.');
                $this->_forward('get-change-prod-id');
                return;
            }

            if ($grnLineCount > 1) {
                $this->addError('Cannot update more then one GRN_LINE.');
                $this->_forward('get-change-prod-id');
                return;
            }

            if (!$rowSelector->hasSelected(self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController)) {
                $this->addError('No rows selected.');
                $this->_forward('get-change-prod-id');
                return;
            }

            /**
             * @var Minder_SysScreen_Model_ChangeProdId $dataModel
             */
            $dataModel = $rowSelector->getModel(self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController);
            $dataModel->addConditions($rowSelector->getSelectConditions(self::$grnCPIDModelName, self::$grnCPIDSelectionAction, self::$grnCPIDSelectionController));

            $result = $dataModel->changeProdId($grnLine['GRN'], $grnLine['ORDER_NO'], $grnLine['ORDER_LINE_NO'], $grnLine['ORIGINAL_QTY'], $grnLine['PROD_ID']);

            if ($result->hasErrors()) {
                $this->addError($result->errors);
            }

            $this->addMessage($result->messages);

        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }

        $this->_forward('get-change-prod-id');
    }
//service block -------------------------------------------

}
