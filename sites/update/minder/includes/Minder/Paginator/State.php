<?php

class Minder_Paginator_State {
    public $itemsCountPerPage = 15;
    public $currentPage = 1;

    function __construct($itemsCountPerPage = 15, $currentPage = 1)
    {
        $this->itemsCountPerPage = $itemsCountPerPage;
        $this->currentPage       = $currentPage;
    }


}