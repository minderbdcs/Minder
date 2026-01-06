<?php

abstract class Minder_Controller_Action_Picking extends Minder_Controller_Action
{
    public function init() {
        parent::init();
        
        if (! ($this->minder->isAdmin || $this->minder->isInventoryOperator)) {
            throw new Minder_Controller_Action_Picking_Exception('Page not found.');
        }
    }

}

class Minder_Controller_Action_Picking_Exception extends Minder_Exception {}