<?php

class Minder_Controller_Action_CRUD extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->basePath = $this->_request->getBasePath();
        $this->view->baseUrl = $this->_request->getBaseUrl();
    }

    public function autoCompleteAction()
    {
        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';
        $varName = strtolower($modelName[0]) . substr($modelName, 1);

        $dao = new $daoName();
        $this->view->dao = $dao;

        $field = $this->_request->getParam('field', null);
        $value = $this->_request->getParam('q', null);
        $params = $this->_request->getParams();
        if ($field === null || $value === null) {
           throw new Zend_Controller_Action_Exception('Missing field or value');
        }

        $results = $dao->autoComplete($field, $value, $params);
        if ($results === null) {
           throw new Zend_Controller_Action_Exception('Could not autocomplete ' . $field);
        }
        $this->view->results = $results;
    }

    public function deleteAction()
    {
        $this->view->deleted = false;

        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';
        $varName = strtolower($modelName[0]) . substr($modelName, 1);

        $dao = new $daoName();
        $this->view->dao = $dao;

        $id = $this->_getParam('id', null);
        if ($id === null) {
           throw new Zend_Controller_Action_Exception('Missing id');
        }
        $dto = $dao->find($id);
        if ($dto === null) {
           throw new Zend_Controller_Action_Exception('Invalid id');
        }

        $this->view->$varName = $dto;
        if ($this->_request->isPost()) {
            if ($this->_getParam('confirm', 'N') !== 'Y') {
                $this->view->errors['confirm'] = 'You need to confirm the delete';
            } else {
                if ($dao->delete($id)) {
                   $this->view->deleted = true;
                } else {
                    $this->view->errors = $dao->errors;
                }
            }
        }
    }

    public function editAction()
    {
        $this->view->saved = false;

        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';
        $varName = strtolower($modelName[0]) . substr($modelName, 1);

        $dao = new $daoName();
        $this->view->dao = $dao;

        $id = $this->_getParam('id', null);
        if ($id === null) {
           throw new Zend_Controller_Action_Exception('Missing id');
        }
        $dto = $dao->find($id);
        if ($dto === null) {
           throw new Zend_Controller_Action_Exception('Invalid id');
        }

        if ($this->_request->isPost()) {
            $dao->saveAttrs($dto, $this->_request->getPost('Data'));
            if ($dao->update($id, $dto)) {
                $this->view->saved = true;
            } else {
                $this->view->errors = $dao->errors;
            }
        }

        $this->view->$varName = $dto;
    }

    public function indexAction()
    {
        $this->_forward('search');
    }

    public function lookupAction()
    {
        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';
        $varName = strtolower($modelName[0]) . substr($modelName, 1);

        $dao = new $daoName();
        $this->view->dao = $dao;

        $field = $this->_request->getParam('field', null);
        $params = $this->_request->getParams();
        if ($field === null) {
           throw new Zend_Controller_Action_Exception('Missing field');
        }

        $results = $dao->lookup($field, $params);
        if ($results === null) {
           throw new Zend_Controller_Action_Exception('Could not lookup ' . $field);
        }
        $this->view->results = $results;
    }

    public function newAction()
    {
        $this->view->saved = false;

        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';
        $varName = strtolower($modelName[0]) . substr($modelName, 1);

        $dao = new $daoName();
        $this->view->dao = $dao;

        $dto = new $modelName();
        $dao->init($dto);
        if ($this->_request->isPost()) {
            $dao->saveAttrs($dto, $this->_request->getPost('Data'));
            if ($dao->create($dto)) {
                $dto = new $modelName();
                $dao->init($dto);
                $this->view->saved = true;
            } else {
                $this->view->errors = $dao->errors;
            }
        }

        $this->view->$varName = $dto;
    }

    public function showAction()
    {
        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';
        $varName = strtolower($modelName[0]) . substr($modelName, 1);

        $dao = new $daoName();
        $this->view->dao = $dao;

        $id = $this->_getParam('id', null);
        if ($id === null) {
           throw new Zend_Controller_Action_Exception('Missing id');
        }
        $dto = $dao->find($id);
        if ($dto === null) {
           throw new Zend_Controller_Action_Exception('Invalid id');
        }

        $this->view->$varName = $dto;
    }

    public function searchAction()
    {
        $search = new Minder_SearchBuilder();

        if ($this->_request->has('Search')) {
            $search->build($this->_request->getParam('Search'));
        }

        $modelName = array_pop(explode('_', substr(get_class($this), 0, -10)));
        $daoName = $modelName . 'DAO';

        $dao = new $daoName();
        $this->view->dao = $dao;

        $this->view->search = $search;
        if ($search->condition != '') {
            $this->view->results = $dao->findAll($search);
        } else {
            $this->view->results = array();
        }
    }
}
