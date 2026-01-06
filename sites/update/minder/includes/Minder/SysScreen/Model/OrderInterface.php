<?php

interface Minder_SysScreen_Model_OrderInterface {
    public function setCustomOrderFields($sortFields = array());

    public function setOrder(array $value = array());
}