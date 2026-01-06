<?php

class Minder_OtcProcess_State_AbstractLocation {

    public $location;
    public $via;
    public $description;
    public $existed = false;
    public $isSet = false;
    public $displayedId = '';
    public $loanedTotal = 0;
    public $loanedTotalDescription = '';
    public $overdue = false;
    public $opened = false;
    public $closedLocation = false;

    public function getWhId()
    {
        return substr($this->location, 0, 2);
    }

    public function getLocnId()
    {
        return substr($this->location, 2);
    }

    public function open() {
        $this->opened = true;
    }

    public function close() {
        $this->opened = false;
    }

    public function isExistedAndOpened() {
        return $this->existed && $this->opened;
    }
}