<?php

class Picking_AllocatingLimitsController extends Minder_Controller_Action_Picking
{
    const SCREEN_NAME = 'ALLOCATELIMITS';

    public function init() {
        parent::init();
        
        $this->view->instanceId = uniqid('alocating_limits_instance_');
        
        $this->view->screenName = static::SCREEN_NAME;
    }

    public function indexAction() {
        try {
        
        $request = $this->getRequest();
        
        $this->view->instanceId = $request->getParam('INSTANCE_ID', $this->view->instanceId);
        $namespace              = 'default';
        
        $formAction = strtolower($request->getParam('form_action', 'none'));
        
        $screenBuilder = new Minder_SysScreen_Builder();
        list($fields, $actions, $tabs) = $screenBuilder->buildSysScreenSearchFields($this->view->screenName);

        $fields = $this->_helper->screenDataKeeper('load', $fields, $this->view->instanceId, $namespace, '');
        
        
        $this->view->deviceIdIsReadonly = false;
        if (!($this->minder->isAdmin || $this->minder->isSalesManagerT())) {
            $this->view->deviceIdIsReadonly = true;
        }

        
        $this->view->fields    = $fields;
        $this->view->actions   = $actions;
        $this->view->tabs      = $tabs;
        $this->view->namespace = $namespace;
        }catch (Exception $e) {
        }
    }
    
    public function setLimitsAction() {
        $response = new stdClass();
        
        $response->errors   = array();
        $response->messages = array();
        $response->warnings = array();
        $response->status   = "failed";
        
        try {
        
        $request = $this->getRequest();
        
        $this->view->instanceId = $request->getParam('INSTANCE_ID', $this->view->instanceId);
        $namespace              = 'default';
        
        $screenBuilder = new Minder_SysScreen_Builder();
        list($fields) = $screenBuilder->buildSysScreenSearchFields($this->view->screenName);
        
        $maxOrders = $request->getParam('MAX_ORDERS');
        if (!is_null($maxOrders) && $maxOrders > $this->minder->defaultControlValues['MAX_PICK_ORDERS']) {
            $request->setParam('MAX_ORDERS', $this->minder->defaultControlValues['MAX_PICK_ORDERS']);
            $response->warnings[] = 'Max. Orders couldn\'t be greater then ' . $this->minder->defaultControlValues['MAX_PICK_ORDERS'] . '.';
        }

        $maxProducts = $request->getParam('MAX_PRODUCTS');
        if (!is_null($maxProducts) && $maxProducts > $this->minder->defaultControlValues['MAX_PICK_PRODUCTS']) {
            $request->setParam('MAX_PRODUCTS', $this->minder->defaultControlValues['MAX_PICK_PRODUCTS']);
            $response->warnings[] = 'Max. Products couldn\'t be greater then ' . $this->minder->defaultControlValues['MAX_PICK_PRODUCTS'] . '.';
        }

        $maxPickItems = $request->getParam('MAX_PICK_ITEMS');
        if (!is_null($maxPickItems) && $maxPickItems > $this->minder->defaultControlValues['MAX_PICK_LINES']) {
            $request->setParam('MAX_PICK_ITEMS', $this->minder->defaultControlValues['MAX_PICK_LINES']);
            $response->warnings[] = 'Max. Products couldn\'t be greater then ' . $this->minder->defaultControlValues['MAX_PICK_LINES'] . '.';
        }

        if (!($this->minder->isAdmin || $this->minder->isSalesManagerT())) {
            $request->setParam('DEVICE_ID', $this->minder->deviceId);
        }
        
        $dataKeeper = $this->_helper->screenDataKeeper;
        
        $dataKeeper->setNamespace($namespace)->setInstanceId($this->view->instanceId)->setFieldsPrefix('');
        $dataKeeper->saveData($fields);
        
        foreach ($dataKeeper->getParams() as $paramName => $paramValue) {
            $response->$paramName = $paramValue;
        }
        
        $response->status   = "success";
        }catch (Exception $e) {
            $response->errors[] = $e->getMessage();
        }
        
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($response);
    }
}