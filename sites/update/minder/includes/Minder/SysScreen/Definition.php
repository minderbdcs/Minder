<?php

class Minder_SysScreen_Definition {
    public $fields;
    public $tabs;
    public $colors;
    public $actions;

    function __construct($fields = array(), $tabs = array(), $colors = array(), $actions = array())
    {
        $this->fields = $fields;
        $this->tabs = $tabs;
        $this->colors = $colors;
        $this->actions = $actions;
    }
}