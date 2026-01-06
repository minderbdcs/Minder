<?php

include_once('functions.php');


class MappingController extends Minder_Controller_Action {
    
    public function init() {
        parent::init();
        if ((!$this->minder->isAdmin) && $this->minder->isInventoryOperator) {
            $this->_redirector->setCode(303)
                              ->goto('index', 'index', '', array());
            return;
        }
        
    }
    
    
    public function indexAction(){
        
    }
}
?>
