<?php

class Minder_SysScreen_Model_UnpickAssembly extends Minder_SysScreen_Model {
    protected $staticConditions = array(
        'PICK_ITEM.PICK_LINE_STATUS = ?' => array('CN')
    );

    public function addOrdersLimit($ordersList) {
        if (empty($ordersList))
            $this->addConditions(array('1 = 2' => array()));
        else
            $this->addConditions(array('PICK_ITEM.PICK_ORDER IN (' . substr(str_repeat('?, ', count($ordersList)), 0, -2) . ')' => array_values($ordersList))); //TODO
    }
}