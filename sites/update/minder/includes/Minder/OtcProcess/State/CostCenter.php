<?php

class Minder_OtcProcess_State_CostCenter {
    public $id;
    public $description;
    public $via;
    public $existed = false;

    function __construct($id = null, $via = 'S', $description = null)
    {
        $this->id = $id;
        $this->via = $via;

        $this->description = is_null($id) ? '' : 'Invalid cost centre';

        if (!is_null($description)) {
            $this->description = $description;
            $this->existed = true;
        }
    }


}