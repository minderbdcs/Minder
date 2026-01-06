<?php

class Minder_ConnoteProccess_PartialOrderPolicy_Legacy implements Minder_ConnoteProccess_PartialOrderPolicy_Interface {
    protected $_pickItems;

    function __construct($pickItems)
    {
        $this->_pickItems = $pickItems;
    }


    public function check()
    {
        $partialOrders = Minder::getInstance()->selectPartialOrders($this->_pickItems);

        if (count($partialOrders) > 0) {
            throw new Minder_ConnoteProccess_Exception('Order(s) ' . implode(', ', $partialOrders) . ' are not fully picked but partial pick not allowed.');
        }
    }
}