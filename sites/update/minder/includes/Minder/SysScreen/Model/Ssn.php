<?php

class Minder_SysScreen_Model_Ssn extends Minder_SysScreen_Model {
    public function __construct()
    {
        $this->useDistinct = false;
        parent::__construct();
    }

}