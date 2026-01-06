<?php
/**
 * @throws Minder_Exception
 * @deprecated
 */
class Picking_WaitingPickingController extends Minder_Controller_Action_Picking 
{
    public function init() {
        parent::init();
        
        $this->view->orderSysScreenName       = 'WAITPICKING';
        $this->view->orderSelectionNamespace  = 'order_wait_picking_selection_namespace';
        $this->view->orderSelectionAction     = 'select-rows';
        $this->view->orderSelectionController = 'service';
        $this->view->orderSelectionModule     = 'picking';
        
        $this->view->orderReportAction        = 'report';
        $this->view->orderReportController    = 'service';
        $this->view->orderReportModule        = 'picking';
        
        $this->view->linesSysScreenName       = 'WAITPICKINGLINES';
        $this->view->linesSelectionNamespace  = 'lines_wait_picking_selection_namespace';
        $this->view->linesSelectionAction     = 'select-rows';
        $this->view->linesSelectionController = 'service';
        $this->view->linesSelectionModule     = 'picking';
        
        $this->view->linesReportAction        = 'report';
        $this->view->linesReportController    = 'service';
        $this->view->linesReportModule        = 'picking';
        
        $this->view->allocatingLimitInstanceId = 'wait_picking_allocating_limits';
        
        $this->view->pageTitle = 'WAITING PICKING ORDERS';
    }

    public function indexAction() {
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->errors   = $flashMessenger->setNamespace('errors')->getMessages();
        $this->view->warnings = $flashMessenger->setNamespace('warnings')->getMessages();
        $this->view->messages = $flashMessenger->setNamespace('messages')->getMessages();
        
        $flashMessenger->setNamespace('errors')->clearMessages();
        $flashMessenger->setNamespace('warnings')->clearMessages();
        $flashMessenger->setNamespace('messages')->clearMessages();
        
        $this->_preProcessNavigation();
        
        $request = $this->getRequest();
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $formAction = $request->getParam('SEARCH_FORM_ACTION', 'none');
        try {
            $orderModel                   = new Minder_SysScreen_Model_WaitingPicking();
            $screenBuilder                = new Minder_SysScreen_Builder();
            $orderModel                   = $screenBuilder->buildSysScreenModel($this->view->orderSysScreenName, $orderModel);
            list($searchFields, $actions) = $screenBuilder->buildSysScreenSearchFields($this->view->orderSysScreenName);

            $searchKeeper  = $this->_helper->getHelper('SearchKeeper');
            
            switch (strtolower($formAction)) {
                case 'search': 
                    $searchFields = $searchKeeper->makeSearch($searchFields);
                    break;
                default:
                    $searchFields = $searchKeeper->getSearch($searchFields);
            }
            $orderModel->setConditions($orderModel->makeConditionsFromSearch($searchFields));
            
            $this->view->searchFields = $searchFields;
        
            $rowSelector = $this->_helper->getHelper('RowSelector');
            $rowSelector->setRowSelection(
                'select_complete', 
                'init', 
                null, 
                null, 
                $orderModel, 
                true, 
                $this->view->orderSelectionNamespace, 
                $this->view->orderSelectionAction,
                $this->view->orderSelectionController
            );
            
            $this->view->totalOrders         = count($orderModel);
            $this->view->selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $this->_postProcessNavigation(array('total' => $this->view->totalOrders));
            $pageSelector                    = $this->view->navigation['pageselector'];
            $showBy                          = $this->view->navigation['show_by'];

            $this->view->orders                          = $orderModel->getItems($pageSelector*$showBy, $showBy, false);
            $this->view->selectedOrders                  = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            list($this->view->fields, $this->view->tabs) = $screenBuilder->buildSysScreenSearchResult($this->view->orderSysScreenName);
            $this->view->actions                         = $actions;
            $this->view->selectMode                      = $rowSelector->getSelectionMode('', $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            
            $pickModes = $this->minder->getPickModes();
            $this->view->pickModes = $pickModes['data'];

            $this->view->resultsForm = $this->view->getHelper('SysScreenSearchResult');
            $this->view->resultsForm->setTabs($this->view->tabs)->setFields($this->view->fields);

            list($this->view->orderScreenButtons) = $screenBuilder->buildScreenButtons($this->view->orderSysScreenName);
            usort($this->view->orderScreenButtons, create_function('$a, $b', 'return $a[$a["ORDER_BY_FIELD_NAME"]] - $b[$b["ORDER_BY_FIELD_NAME"]];'));
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
    }    
    
    public function getLinesAction() {
        $this->view->errors   = array();
        $this->view->warnings = array();
        $this->_preProcessNavigation();
        
        $request = $this->getRequest();
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $screenBuilder = new Minder_SysScreen_Builder();
        $this->view->lines = array();
        $this->view->totalLines = 0;
        $this->view->selectedLines = array();
        $this->view->pageTitle = 'Lines List';
        try {
            $rowSelector = $this->_helper->getHelper('RowSelector');
            
            $selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $linesModel          = $screenBuilder->buildSysScreenModel($this->view->linesSysScreenName, new Minder_SysScreen_Model_WaitingPickingLine());

            if ($selectedOrdersCount > 0) {
                $totalOrders    = $rowSelector->getTotalCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                $selectedOrders = $rowSelector->getSelected(0, $totalOrders, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                
                $tmpOrderKeys   = array();
                foreach ($selectedOrders as $row) {
                    $tmpOrderKeys[] = $row['PICK_ORDER'];
                }
                
                $tmpCondString = 'PICK_ITEM.PICK_ORDER IN (' . substr(str_repeat('?, ', count($tmpOrderKeys)), 0, -2) . ')';
                
                $linesModel->setConditions(array($tmpCondString => $tmpOrderKeys));
                $rowSelector->setRowSelection(
                    'select_complete', 
                    'init', 
                    null, 
                    null, 
                    $linesModel, 
                    true, 
                    $this->view->linesSelectionNamespace, 
                    $this->view->linesSelectionAction,
                    $this->view->linesSelectionController
                );
            
                $this->view->totalLines         = count($linesModel);
                $this->view->selectedLinesCount = $rowSelector->getSelectedCount($this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
                $this->_postProcessNavigation(array('total' => $this->view->totalLines));
                $pageSelector                   = $this->view->navigation['pageselector'];
                $showBy                         = $this->view->navigation['show_by'];

                $this->view->lines              = $linesModel->getItems($pageSelector*$showBy, $showBy, false);
                $this->view->selectedLines      = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
                
                $this->view->prodCodesTotal     = $linesModel->getProductCodesCount();
                $this->view->issnsTotal         = $linesModel->getISSNsCount();
                
                $this->view->orderLinesSelected = 0;
                $this->view->prodCodesSelected  = 0;
                $this->view->issnsSelected      = 0;
                $this->view->selectMode         = $rowSelector->getSelectionMode('', $this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController);
                
                if ($this->view->selectedLinesCount > 0) {
                    $linesModel->addConditions($rowSelector->getSelectConditions($this->view->linesSelectionNamespace, $this->view->linesSelectionAction, $this->view->linesSelectionController));
                    $this->view->orderLinesSelected = $linesModel->getOrderLinesCount();
                    $this->view->prodCodesSelected  = $linesModel->getProductCodesCount();
                    $this->view->issnsSelected      = $linesModel->getISSNsCount();
                    $nonOPStatus                    = $linesModel->selectNonOPStatus(0, count($linesModel));
                    
                    if (count($nonOPStatus) > 0) 
                        $this->view->errors[] = 'Cannot allocate lines with status ("' . implode('", "', $nonOPStatus) . '")';
                }
            
            } else {
                $linesModel->addConditions(array('1=2' => array()));
                $rowSelector->setRowSelection(
                    'select_complete',
                    'init',
                    null,
                    null,
                    $linesModel,
                    true,
                    $this->view->linesSelectionNamespace,
                    $this->view->linesSelectionAction,
                    $this->view->linesSelectionController
                );
                $this->_postProcessNavigation(array('total' => 0));
            }
            list($this->view->fields, $this->view->tabs) = $screenBuilder->buildSysScreenSearchResult($this->view->linesSysScreenName);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
    }
    
    public function holdAction() {
        $rowSelector = $this->_helper->getHelper('RowSelector');
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        
        $response = new stdClass();
        $response->status = 'failed';
        $response->redirect = '';
        $response->errors = array();
        $response->messages = array();
        $response->warnings = array();
        
        try {
        
            $totalOrders    = $rowSelector->getTotalCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, null, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
        
            foreach ($selectedOrders as $order) {
                if (!$this->minder->pickOrderHold($order['PICK_ORDER'])) {
                    $flashMessenger->setNamespace('errors')->addMessage('Order ' . $order['PICK_ORDER'] . ' was not held.');
                } else {
                    $flashMessenger->setNamespace('messages')->addMessage('Order ' . $order['PICK_ORDER'] . ' was held successfully.');
                }
            }
            $response->errors = $flashMessenger->setNamespace('errors')->getCurrentMessages();
            $response->messages = $flashMessenger->setNamespace('messages')->getCurrentMessages();
            $response->warnings = $flashMessenger->setNamespace('warnings')->getCurrentMessages();
            $response->redirect = $this->view->url(array('module' => 'picking', 'controller' => 'waiting-picking'), null, true);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function cancelAction() {
        $rowSelector = $this->_helper->getHelper('RowSelector');
        $flashMessenger = $this->_helper->getHelper('FlashMessenger');
        
        $response = new stdClass();
        $response->status = 'failed';
        $response->redirect = '';
        $response->errors = array();
        $response->messages = array();
        $response->warnings = array();
        
        $reason = $this->getRequest()->getParam('cancel_reason');
        if (empty($reason)) {
            $response->errors[] = 'Please enter a reason for cancelling.';
            $this->_helper->viewRenderer->setNoRender();
            echo json_encode($response);
            return;
        }
        
        try {
        
            $totalOrders    = $rowSelector->getTotalCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, null, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
        
            foreach ($selectedOrders as $order) {
                if (!$this->minder->pickOrderCancel($order['PICK_ORDER'], $reason)) {
                    $flashMessenger->setNamespace('errors')->addMessage('Order ' . $order['PICK_ORDER'] . ' was not canceled.');
                } else {
                    $flashMessenger->setNamespace('messages')->addMessage('Order ' . $order['PICK_ORDER'] . ' was canceled.');
                }
            }
            $response->errors = $flashMessenger->setNamespace('errors')->getCurrentMessages();
            $response->messages = $flashMessenger->setNamespace('messages')->getCurrentMessages();
            $response->warnings = $flashMessenger->setNamespace('warnings')->getCurrentMessages();
            $response->redirect = $this->view->url(array('module' => 'picking', 'controller' => 'waiting-picking'), null, true);
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    public function changePriorityAction() {
        $mode                  = $this->getRequest()->getParam('mode');
        $response              = new stdClass();
        $response->errors      = array();
        $response->warnings    = array();
        $response->messages    = array();
        $response->updatedRows = array();
        
        try {
            $rowSelector = $this->_helper->rowSelector;
            $selectedCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            if ($selectedCount < 1) {
                throw new Minder_Exception('Select Order first.');
            }
            
            $totalOrders    = $rowSelector->getTotalCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $selectedOrders = $rowSelector->getSelected(0, $totalOrders, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            
            foreach ($selectedOrders as $rowId => $row) {
                $order = $this->minder->getPickOrder($row['PICK_ORDER'], '');
                
                switch (strtolower($mode)) {
                    case 'inc':
                        $order->pickPriority++; 
                        break;
                    case 'dec':
                        if ($order->pickPriority > 1) {
                            $order->pickPriority--;
                        } else {
                            continue 2;
                        }
                        break;
                    default:
                        throw new Minder_Exception("Unsupported mode: '$mode'");
                }
                if ($this->minder->updatePickOrder($order)) {
                    $order = $this->minder->getPickOrder($row['PICK_ORDER'], '');
                    $response->updatedRows[$rowId] = $order->pickPriority;
                }
            }
        } catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }

        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
    
    protected function _setupShortcuts() {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('picking')->buildMinderMenuArray();

        return $this;
    }
}

