<?php

class Picking_WipController extends Minder_Controller_Action_Picking 
{
    public function init() {
        parent::init();
        
        $this->view->orderSysScreenName       = 'WIPPICKING';
        $this->view->orderSelectionNamespace  = 'order_wip_picking_selection_namespace';
        $this->view->orderSelectionAction     = 'select-rows';
        $this->view->orderSelectionController = 'service';
        $this->view->orderSelectionModule     = 'picking';
        
        $this->view->orderReportAction        = 'report';
        $this->view->orderReportController    = 'service';
        $this->view->orderReportModule        = 'picking';
        
        $this->view->linesSysScreenName       = 'WIPPICKINGLINES';
        $this->view->linesSelectionNamespace  = 'lines_wip_picking_selection_namespace';
        $this->view->linesSelectionAction     = 'select-rows';
        $this->view->linesSelectionController = 'service';
        $this->view->linesSelectionModule     = 'picking';
        
        $this->view->linesReportAction        = 'report';
        $this->view->linesReportController    = 'service';
        $this->view->linesReportModule        = 'picking';
        
        $this->view->pageTitle = 'WIP';
    }

    public function indexAction() {
        $this->view->errors = array();
        $this->_preProcessNavigation();
        
        $request = $this->getRequest();
        
        $this->session->navigation[$this->_controller][$this->_action]['pageselector'] = $request->getParam('pageselector', $this->session->navigation[$this->_controller][$this->_action]['pageselector']);
        $this->session->navigation[$this->_controller][$this->_action]['show_by']      = $request->getParam('show_by', $this->session->navigation[$this->_controller][$this->_action]['show_by']);
        
        $formAction = $request->getParam('SEARCH_FORM_ACTION', 'none');
        try {
            $orderModel                    = new Minder_SysScreen_Model_WipPicking();
            $screenBuilder                 = new Minder_SysScreen_Builder();
            $orderModel                    = $screenBuilder->buildSysScreenModel($this->view->orderSysScreenName, $orderModel);
            list($searchFields, $actions)  = $screenBuilder->buildSysScreenSearchFields($this->view->orderSysScreenName);

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
            
            $this->view->totalOrders = count($orderModel);
            $this->view->selectedOrdersCount = $rowSelector->getSelectedCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            $this->_postProcessNavigation(array('total' => $this->view->totalOrders));
            $pageSelector            = $this->view->navigation['pageselector'];
            $showBy                  = $this->view->navigation['show_by'];

            $this->view->orders                          = $orderModel->getItems($pageSelector*$showBy, $showBy, false);
            $this->view->selectedOrders                  = $rowSelector->getSelected($pageSelector, $showBy, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
            list($this->view->fields, $this->view->tabs) = $screenBuilder->buildSysScreenSearchResult($this->view->orderSysScreenName);
            $this->view->actions = $actions;
            $this->view->selectMode                      = $rowSelector->getSelectionMode('', $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);

            $pickModes = $this->minder->getPickModes();
            $this->view->pickModes = $pickModes['data'];
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
    }    
    
    public function getLinesAction() {
        $this->view->errors = array();
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
            
            if ($selectedOrdersCount > 0) {
                $totalOrders    = $rowSelector->getTotalCount($this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                $selectedOrders = $rowSelector->getSelected(0, $totalOrders, true, $this->view->orderSelectionNamespace, $this->view->orderSelectionAction, $this->view->orderSelectionController);
                
                $tmpOrderKeys   = array();
                foreach ($selectedOrders as $row) {
                    $tmpOrderKeys[] = $row['PICK_ORDER'];
                }
                
                $tmpCondString = 'PICK_ITEM.PICK_ORDER IN (' . substr(str_repeat('?, ', count($tmpOrderKeys)), 0, -2) . ')';
                
                $linesModel = $screenBuilder->buildSysScreenModel($this->view->linesSysScreenName);
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
                }
            
            } else {
                $this->_postProcessNavigation(array('total' => 0));
            }
            list($this->view->fields, $this->view->tabs) = $screenBuilder->buildSysScreenSearchResult($this->view->linesSysScreenName);
        } catch (Exception $e) {
            $this->view->errors[] = $e->getMessage();
        }
    }
    
    /*protected function _setupShortcuts() {
        $this->view->shortcuts = Minder_Navigation::getNavigationInstance('picking')->buildMinderMenuArray();

        return $this;
    }*/
}

