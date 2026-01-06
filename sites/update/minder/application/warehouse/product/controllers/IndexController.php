<?php

class Warehouse_IndexController extends Minder_Controller_Action
{
    public function indexAction()
    {   
        // redirect to warehouse/grn/index
        $minder = Minder::getInstance();

        $this->_redirector = $this->_helper->getHelper('Redirector');
        $this->_redirector->setCode(303);
        if ($minder->userId == null) {
             $this->_redirector->goto('login', 'user', '', array());
        } else {
             $this->_redirector->goto('index', 'issn2', 'warehouse', array());
        }
    }

}
