<?php

class Services_QueryLogController extends Zend_Controller_Action {

    protected function _getCurrentUser() {
        return Minder2_Environment::getCurrentUser();
    }

    protected function _getQueryLog() {
        return Minder_SysScreen_QueryLog::getInstance();
    }

    public function setLimitAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $response = new Minder_JSResponse();

        if (!$this->_getCurrentUser()->isAdmin()) {
            $response->success = false;
            $response->errors[] = 'Only administartors are allowed to use query log.';
        } else {
            $this->_getQueryLog()->setLimit($this->getRequest()->getParam('limit', 0));
            $response->success = true;
        }

        echo json_encode($response);
    }

    public function getLogAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $response = new Minder_JSResponse();

        if (!$this->_getCurrentUser()->isAdmin()) {
            $response->success = false;
            $response->errors[] = 'Only administartors are allowed to use query log.';
        } else {
            $log = $this->_getQueryLog()->getQueryLog();

            $limit = $this->getRequest()->getParam('limit', 0);
            $range = $this->getRequest()->getParam('range', 'all');

            switch (strtolower($range)) {
                case 'all':
                    $response->log = $log;
                    break;
                case 'last':
                    $response->log = array_slice($log, -$limit);
                    break;
                case 'first':
                    $response->log = array_slice($log, 0, $limit);
                    break;
                default:
                    $response->log = $log;
            }

            $response->success = true;
        }

        echo json_encode($response);
    }

    public function setMarkAction() {
        $this->_helper->viewRenderer->setNoRender(true);

        $response = new Minder_JSResponse();

        if (!$this->_getCurrentUser()->isAdmin()) {
            $response->success = false;
            $response->errors[] = 'Only administartors are allowed to use query log.';
        } else {
            $this->_getQueryLog()->logQuery('User mark: ' . $this->getRequest()->getParam('label', ''), array(), 0);
            $response->success = true;
        }

        echo json_encode($response);
    }
}