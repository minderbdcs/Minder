<?php

class Minder_Controller_Action_Helper_Navigation extends Zend_Controller_Action_Helper_Abstract {
    public function navigation() {
        return $this;
    }

    public function direct() {
        return $this;
    }

    public function getPage($action = null, $controller = null) {
        $request = $this->getRequest();
        $actionController = $this->getActionController();

        $action = is_null($action) ? $request->getActionName(): $action;
        $controller = is_null($controller) ? $request->getControllerName() : $controller;

        return $request->getParam('pageselector', $actionController->session->navigation[$controller][$action]['pageselector']);
    }

    public function getShowBy($action = null, $controller = null) {
        $request = $this->getRequest();
        $actionController = $this->getActionController();

        $action = is_null($action) ? $request->getActionName(): $action;
        $controller = is_null($controller) ? $request->getControllerName() : $controller;

        return $request->getParam('show_by', $actionController->session->navigation[$controller][$action]['show_by']);
    }

    public function setPage($page, $action = null, $controller = null) {
        $request = $this->getRequest();
        $actionController = $this->getActionController();

        $action = is_null($action) ? $request->getActionName(): $action;
        $controller = is_null($controller) ? $request->getControllerName() : $controller;

        $actionController->session->navigation[$controller][$action]['pageselector'] = $page;
        return $this;
    }

    public function setShowBy($showBy, $action = null, $controller = null) {
        $request = $this->getRequest();
        $actionController = $this->getActionController();

        $action = is_null($action) ? $request->getActionName(): $action;
        $controller = is_null($controller) ? $request->getControllerName() : $controller;

        $actionController->session->navigation[$controller][$action]['show_by'] = $showBy;
        return $this;
    }
}