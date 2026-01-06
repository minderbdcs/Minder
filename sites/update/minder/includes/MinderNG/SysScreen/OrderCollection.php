<?php

namespace MinderNG\SysScreen;

use MinderNG\Collection\Collection;

class OrderCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Order::CLASS_NAME;
    }

}