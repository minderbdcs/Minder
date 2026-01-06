<?php

class Minder_SysScreen_QueryLog_Entry{
    public $time  = 0;
    public $query = '';
    public $args = array();

    public function __construct($query, $args, $time) {
        $this->query = $query;
        $this->args = $args;
        $this->time = $time;
    }
}