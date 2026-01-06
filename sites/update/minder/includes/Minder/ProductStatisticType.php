<?php

class Minder_ProductStatisticType {
    public $all = false;
    public $selected = false;

    public function __construct($all = false, $selected = false) {
        $this->all      = $all;
        $this->selected = $selected;
    }
}
