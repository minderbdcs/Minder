<?php

class Minder_AddPickItemRoutine_State {
    const STATE_ITEM_ADDED     = 'STATE_SUCCESS';
    const STATE_ERROR          = 'STATE_ERROR';
    const STATE_ITEM_NOT_FOUND = 'STATE_ITEM_NOT_FOUND';
    const STATE_NO_STOCK       = 'STATE_NO_STOCK';

    /**
     * @var string
     */
    public $type         = null;

    /**
     * @var Minder_AddPickItemRoutine_Request
     */
    public $request      = null;

    /**
     * @var Minder_AddPickItemRoutine_ItemProvider_Interface
     */
    public $itemProvider = null;

    /**
     * @var Minder_AddPickItemRoutine_Strategy_Interface
     */
    public $addStrategy  = null;

    /**
     * @var int
     */
    public $stockAmount  = 0;

    public $errors       = array();
}