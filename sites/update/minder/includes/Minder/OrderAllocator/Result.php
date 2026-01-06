<?php

class Minder_OrderAllocator_Result {
    public $messages = array();
    public $warnings = array();
    public $errors   = array();

    public $allocatedOrders   = 0;
    public $allocatedProducts = 0;

    public $partiallyDespatchedOrders = array();
}