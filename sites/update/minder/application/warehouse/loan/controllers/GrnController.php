<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/*
@todo: join dynamic type sorting with index.phtml javascript code
@todo: apply descending sort on 1st column.
*/

/**
 * Warehouse_GrnController
 *
 * Action controller
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 * 
 * @deprecated use Receipts_GrnController instead
 */
class Warehouse_GrnController extends Minder_Controller_Action
{
    /**
     * Index action. Show GRN list, process sorting and filtering data
     *
     * @return void
     */
    public function indexAction()
    {   
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        $log->info('Required fields ' . implode(", ", $listOfFields) . ' is EMPTY');
        $this->view->pageTitle = 'Search GRN';
        $this->_preProcessNavigation();
        
        $searchInputs = array();
        $allowed      = array();
        $this->view->tabList    =   array();
        $this->view->headers    =   array();
        $this->view->editInputs =   array();
        
        try {
            list($searchInputs, $allowed) = $this->minder->getSearchInputs2('GRN');
//            $this->view->reportButtonList       = $this->minder->getReportButtonList();
            $this->view->tabList                = $this->minder->getTabList('GRN');
            $this->view->headers                = $this->minder->getHeadersForTabList('GRN', $this->view->tabList);
            $this->view->editInputs             = $this->minder->getEditInputs2('GRN');
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
        
        $thisIsSearch = ('search' == strtolower($this->getRequest()->getParam('form_action', 'none'))) ? true : false;
        list($searchInputs, $allowed) = $this->_saveSearchedDLValue($searchInputs, $allowed, NULL, NULL, $thisIsSearch);
        $conditions                   = $this->_makeConditions($allowed);
        $this->view->searchInputs     = $this->_saveSearchedValue($searchInputs, $conditions);   
        $clause                       = $this->_makeClause($conditions, $allowed);
        
        $pageSelector = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
        $showBy       = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
        
        try{
            $grns             = $this->minder->getGrns($clause, 'show', $pageSelector, $showBy );
            $this->view->grns = $grns['data'];   
        } catch(Exception $ex){
            $this->addError($ex->getMessage());
        }
     
        // Save data array in view.
        $data        = array();
        $conditions  = $this->_getConditions('calc');
        $selectedNum = 0;
        foreach($grns['data'] as $line) {
            if (array_search($line->id, $conditions)) {
                $data[$line->id] = $line->items;
                $selectedNum++;
            }
        }
        $this->view->selectedNum = $selectedNum;
        $this->view->data        = $data;
        
        if ($this->_processReportTo()) {
            return;
        }

        $action =   isset($_POST['action']) ? $_POST['action'] : '';
        switch(strtoupper($action)){
            case 'PRINT LABEL':
                $conditions =   $this->_getConditions('calc');
                $printerObj =   $this->minder->getPrinter();
                $count      =   0;
                        
                foreach($grns['data'] as $key => $value){
                    if(false !== array_search($value->items['GRN'], $conditions)) {
                         try{
                            $result    =    $printerObj->printGrnLabel($value->items);
                            if($result['RES'] < 0){
                                 $this->view->flashMessenger->addMessage('Error while print label(s): ' . $result['ERROR_TEXT']);
                                break;     
                            }             
                         } catch(Exception $ex){
                                $this->view->flashMessenger->addMessage($ex->getMessage());
                               break;    
                         }
                         $count++;
                    }
                }
                if($result['RES'] >= 0){
                    $this->view->flashMessenger->addMessage($count . ' label(s) printed successfully');
                }
                
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(303)
                                  ->goto('index',
                                         'grn',
                                         'warehouse'
                                        );
                break;
        }
  
        $this->_postProcessNavigation($grns);
        
        $this->view->conditions =   $this->_getConditions() + $this->_getConditions('calc');
    
        $log->info('End of ' .  __FUNCTION__);
    }

    /**
     * Show detailed info about object selected in Index
     *
     * @return void
     */
    public function showAction()
    {
        if (count($this->getRequest()->getPost('action')) > 0) {
            switch (strtolower($this->getRequest()->getPost('action'))) {
                case 'edit':
                    $this->_helper->redirector('edit', 'grn', 'warehouse', array(
                        'grn_no' => $this->getRequest()->getParam('grn_no')));
                    break;
                case 'return':
                    $this->_helper->redirector('index', 'grn', 'warehouse');
                    break;
            }
        }
        $this->view->pageTitle  = 'Show';
        $data                   = $this->minder->getGrns(array('GRN = ? AND ' => $this->getRequest()->getParam('grn_no')), 'edit');
        
        $this->view->grnObj     = current($data['data']);
        
        
    }

    /**
     * Perform edit and save grnLine object
     *
     * @return void
     */
    public function editAction()
    {   
        $this->view->pageTitle  = 'edit';
        $data                   = $this->minder->getGrns(array('GRN = ? AND ' => $this->getRequest()->getParam('grn_no')), 'edit');
        $this->view->grnObj     = current($data['data']);

        if (count($this->getRequest()->getPost('action')) > 0) {
            switch (strtolower($this->getRequest()->getPost('action'))) {
                case 'save':
                    $result = true;
                    
                    if(array_key_exists('PALLETS_YN', $this->view->grnObj->items) || array_key_exists('GRN_PALLET_QTY', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('pallets_yn') != $this->view->grnObj->palletsYn ||
                            $this->getRequest()->getPost('grn_pallet_qty') != $this->view->grnObj->grnPalletQty) {
                            
                                /*if ( $this->getRequest()->getPost('pallet_owner') != 'NOT_FOUND'
                             && $this->getRequest()->getPost('pallet_owner') != null
                             && $this->getRequest()->getPost('pallet_qty') != null) {*/
                            $transaction = new Transaction_UGHPA();
                            $transaction->grnId       = $this->getRequest()->getParam('grn_no');
                            $transaction->palletQty   = $this->getRequest()->getParam('grn_pallet_qty');
                            if ($this->getRequest()->getPost('pallets_yn') == 'N') {
                                $transaction->palletOwner = 0;
                            } else {
                                $transaction->palletOwner = $this->getRequest()->getParam('pallets_yn');
                            }
                            
                            $currentResult = $this->minder->doTransactionResponse($transaction);
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }

                    if(array_key_exists('PACK_CRATE_OWNER', $this->view->grnObj->items) || array_key_exists('PACK_CRATE_TYPE', $this->view->grnObj->items) || array_key_exists('PACK_CRATE_QTY', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('pack_crate_owner') != $this->view->grnObj->packCrateOwner ||
                            $this->getRequest()->getPost('pack_crate_type') != $this->view->grnObj->packCrateType ||
                            $this->getRequest()->getPost('pack_crate_qty') != $this->view->grnObj->packCrateQty) {

                            $transaction             = new Transaction_UGHCA();
                            $transaction->grnId      = $this->getRequest()->getParam('grn_no');
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
                    }
                    
                    if(array_key_exists('CARRIER', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('carrier') != $this->view->grnObj->carrier) {

                            $transaction               = new Transaction_UGCAA();
                            $transaction->grnId        = $this->getRequest()->getParam('grn_no');
                            $transaction->carrierId    = $this->getRequest()->getPost('carrier');

                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            $result                   = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('RETURN_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('return_id') != $this->view->grnObj->returnId) {

                            $transaction = new Transaction_UGRIA();
                            $transaction->grnId    = $this->getRequest()->getParam('grn_no');
                            $transaction->returnId = $this->getRequest()->getPost('return_id');
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            $result                   = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('AWB_CONSIGNMENT_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('awb_consignment_no') != $this->view->grnObj->awbConsignmentNo) {

                            $transaction = new Transaction_UGCNA();
                            $transaction->grnId        = $this->getRequest()->getParam('grn_no');
                            $transaction->awbconnoteNo = $this->getRequest()->getPost('awb_consignment_no');
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            $result                   = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('OWNER_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('owner_id') != $this->view->grnObj->ownerId) {

                            $transaction = new Transaction_UGONA();
                            $transaction->grnId   = $this->getRequest()->getParam('grn_no');
                            $transaction->ownerId = $this->getRequest()->getPost('owner_id');
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            $result                   = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('CONTAINER_NO', $this->view->grnObj->items) || array_key_exists('SHIP_CONTAINER_TYPE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('container_no') != $this->view->grnObj->containerNo ||
                            $this->getRequest()->getPost('ship_container_type') != $this->view->grnObj->shipContainerType) {

                            $transaction = new Transaction_UGSNA();
                            $transaction->grnId         = $this->getRequest()->getParam('grn_no');
                            $transaction->containerNo   = $this->getRequest()->getPost('container_no');
                            $transaction->containerType = $this->getRequest()->getPost('ship_container_type');
                            $currentResult            = $this->minder->doTransactionResponse($transaction);
                            $result                   = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage('UGSNA failed ' . $this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('SHIPPED_DATE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('shipped_date') != $this->view->grnObj->shippedDate) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPED_DATE', $this->getRequest()->getPost('shipped_date'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage('Shipped date update failed ' . $this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('ORDER_LINE_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('order_line_no') != $this->view->grnObj->orderLineNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'ORDER_LINE_NO', $this->getRequest()->getPost('order_line_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('RECEIPT_FLAG', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('receipt_flag') != $this->view->grnObj->receiptFlag) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'RECEIPT_FLAG', $this->getRequest()->getPost('receipt_flag'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('COMMENTS', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('comments') != $this->view->grnObj->comments) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'COMMENTS', $this->getRequest()->getPost('comments'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_TYPE', $this->view->grnObj->items)){
                        if ($this->getRequest()->getPost('grn_type') != $this->view->grnObj->grnType) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_TYPE', $this->getRequest()->getPost('grn_type'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('TOTAL_QTY_PACKS', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('total_qty_packs') != $this->view->grnObj->totalQtyPacks) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'TOTAL_QTY_PACKS', $this->getRequest()->getPost('total_qty_packs'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('PACK_EAN_SSCC', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('pack_ean_ssc') != $this->view->grnObj->packEanSscc) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'PACK_EAN_SSCC', $this->getRequest()->getPost('pack_ean_ssc'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('VEHICLE_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('vehicle_id') != $this->view->grnObj->vehicleId) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'VEHICLE_ID', $this->getRequest()->getPost('vehicle_id'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('DELIVERY_DOCKET', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('delivery_docket') != $this->view->grnObj->deliveryDocket) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'DELIVERY_DOCKET', $this->getRequest()->getPost('delivery_docket'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('DELIVERY_DOCKET', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('delivery_docket') != $this->view->grnObj->deliveryDocket) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'DELIVERY_DOCKET', $this->getRequest()->getPost('delivery_docket'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_PRINTED', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_printed') != $this->view->grnObj->grnPrinted) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_PRINTED', $this->getRequest()->getPost('grn_printed'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('USER_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('user_id') != $this->view->grnObj->userId) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'USER_ID', $this->getRequest()->getPost('user_id'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('DEVICE_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('device_id') != $this->view->grnObj->deviceId) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'DEVICE_ID', $this->getRequest()->getPost('device_id'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_DATE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_date') != $this->view->grnObj->grnDate) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_DATE', $this->getRequest()->getPost('grn_date'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('ORDER_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('order_no') != $this->view->grnObj->orderNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'ORDER_NO', $this->getRequest()->getPost('order_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('LAST_UPDATE_DATE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('last_update_date') != $this->view->grnObj->lastUpdateDate) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_UPDATE_DATE', $this->getRequest()->getPost('last_update_date'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('LAST_LINE_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('last_line_no') != $this->view->grnObj->lastLineNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_LINE_NO', $this->getRequest()->getPost('last_line_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('LAST_PALLET_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('last_pallet_no') != $this->view->grnObj->lastPalletNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_PALLET_NO', $this->getRequest()->getPost('last_pallet_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('CONTAINER_TYPE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('container_type') != $this->view->grnObj->containerType) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'CONTAINER_TYPE', $this->getRequest()->getPost('container_type'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('SHIPPING_CRATE_OWNER', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('shipping_crate_owner') != $this->view->grnObj->shippingCrateOwner) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPING_CRATE_OWNER', $this->getRequest()->getPost('shipping_crate_owner'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('SHIPPING_CRATE_TYPE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('shipping_crate_type') != $this->view->grnObj->shippingCrateType) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPING_CRATE_TYPE', $this->getRequest()->getPost('shipping_crate_type'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('SHIPPING_CRATE_QTY', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('shipping_crate_qty') != $this->view->grnObj->shippingCrateQty) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'SHIPPING_CRATE_QTY', $this->getRequest()->getPost('shipping_crate_qty'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_DUE_DATE', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_due_date') != $this->view->grnObj->grnDueDate) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_DUE_DATE', $this->getRequest()->getPost('grn_due_date'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_STATUS', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_status') != $this->view->grnObj->grnStatus) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_STATUS', $this->getRequest()->getPost('grn_status'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_FREIGHT_FORWARDER', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_freight_forwarder') != $this->view->grnObj->grnFreightForwarder) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_FREIGHT_FORWARDER', $this->getRequest()->getPost('grn_freight_forwarder'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_LEGACY_INTERNAL_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_legacy_internal_id') != $this->view->grnObj->grnLegacyInternalId) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_LEGACY_INTERNAL_ID', $this->getRequest()->getPost('grn_legacy_internal_id'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_LEGACY_MEMO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_legacy_memo') != $this->view->grnObj->grnLegacyMemo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_LEGACY_MEMO', $this->getRequest()->getPost('grn_legacy_memo'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('WH_ID', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('wh_id') != $this->view->grnObj->whId) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'WH_ID', $this->getRequest()->getPost('wh_id'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_POSTING_PERIOD', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_posting_period') != $this->view->grnObj->grnPostingPeriod) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_POSTING_PERIOD', $this->getRequest()->getPost('grn_posting_period'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('LAST_LOT_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('last_lot_no') != $this->view->grnObj->lastLotNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'LAST_LOT_NO', $this->getRequest()->getPost('last_lot_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('GRN_FREIGHT_ACCOUNT_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('freight_account_no') != $this->view->grnObj->grnFreightAccountNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_FREIGHT_ACCOUNT_NO', $this->getRequest()->getPost('freight_account_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('VESSEL_NAME', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('vessel_name') != $this->view->grnObj->vesselName) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'VESSEL_NAME', $this->getRequest()->getPost('vessel_name'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('VOYAGE_NO', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('voyage_no') != $this->view->grnObj->voyageNo) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'VOYAGE_NO', $this->getRequest()->getPost('voyage_no'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('OTHER1', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('other1') != $this->view->grnObj->other1) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'OTHER1', $this->getRequest()->getPost('other1'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('OTHER2', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('other2') != $this->view->grnObj->other2) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'OTHER2', $this->getRequest()->getPost('other2'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                    if(array_key_exists('OTHER3', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('other3') != $this->view->grnObj->other3) {
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'OTHER3', $this->getRequest()->getPost('other3'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
                        }
                    }
                    
                   
                    if(array_key_exists('GRN_STATUS', $this->view->grnObj->items)) {
                        if ($this->getRequest()->getPost('grn_status') != $this->view->grnObj->grnStatus) {
                            
                            $currentResult = $this->minder->updateGrn($this->view->grnObj->grn, 'GRN_STATUS', $this->getRequest()->getPost('grn_status'));
                            $result        = $result && $currentResult;
                            If (false == $currentResult) {
                                $this->view->flashMessenger->addMessage($this->minder->lastError);
                            }
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
                                     'warehouse',
                                     array('grn_no' => $this->view->grnObj->grn));
        }

        $this->view->editInputs     = $this->minder->getEditInputs('GRN');
        
    }

    /**
     * Provide seek and filtering interface for /warehouse/grn/index
     *
     * @return void
     */
    public function seekAction()
    {
        $this->view->data = array();
    }

    public function calcAction()
    {
        $action = $this->getRequest()->getParam('inAction');
        $id = $this->getRequest()->getParam('id');
        $method = $this->getRequest()->getParam('method');
        $value  = $this->getRequest()->getParam('value');
        if (is_null($id)) {
            $id = 'select_all';
        }
        if (is_null($method)) {
            $method = 'init';
        }
        $this->_preProcessNavigation();

        switch (strtolower($action)) {
            default:
                try {
                    list($searchInputs, $allowed) = $this->minder->getSearchInputs2('GRN');
                } catch (Exception $e) {
                    $this->addError($e->getMessage());
                }
                
                $thisIsSearch = ('search' == strtolower($this->getRequest()->getParam('form_action', 'none'))) ? true : false;
                list($searchInputs, $allowed) = $this->_saveSearchedDLValue($searchInputs, $allowed, NULL, 'index', $thisIsSearch);
                $conditions = $this->_getConditions('index');
                $clause     = $this->_makeClause($conditions, $allowed);

                $pageSelector   = $this->session->navigation[$this->_controller][$this->_action]['pageselector'];
                $showBy         = $this->session->navigation[$this->_controller][$this->_action]['show_by'];
            
                try{
                    $lines = $this->minder->getGrns($clause, 'show', $pageSelector, $showBy );
                } catch(Exception $ex) {
                    $this->addError($ex->getMessage());
                }
            break;
        }
        $conditions = $this->_markSelected2($lines['data'], $id, $value, $method, 'calc');
        $numRecords = count($lines['data']);
        
        // Calculate the number of selected items.
        for ($count = 0, $i = 0; $i < $numRecords; $i++) {
            if (false !== array_search($lines['data'][$i]->id, $conditions, true )) {
                $count++;
            }
        }
        $this->_setConditions($conditions);
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode(array('selected_num' => $count, 'errors' => $this->_helper->flashMessenger->setNamespace('errors')->getCurrentMessages()));
        $this->_helper->flashMessenger->setNamespace('errors')->clearCurrentMessages();
    }

    /**
     * Performs lookup for Autocomplete fields
     *
     * @return void
     */
    public function lookupAction()
    {
        $tdata = array();
        switch ($this->getRequest()->getParam('field')) {
            case 'grn':
                $tdata = $this->minder->getGrnNoListFromGrn($this->getRequest()->getParam('q'));
                break;

            case 'order_no':
                $tdata = $this->minder->getOrderNoListFromGrn($this->getRequest()->getParam('q'));
                break;

            case 'awb_consignment_no':
                $tdata = array();
                foreach ($this->minder->getGrns(array(
                    'AWB_CONSIGNMENT_NO = ? AND ' => $this->getRequest()->getParam('q'))) as $key => $line) {
                    $tdata[$line->items['AWB_CONSIGNMENT_NO']] = $line->items['AWB_CONSIGNMENT_NO'];
                }
                break;

            case 'container_no':
                $tdata = array();
                foreach ($this->minder->getGrns(array(
                    'CONTAINER_NO = ? AND ' => $this->getRequest()->getParam('q'))) as $key => $line) {
                    $tdata[$line->items['CONTAINER_NO']] = $line->items['CONTAINER_NO'];
                }
                break;

            default:
                break;
        }
        /*
        if (count($tdata) > 10) {
            $tdata = array_slice($tdata,0, 10, true);
        }
        */
        $this->view->data = $tdata;
    }
}
