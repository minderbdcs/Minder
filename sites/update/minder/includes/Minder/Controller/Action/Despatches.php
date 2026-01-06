<?php

abstract class Minder_Controller_Action_Despatches extends Minder_Controller_Action
{
    public function init() {
        parent::init();
    }
}

class Minder_Controller_Action_Picking_Exception extends Minder_Exception {}