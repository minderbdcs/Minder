<?php

class Minder_Paginator_ItemAddress {
    public $itemNo = null;
    public $pageNo = null;

    function __construct($itemNo, $pageNo = null)
    {
        $this->itemNo = $itemNo;
        $this->pageNo = $pageNo;
    }
}