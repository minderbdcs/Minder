<?php

class SysUserController extends Minder_Controller_Action_CRUD
{
    public function init()
    {
        parent::init();
        $this->minder = Minder::getInstance();

        if ($this->minder->userId == null) {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('login', 'user', '', array());
            return;
        }

        $this->view->minder = $this->minder;
        $this->view->shortcuts = array(
            'Search Users' => $this->view->url(array('action' => 'search', 'controller' => 'sys-user')),
            'New User' => $this->view->url(array('action' => 'new', 'controller' => 'sys-user')));
        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index', 'module' => 'default'), '', true);
    }
}
